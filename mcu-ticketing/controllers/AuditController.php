<?php


class AuditController extends BaseController {
    private $project;
    private $comment;

    public function __construct() {
        parent::__construct();
        $this->checkRole(['superadmin', 'manager_ops', 'head_ops', 'ceo']);
        
        $this->project = $this->loadModel('Project');
        $this->comment = $this->loadModel('ProjectComment');
    }

    public function index() {
        $project = null;
        $projects = [];
        $term = '';
        $history = [];
        $duration_string = "N/A";
        $start_date = null;
        $end_date = null;

        if (isset($_GET['project_id'])) {
            $term = $_GET['project_id'];
            $id = $term;
            
            // Try to find by ID first
            $project = $this->project->getProjectById($id);

            // If not found by ID, try SPH Number
            if (!$project) {
                $projects = $this->project->getBySph($term);
                
                if (!empty($projects)) {
                    // Check if a specific project from the SPH group is selected
                    if (isset($_GET['selected_id'])) {
                        foreach ($projects as $p) {
                            if ($p['project_id'] == $_GET['selected_id']) {
                                $project = $p;
                                break;
                            }
                        }
                    }
                    
                    // Default to first project if none selected or selection invalid
                    if (!$project) {
                        $project = $projects[0];
                    }
                    
                    // Update ID for further data retrieval
                    $id = $project['project_id'];
                }
            }

            if ($project) {
                $history = $this->project->getHistory($id);
                $comments = $this->comment->readByProject($id)->fetchAll(PDO::FETCH_ASSOC);

                // Process Mentions for Display
                $userModel = $this->loadModel('User');
                $users = $userModel->getAllUsers()->fetchAll(PDO::FETCH_ASSOC);
                
                // Sort users by length of name/username descending to avoid partial matches
                usort($users, function($a, $b) {
                    return strlen($b['full_name']) - strlen($a['full_name']);
                });

                foreach ($comments as &$comment) {
                    // Secure the message first (equivalent to htmlspecialchars in view)
                    $comment['message'] = htmlspecialchars($comment['message']);
                    
                    foreach ($users as $u) {
                        $mentionsToTry = [];
                        if (!empty($u['full_name'])) $mentionsToTry[] = htmlspecialchars($u['full_name']);
                        if (!empty($u['username'])) $mentionsToTry[] = htmlspecialchars($u['username']);
                        
                        foreach ($mentionsToTry as $mentionTerm) {
                            $replacement = '<span class="text-primary fw-bold" style="background-color: #e8f0fe; padding: 0 4px; border-radius: 4px;">@' . $mentionTerm . '</span>';
                            // Replace @Name ensuring it's not inside a tag (simple heuristic) and matches whole words
                            $comment['message'] = preg_replace('/(?<!>)(?<!\w)@' . preg_quote($mentionTerm, '/') . '(?!\w)/i', $replacement, $comment['message']);
                        }
                    }
                }

                $ba_files = $this->project->getBeritaAcara($id); // Fetch Berita Acara
                $tm_data = $this->project->getTechnicalMeeting($id); // Fetch Technical Meeting Data
                $vendor_allocations = $this->project->getVendorAllocations($id)->fetchAll(PDO::FETCH_ASSOC); // Fetch Vendor Allocations
                
                // Fetch RAB Documents
                $rabModel = $this->loadModel('Rab');
                $rabs = $rabModel->isProjectHasRab($id);

                // Fetch Medical Results
                $medicalResultModel = $this->loadModel('MedicalResult');
                $medical_result = $medicalResultModel->getByProjectId($id);
                $medical_result_items = [];
                if ($medical_result) {
                    $medical_result_items = $medicalResultModel->getItemsByResultId($medical_result['id']);
                }

                // Fetch Staff Assignments
                $manpowerModel = $this->loadModel('ProjectManPower');
                $staff_assignments = $manpowerModel->getAssignments($id);

                // Fetch DW Realizations
                $realizationModel = $this->loadModel('MedicalResultRealization');
                $dw_realizations = $realizationModel->getByProjectId($id);
                
                // Calculate Durations per Phase
                // 1. Define Phase Start/End Times
                $t_create = strtotime($project['created_at']);
                
                // Use explicit approval date if available
                $t_approved = !empty($project['approved_date_head']) ? strtotime($project['approved_date_head']) : null;
                if (!$t_approved && !empty($project['approved_date_manager'])) {
                     $t_approved = strtotime($project['approved_date_manager']);
                }

                $t_invoicing = null;
                $t_completed = null;
                $t_cancelled = null;

                // Scan logs for key transitions
                foreach ($history as $log) {
                    $ts = strtotime($log['changed_at']);
                    $action = strtolower($log['status_to']);
                    
                    // Fallback for Approved if column is empty
                    if (!$t_approved && strpos($action, 'status changed to approved') !== false) {
                        $t_approved = $ts;
                    }

                    // Ops -> Invoicing (INVOICE_REQUESTED or ready_for_invoicing)
                    // Matches: "INVOICE_REQUESTED", "Status Changed to Invoice requested"
                    if (!$t_invoicing && (
                        strpos($action, 'invoice_requested') !== false || 
                        strpos($action, 'ready_for_invoicing') !== false
                    )) {
                        $t_invoicing = $ts;
                    }

                    // Completed
                    if (!$t_completed && (
                        strpos($action, 'status changed to completed') !== false || 
                        $action === 'completed'
                    )) {
                        $t_completed = $ts;
                    }

                    // Cancelled/Rejected
                    if (!$t_cancelled && (
                        strpos($action, 'status changed to cancelled') !== false || 
                        strpos($action, 'status changed to rejected') !== false ||
                        $action === 'cancelled' || 
                        $action === 'rejected'
                    )) {
                        $t_cancelled = $ts;
                    }
                }

                // Current Time (for ongoing phases)
                $now = time();
                if ($t_cancelled) $now = $t_cancelled; // If cancelled, stop clock at cancellation

                // 2. Calculate Durations
                
                // Approval Phase: Create -> Approved
                if ($t_approved) {
                    $dur_approval = $t_approved - $t_create;
                } else {
                    // Still in approval or cancelled before approval
                    $dur_approval = $now - $t_create;
                }

                // Ops Phase: Approved -> Invoicing
                $dur_ops = 0;
                if ($t_approved) {
                    if ($t_invoicing) {
                        $dur_ops = $t_invoicing - $t_approved;
                    } else {
                        // Still in Ops or cancelled in Ops
                        // Only if NOT completed directly (rare)
                        if ($t_completed && !$t_invoicing) {
                            // Direct jump approved -> completed?
                            $dur_ops = $t_completed - $t_approved;
                        } else {
                            $dur_ops = $now - $t_approved;
                        }
                    }
                }

                // Invoicing Phase: Invoicing -> Completed
                $dur_invoicing = 0;
                if ($t_invoicing) {
                    if ($t_completed) {
                        $dur_invoicing = $t_completed - $t_invoicing;
                    } else {
                        // Still in invoicing
                        $dur_invoicing = $now - $t_invoicing;
                    }
                } elseif ($t_completed && !$t_invoicing && $t_approved) {
                    // Fallback if invoicing step skipped
                    $dur_invoicing = 0; 
                }

                // Format Helpers
                $formatDuration = function($seconds) {
                    if ($seconds < 0) $seconds = 0;
                    
                    $days = floor($seconds / (60 * 60 * 24));
                    $seconds_remaining = $seconds - ($days * 60 * 60 * 24);
                    $hours = floor($seconds_remaining / (60 * 60));
                    $minutes = floor(($seconds_remaining - ($hours * 60 * 60)) / 60);

                    if ($days > 0) {
                        return "{$days}d {$hours}h";
                    } elseif ($hours > 0) {
                        return "{$hours}h {$minutes}m";
                    } else {
                        return "{$minutes}m";
                    }
                };

                $durations = [
                    'approval' => $formatDuration($dur_approval),
                    'ops' => $formatDuration($dur_ops),
                    'invoicing' => $formatDuration($dur_invoicing)
                ];

                $start_date = DateHelper::formatDateTimeShort($t_create);
                $end_date = ($t_completed || $t_cancelled) ? DateHelper::formatDateTimeShort($t_completed ?? $t_cancelled) : 'Ongoing';
                
                // Calculate Progress Percentage based on status
                $progress_percent = 0;
                $is_cancelled = in_array($project['status_project'], ['cancelled', 'rejected']);
                
                // New Map per User Request
                $status_map = [
                    'pending' => 10,
                    'need_manager' => 20, // inferred for 'need_approval_manager'
                    'need_approval_manager' => 20,
                    'need_head' => 35, // inferred for 'need_approval_head'
                    'need_approval_head' => 35,
                    'approved' => 50,
                    'process_vendor' => 60, // Ops part 1
                    'vendor_assigned' => 65, // Ops part 2
                    'no_vendor_needed' => 65,
                    'in_progress_ops' => 70,
                    'ongoing' => 70, // legacy
                    'ready_for_invoicing' => 80,
                    'invoice_requested' => 85,
                    'invoiced' => 90,
                    'paid' => 95,
                    'completed' => 100
                ];
                
                if ($is_cancelled) {
                    $progress_percent = 100; // Full bar but red
                } else {
                    $progress_percent = $status_map[$project['status_project']] ?? 10;
                }
            }
        }

        $this->view('audit/index', [
            'project' => $project,
            'projects' => $projects,
            'search_term' => $term,
            'history' => $history,
            'durations' => $durations ?? ['approval'=>'-', 'ops'=>'-', 'invoicing'=>'-'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'progress_percent' => $progress_percent ?? 0,
            'is_cancelled' => $is_cancelled ?? false,
            'comments' => $comments ?? [],
            'ba_files' => $ba_files ?? [],
            'tm_data' => $tm_data ?? [],
            'vendor_allocations' => $vendor_allocations ?? [],
            'rabs' => $rabs ?? [],
            'medical_result' => $medical_result ?? null,
            'medical_result_items' => $medical_result_items ?? [],
            'staff_assignments' => $staff_assignments ?? [],
            'dw_realizations' => $dw_realizations ?? []
        ]);
    }
}
