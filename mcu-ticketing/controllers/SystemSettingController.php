<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/SystemSetting.php';

class SystemSettingController extends BaseController {
    // private $db; // Inherited from BaseController
    private $setting;

    public function __construct() {
        parent::__construct();

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
            header("Location: index.php?page=login");
            exit;
        }

        // $this->db is initialized in BaseController
        $this->setting = new SystemSetting($this->db);
    }

    public function index() {
        // Define standard settings with descriptions
        $standard_settings = [
            'company_address' => [
                'value' => 'Jl. TB Simatupang No. 1, Jakarta Selatan',
                'description' => 'Alamat perusahaan yang muncul di header surat'
            ],
            'vendor_memo_signer_2_name' => [
                'value' => '',
                'description' => 'Nama penanda tangan 2 (Approved By - Kanan Atas)'
            ],
            'vendor_memo_signer_2_title' => [
                'value' => 'Head of Operations',
                'description' => 'Jabatan penanda tangan 2 (Approved By - Kanan Atas)'
            ],
            'doctor_max_patient' => [
                'value' => '50',
                'description' => 'Kapasitas maksimal pasien per dokter (untuk perhitungan overload)'
            ],
            'doctor_extra_fee' => [
                'value' => '15000',
                'description' => 'Fee tambahan per pasien jika melebihi kapasitas (Overload Fee)'
            ],
            'tat_normal_days' => [
                'value' => '3',
                'description' => 'Standard Turn Around Time (days)'
            ],
            'tat_calculation_mode' => [
                'value' => 'calendar',
                'description' => 'TAT Calculation Mode: calendar or working_days'
            ],
            'tat_config_rules' => [
                'value' => '[]',
                'description' => 'Configuration Rules for TAT based on Exam Keywords (JSON)'
            ],
            'rab_items_consumption' => [
                'value' => '[{"name":"Air Mineral Petugas","expense_code":"EXP-CONS-WTR"},{"name":"Lunch Petugas","expense_code":"EXP-CONS-LUNCH-STF"},{"name":"Snack Peserta","expense_code":"EXP-CONS-SNACK"},{"name":"Lunch Peserta","expense_code":"EXP-CONS-LUNCH-PTC"}]',
                'description' => 'Daftar item konsumsi (JSON Format)'
            ],
            'rab_items_transport' => [
                'value' => '[{"name":"BBM","expense_code":"EXP-TRANS-FUEL"},{"name":"Tol","expense_code":"EXP-TRANS-TOLL"},{"name":"Parkir","expense_code":"EXP-TRANS-PARK"},{"name":"Lainnya","expense_code":"EXP-TRANS-OTHERS"}]',
                'description' => 'Daftar item transportasi (JSON Format)'
            ],
            'rab_personnel_codes' => [
                'value' => '{"admin":"EXP-PERS-ADM","ttv":"EXP-PERS-TTV","visus":"EXP-PERS-VIS","plebo":"EXP-PERS-PLE","dokter":"EXP-PERS-DOC","driver":"EXP-PERS-DRV","pj":"EXP-PERS-PJ","pcr":"EXP-PERS-PCR","swab":"EXP-PERS-SWAB"}',
                'description' => 'Mapping Expense Code untuk Petugas (Role -> Code, JSON Object)'
            ],
            'lark_link' => [
                'value' => 'https://www.larksuite.com',
                'description' => 'Link akses Lark / Feishu untuk koordinasi konsumsi'
            ]
        ];

        // Legacy keys to migrate/cleanup
        $legacy_map = [
            'vendor_memo_approved_by_1_name' => 'vendor_memo_signer_2_name',
            'vendor_memo_approved_by_1_title' => 'vendor_memo_signer_2_title'
        ];

        // Keys to completely remove (unused)
        $unused_keys = [
            'po_approved_by',
            'vendor_memo_approved_by',
            'vendor_memo_signer_1_title',
            'vendor_memo_signer_3_name',
            'vendor_memo_signer_3_title',
            'vendor_memo_signer_4_name',
            'vendor_memo_signer_4_title',
            'vendor_memo_prepared_by_title',
            'vendor_memo_approved_by_2_name',
            'vendor_memo_approved_by_2_title',
            'vendor_memo_signer_4_label',
            'vendor_memo_signer_4_role'
        ];

        // Cleanup Unused Keys
        foreach ($unused_keys as $key) {
            if ($this->setting->get($key) !== null) {
                $this->setting->delete($key);
            }
        }

        // Migration for TAT Vendor (Convert old keywords/days to new rules format)
        $current_tat_keywords = $this->setting->get('tat_vendor_keywords');
        $current_tat_days = $this->setting->get('tat_vendor_days');
        
        if ($current_tat_keywords !== null && $this->setting->get('tat_config_rules') === null) {
             $keywords = explode(',', $current_tat_keywords);
             $rules = [];
             foreach($keywords as $k) {
                 if(trim($k)) {
                     $rules[] = ['keyword' => trim($k), 'days' => $current_tat_days ?? 7];
                 }
             }
             $this->setting->setting_key = 'tat_config_rules';
             $this->setting->setting_value = json_encode($rules);
             $this->setting->description = $standard_settings['tat_config_rules']['description'];
             $this->setting->create();
             
             // Delete old keys
             $this->setting->delete('tat_vendor_keywords');
             $this->setting->delete('tat_vendor_days');
        }

        // Migration Logic
        foreach ($legacy_map as $old_key => $new_key) {
            $old_val = $this->setting->get($old_key);
            if ($old_val !== null) {
                // If new key doesn't exist, create it with old value
                if ($this->setting->get($new_key) === null) {
                    $this->setting->setting_key = $new_key;
                    $this->setting->setting_value = $old_val;
                    $this->setting->description = $standard_settings[$new_key]['description'];
                    $this->setting->create();
                }
                // Delete old key
                $this->setting->delete($old_key);
            }
        }

        // Ensure all standard settings exist and update descriptions
        foreach ($standard_settings as $key => $data) {
             if ($this->setting->get($key) === null) {
                $this->setting->setting_key = $key;
                $this->setting->setting_value = $data['value'];
                $this->setting->description = $data['description'];
                $this->setting->create();
             } else {
                 // Update description to standard one
                 $this->setting->updateDescription($key, $data['description']);
             }
        }

        $stmt = $this->setting->getAll();
        $all_settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Grouping Logic
        $grouped_settings = [
            'General Settings' => [],
            'RAB Configuration' => [],
            'IM / Project Documents' => [],
            'Medical Result Configuration' => []
        ];

        foreach ($all_settings as $item) {
            if (strpos($item['setting_key'], 'fee_') === 0 || strpos($item['setting_key'], 'doctor_') === 0 || strpos($item['setting_key'], 'rab_') === 0) {
                $grouped_settings['RAB Configuration'][] = $item;
            } elseif (strpos($item['setting_key'], 'vendor_memo_') === 0) {
                $grouped_settings['IM / Project Documents'][] = $item;
            } elseif (strpos($item['setting_key'], 'tat_') === 0) {
                $grouped_settings['Medical Result Configuration'][] = $item;
            } else {
                $grouped_settings['General Settings'][] = $item;
            }
        }
        
        // Sort General Settings to put company_address first
        usort($grouped_settings['General Settings'], function($a, $b) {
            if ($a['setting_key'] === 'company_address') return -1;
            if ($b['setting_key'] === 'company_address') return 1;
            return strcmp($a['setting_key'], $b['setting_key']);
        });
        
        include '../views/superadmin/settings/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token.");
            }
            foreach ($_POST['settings'] as $key => $value) {
                // Strip dots from fee/currency settings (convert 100.000 to 100000)
                if (strpos($key, 'fee_') === 0 || strpos($key, 'doctor_') === 0) {
                    $value = str_replace('.', '', $value);
                }
                
                $this->setting->setting_key = $key;
                $this->setting->setting_value = $value;
                $this->setting->update();
            }
            header("Location: index.php?page=settings&status=updated");
        }
    }

    public function deleteRabRole() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token.");
            }

            $roleKey = $_POST['role_key'] ?? '';
            if (empty($roleKey)) {
                 header("Location: index.php?page=settings&error=Invalid role key");
                 exit;
            }

            // 1. Remove from rab_personnel_codes
            $currentCodes = $this->setting->get('rab_personnel_codes');
            $json = json_decode($currentCodes, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                if (isset($json[$roleKey])) {
                    unset($json[$roleKey]);
                    $this->setting->setting_key = 'rab_personnel_codes';
                    $this->setting->setting_value = json_encode($json);
                    $this->setting->update();
                }
            }

            // 2. Delete Fee Settings
            $keys = [
                'fee_dalam_kota_' . $roleKey,
                'fee_luar_kota_' . $roleKey
            ];

            foreach ($keys as $key) {
                if ($this->setting->get($key) !== null) {
                    $this->setting->delete($key);
                }
            }

            header("Location: index.php?page=settings&status=role_deleted");
            exit;
        }
    }

    public function addRabRole() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token.");
            }

            $roleName = trim($_POST['role_name']);
            if (empty($roleName)) {
                 header("Location: index.php?page=settings&error=Role name cannot be empty");
                 exit;
            }

            // Generate key (e.g. "Dokter Gigi" -> "dokter_gigi")
            $roleKey = strtolower(str_replace(' ', '_', $roleName));
            $roleKey = preg_replace('/[^a-z0-9_]/', '', $roleKey); // Sanitize

            // 1. Update rab_personnel_codes
            $currentCodes = $this->setting->get('rab_personnel_codes');
            
            // Try to parse as JSON first
            $json = json_decode($currentCodes, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                if (!isset($json[$roleKey])) {
                    $json[$roleKey] = $roleName;
                }
                $newValue = json_encode($json);
            } else {
                // Line-based fallback
                if (strpos($currentCodes, "$roleKey=") === false) {
                    $newValue = trim($currentCodes) . "\n$roleKey=$roleName";
                } else {
                    $newValue = $currentCodes;
                }
            }
            
            // Update the setting
            $this->setting->setting_key = 'rab_personnel_codes';
            $this->setting->setting_value = $newValue;
            $this->setting->update();

            // 2. Create Fee Settings
            $fees = [
                'fee_dalam_kota_' . $roleKey => 0,
                'fee_luar_kota_' . $roleKey => 0
            ];

            foreach ($fees as $key => $val) {
                if ($this->setting->get($key) === null) {
                    $this->setting->setting_key = $key;
                    $this->setting->setting_value = $val;
                    $this->setting->description = "Fee " . str_replace('_', ' ', str_replace('fee_', '', $key));
                    $this->setting->create();
                }
            }

            header("Location: index.php?page=settings&status=role_added");
            exit;
        }
    }
}