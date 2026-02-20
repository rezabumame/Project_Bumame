<?php

class KanbanController extends BaseController {
    private $project;

    public function __construct() {
        parent::__construct();
        $this->project = $this->loadModel('Project');
    }

    public function index() {
        $this->checkRole(['manager_ops', 'head_ops', 'admin_ops', 'superadmin', 'admin_sales', 'ceo', 'sales_support_supervisor', 'sales_performance_manager', 'sales', 'manager_sales', 'surat_hasil']);

        // Fetch projects by status for columns
        $need_approval = [];
        $next_approval = [];
        $approved = [];
        $others = []; // rejected, cancelled, re-nego

        // Fetch SLA Setting
        $systemSetting = $this->loadModel('SystemSetting');
        $approval_sla_days = $systemSetting->get('approval_sla_days') ?? 1;

        // Fetch Holidays
        $nationalHoliday = $this->loadModel('NationalHoliday');
        $holidays = $nationalHoliday->getHolidayDates();


        $limit = 100; // Increased limit to ensure admin_ops sees all relevant projects

        // Logic differs slightly for Manager vs Head vs Admin Ops
        if (in_array($_SESSION['role'], ['manager_ops', 'admin_ops', 'superadmin', 'admin_sales', 'sales', 'manager_sales', 'sales_support_supervisor', 'sales_performance_manager', 'surat_hasil'])) {
            // Admin Ops sees same as Manager Ops (Full Flow start)
            $stmt = $this->project->readByStatus('need_approval_manager', $limit);
            $need_approval = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->project->readByStatus('need_approval_head', $limit);
            $next_approval = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
             // Head Ops logic
             $stmt = $this->project->readByStatus('need_approval_head', $limit);
            $need_approval = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Next approval for head is just Approved state effectively
        }

        $stmt = $this->project->readByStatus('approved', $limit);
        $approved = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $others = []; // rejected, cancelled, re-nego

        // Fix: Use separate arrays or verify merge logic. array_merge reindexes numeric keys but we want list of assoc arrays.
        // fetchAll returns [0 => [...], 1 => [...]]. array_merge appends them. This is correct.
        
        $stmt = $this->project->readByStatus('rejected', $limit);
        $rejected = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->project->readByStatus('cancelled', $limit);
        $cancelled = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->project->readByStatus('re-nego', $limit);
        $renego = $stmt->fetchAll(PDO::FETCH_ASSOC);



        $this->view('manager_ops/kanban', [
            'need_approval' => $need_approval,
            'next_approval' => $next_approval,
            'approved' => $approved,
            'others' => $others,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'renego' => $renego,
            'holidays' => $holidays,
            'approval_sla_days' => $approval_sla_days
        ]);
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Block read-only roles
            if (in_array($_SESSION['role'], ['admin_sales', 'admin_ops', 'ceo', 'sales_support_supervisor', 'sales_performance_manager', 'sales', 'manager_sales', 'surat_hasil', 'dw_tim_hasil'])) {
                $this->jsonResponse(['status' => 'error', 'message' => 'You do not have permission to update project status.']);
            }

            $project_id = $_POST['project_id'];
            $new_status = $_POST['status'];
            $reason = $_POST['reason'] ?? null;
            
            // Get current project status
            $current_project = $this->project->getProjectById($project_id);
            if (!$current_project) {
                    $this->jsonResponse(['status' => 'error', 'message' => 'Project not found']);
            }
            $current_status = $current_project['status_project'];

            // Validate Transition
            $validation = $this->isValidTransition($current_status, $new_status, $_SESSION['role']);
            if ($validation !== true) {
                    $this->jsonResponse(['status' => 'error', 'message' => $validation]);
            }

            // Check SPH Validation (Mandatory for promotions)
            // Exception: Superadmin
            if ($_SESSION['role'] != 'superadmin') {
                $check_sph = false;
                
                // Manager -> Head
                if ($current_status == 'need_approval_manager' && $new_status == 'need_approval_head') {
                    $check_sph = true;
                }
                
                // Head -> Approved
                if ($current_status == 'need_approval_head' && $new_status == 'approved') {
                    $check_sph = true;
                }

                if ($check_sph && empty($current_project['sph_file'])) {
                    $this->jsonResponse(['status' => 'error', 'message' => 'Cannot proceed: SPH link is mandatory before approval.']);
                }
            }
            
            if ($this->project->updateStatus($project_id, $new_status, $_SESSION['user_id'], $_SESSION['role'], $reason)) {
                // Email Notification
                try {
                    $userModel = $this->loadModel('User');
                    $emails = [];
                    $subject = "";
                    $content = "";
                    $link = "https://mcu.bumame.com/public/index.php?page=manager_ops_kanban";

                    $salesName = $current_project['sales_name'] ?? '-';
                    $totalPeserta = $current_project['total_peserta'] ?? '-';
                    $tanggalMcu = isset($current_project['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($current_project['tanggal_mcu']) : '-';

                    if ($new_status == 'need_approval_head') {
                        $emails = $userModel->getEmailsByRole('head_ops');
                        $subject = "[Action Required] Persetujuan Project: " . $current_project['nama_project'];
                        $content = "Project telah disetujui oleh Manager Ops dan kini memerlukan persetujuan Anda.<br><br>";
                        $content .= "<b>Nama Project:</b> " . $current_project['nama_project'] . "<br>";
                        $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                        $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                        $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                        $link = MailHelper::getBaseUrl() . "?page=manager_ops_kanban";
                    } elseif ($new_status == 'approved') {
                        $emails = $userModel->getEmailsByRole('admin_ops');
                        $subject = "[Info] Project Disetujui: " . $current_project['nama_project'];
                        $content = "Project telah disetujui sepenuhnya oleh Head Ops. Silakan lakukan persiapan operasional.<br><br>";
                        $content .= "<b>Nama Project:</b> " . $current_project['nama_project'] . "<br>";
                        $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                        $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                        $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                        $link = MailHelper::getBaseUrl() . "?page=all_projects&open_project_id=" . $project_id;
                    } elseif (in_array($new_status, ['rejected', 're-nego'])) {
                        // Notify Admin Sales (Role: admin_sales)
                        $emails = $userModel->getEmailsByRole('admin_sales');
                        $subject = "[Revisi] Project " . ucfirst($new_status) . ": " . $current_project['nama_project'];
                        $content = "Project Anda berstatus <b>" . ucfirst($new_status) . "</b>.<br><br>";
                        $content .= "<b>Nama Project:</b> " . $current_project['nama_project'] . "<br>";
                        $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                        $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                        $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                        if ($reason) {
                            $content .= "<b>Alasan:</b> " . $reason . "<br>";
                        }
                        $link = MailHelper::getBaseUrl() . "?page=all_projects&open_project_id=" . $project_id;
                    }

                    if (!empty($emails)) {
                        $html = MailHelper::getTemplate("Update Status Project", $content, $link);
                        MailHelper::send($emails, $subject, $html);
                    }
                } catch (Exception $e) {
                    error_log("Email notification failed on Kanban status update: " . $e->getMessage());
                }

                $this->jsonResponse(['status' => 'success']);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'Database update failed']);
            }
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method']);
        }
    }

    private function isValidTransition($current, $new, $role) {
        // Superadmin can do anything
        if ($role == 'superadmin') {
            return true;
        }

        // Strict Rule: Projects in 'cancelled' state can ONLY be moved by 'head_ops'
        if ($current == 'cancelled') {
            if ($role != 'head_ops') {
                return "Only Head Ops can revive a cancelled project.";
            }
            // Head Ops can revive to 'need_approval_manager'
            if ($new == 'need_approval_manager') {
                return true;
            }
            return "Cancelled projects can only be moved to 'Need Approval Manager'.";
        }

        // Strict Rule: Projects in 'rejected' or 're-nego' cannot be moved via Kanban
        // They must be edited by Admin Sales to reset status.
        if (in_array($current, ['rejected', 're-nego'])) {
            return "Projects in Rejected or Re-Nego status cannot be moved here. Admin Sales must edit the project to resubmit.";
        }

        // Strict Rule: Projects in 'approved' status can ONLY be modified by 'superadmin'
        // (Superadmin is already handled at the top of this function)
        if ($current == 'approved') {
            return "Alert: Status 'Approved' cannot be changed (Only Superadmin can modify this).";
        }

        // Allow anything to rejected/re-nego/cancelled for authorized roles
        if (in_array($new, ['rejected', 're-nego', 'cancelled'])) {
            return true; // Assuming manager/head can always reject/cancel
        }

        // Manager Ops: need_approval_manager -> need_approval_head
        if ($role == 'manager_ops') {
            if ($current == 'need_approval_manager' && $new == 'need_approval_head') return true;
            // Removed backward transition
            
            if ($current == 'need_approval_head' && $new == 'approved') {
                return "Only Head of Ops can final approve this project.";
            }
            if ($current == 'need_approval_manager' && $new == 'approved') {
                return "You must send to Head of Ops for approval first.";
            }
            if ($current == 'need_approval_head' && $new == 'need_approval_manager') {
                return "You cannot move back to Need My Approval.";
            }
        }

        // Head Ops: need_approval_head -> approved
        if ($role == 'head_ops') {
             if ($current == 'need_approval_head' && $new == 'approved') return true;
             // Removed backward transition
             
             if ($current == 'need_approval_manager') {
                 return "This project is still waiting for Manager Ops approval.";
             }
             if ($current == 'approved' && $new == 'need_approval_head') {
                 return "You cannot move back to Need My Approval.";
             }
        }

        return "You are not authorized to perform this action. Role: " . $role . ", Move: " . $current . " -> " . $new;
    }
    
    public function getProjectDetail() {
         // Security: Restrict access to authorized roles
         $allowed_roles = ['manager_ops', 'head_ops', 'admin_ops', 'superadmin', 'admin_sales', 'ceo', 'procurement', 'finance', 'korlap', 'dw_tim_hasil', 'admin_gudang_warehouse', 'admin_gudang_aset', 'surat_hasil', 'sales_support_supervisor', 'sales_performance_manager', 'sales', 'manager_sales'];
         if (!in_array($_SESSION['role'], $allowed_roles)) {
             $this->jsonResponse(['error' => 'Unauthorized'], 403);
         }

         if (isset($_GET['id'])) {
             try {
                 // Self-Healing: Check completion status
                 if (isset($_SESSION['user_id'])) {
                     $this->project->checkCompletionStatus($_GET['id'], $_SESSION['user_id']);
                 }

                 $project = $this->project->getProjectById($_GET['id']);
                 if ($project) {
                     $project['tanggal_mcu_formatted'] = DateHelper::formatSmartDateIndonesian($project['tanggal_mcu']);
                     
                     // Generate Exam Matrix HTML
                     $project['exam_matrix_html'] = PackageHelper::renderMatrix($project['jenis_pemeriksaan'], $project['company_name']);

                     // Fetch Vendor Allocations (for Vendor Memo Button)
                     $allocations_stmt = $this->project->getVendorAllocations($_GET['id']);
                     $project['vendor_allocations'] = $allocations_stmt->fetchAll(PDO::FETCH_ASSOC);

                     // Fetch Cost Codes (for Vendor Memo Modal)
                     $costCodeModel = $this->loadModel('CostCode');
                     $cost_codes_stmt = $costCodeModel->readAll();
                     $project['cost_codes'] = $cost_codes_stmt->fetchAll(PDO::FETCH_ASSOC);

                     // Process Dates for Berita Acara
                     $dates = DateHelper::parseDateArray($project['tanggal_mcu']);
                     $ba_status = $this->project->getBeritaAcara($_GET['id']);
                     
                     $project['dates'] = [];
                     if (is_array($dates)) {
                         foreach ($dates as $date) {
                             $status = isset($ba_status[$date]) ? $ba_status[$date] : null;
                             $project['dates'][] = [
                                 'date' => $date,
                                'formatted_date' => DateHelper::formatIndonesianDate($date),
                                 'ba_status' => $status ? $status['status'] : 'pending',
                                 'ba_file' => $status ? $status['file_path'] : null,
                                 'ba_uploaded_at' => $status ? $status['created_at'] : null
                             ];
                         }
                     }

                     // Fetch history
                     $project['history'] = $this->project->getHistory($_GET['id']);
                     if (!empty($project['history'])) {
                         foreach ($project['history'] as &$log) {
                             $log['formatted_at'] = DateHelper::formatIndonesianDate($log['changed_at'], true);
                         }
                     }

                     // Fetch Technical Meeting
                     $project['technical_meeting'] = $this->project->getTechnicalMeeting($_GET['id']);
                     if ($project['technical_meeting']) {
                         $project['technical_meeting']['tm_date_formatted'] = DateHelper::formatIndonesianDate($project['technical_meeting']['tm_date']);
                         if (!empty($project['technical_meeting']['setting_alat_date'])) {
                             $project['technical_meeting']['setting_alat_date_formatted'] = DateHelper::formatIndonesianDate($project['technical_meeting']['setting_alat_date'], true);
                         }
                     }

                     // Fetch Koordinator Hasil (Medical Result Assignees)
                     $medicalResultModel = $this->loadModel('MedicalResult');
                     $medicalResult = $medicalResultModel->getByProjectId($_GET['id']);
                     $koordinator_hasil = [];
                     
                     if ($medicalResult) {
                         $items = $medicalResultModel->getItemsByResultId($medicalResult['id']);
                         foreach ($items as $item) {
                             if (!empty($item['assignee_name'])) {
                                 $koordinator_hasil[] = $item['assignee_name'];
                             }
                         }
                     }
                     $project['koordinator_hasil'] = !empty($koordinator_hasil) ? implode(', ', array_unique($koordinator_hasil)) : '-';

                     // Fetch Staff Assignments
                     $manpowerModel = $this->loadModel('ProjectManPower');
                     $project['staff_assignments'] = $manpowerModel->getAssignments($_GET['id']);
                     if (!empty($project['staff_assignments'])) {
                         foreach ($project['staff_assignments'] as &$sa) {
                             $sa['formatted_date'] = DateHelper::formatIndonesianDate($sa['date']);
                         }
                     }

                     // Fetch DW Realizations
                     $realizationModel = $this->loadModel('MedicalResultRealization');
                     $project['realizations'] = $realizationModel->getByProjectId($_GET['id']);
                     if (!empty($project['realizations'])) {
                         foreach ($project['realizations'] as &$r) {
                             $r['formatted_date'] = DateHelper::formatIndonesianDate($r['date']);
                         }
                     }

                     $this->jsonResponse($project);
                 } else {
                     $this->jsonResponse(['error' => 'Project not found'], 404);
                 }
             } catch (Exception $e) {
                 $this->jsonResponse(['error' => $e->getMessage()], 500);
             }
         } else {
             $this->jsonResponse(['error' => 'No ID provided'], 400);
         }
    }
}
