<?php
/**
 * Standalone Public Project Detail Handler
 * This file handles project detail requests for the public calendar bypasses session auth
 */

// Error handling to prevent HTML in JSON output
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Project.php';
    require_once __DIR__ . '/../models/TechnicalMeeting.php';

    $database = new Database();
    $db = $database->getConnection();

    $projectModel = new Project($db);
    $tmModel = new TechnicalMeeting($db);

    $id = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$id) {
        echo json_encode(['error' => 'No ID provided']);
        exit;
    }

    $project_data = $projectModel->getProjectById($id);
    if (!$project_data) {
        echo json_encode(['error' => 'Project not found']);
        exit;
    }

    // Fetch Technical Meeting
    $technical_meeting = $tmModel->getByProject($id);

    // Return only public-safe fields with robust defaults
    $safe_data = [
        'project_id' => $project_data['project_id'] ?? 0,
        'nama_project' => $project_data['nama_project'] ?? '',
        'company_name' => $project_data['company_name'] ?? '',
        'sph_number' => $project_data['sph_number'] ?? '',
        'lunch' => $project_data['lunch'] ?? 'Tidak',
        'lunch_qty' => $project_data['lunch_qty'] ?? 0,
        'lunch_notes' => $project_data['lunch_notes'] ?? '',
        'lunch_items' => $project_data['lunch_items'] ?? '',
        'snack' => $project_data['snack'] ?? 'Tidak',
        'snack_qty' => $project_data['snack_qty'] ?? 0,
        'snack_notes' => $project_data['snack_notes'] ?? '',
        'snack_items' => $project_data['snack_items'] ?? '',
        'tanggal_mcu' => $project_data['tanggal_mcu'] ?? '',
        'tanggal_mcu_formatted' => $project_data['tanggal_mcu_formatted'] ?? ($project_data['tanggal_mcu'] ?? ''),
        'alamat' => $project_data['alamat'] ?? '',
        'total_peserta' => $project_data['total_peserta'] ?? 0,
        'sales_name' => $project_data['sales_name'] ?? '',
        'korlap_name' => $project_data['korlap_name'] ?? '',
        'koordinator_hasil' => $project_data['koordinator_hasil'] ?? '',
        'notes' => $project_data['notes'] ?? '',
        'header_footer' => $project_data['header_footer'] ?? 'Tidak',
        'foto_peserta' => $project_data['foto_peserta'] ?? 'Tidak',
        'jenis_pemeriksaan' => $project_data['jenis_pemeriksaan'] ?? '',
        'status_project' => $project_data['status_project'] ?? '',
        'technical_meeting' => $technical_meeting ? $technical_meeting : null,
        'rabs' => [],
        'approved_rab_id' => 0,
        'approved_rab_number' => '',
        'is_rab_approved' => false,
        'rab_status' => '',
        'vendor_allocations' => [],
        'history' => [],
        'staff_assignments' => [],
        'realizations' => []
    ];

    echo json_encode($safe_data);

} catch (Exception $e) {
    echo json_encode(['error' => 'Internal Server Error: ' . $e->getMessage()]);
}
exit;
