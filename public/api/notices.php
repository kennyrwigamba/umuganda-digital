<?php
/**
 * Notices API
 * Handles CRUD operations for community notices
 */

session_start();
header('Content-Type: application/json');

// Check authentication
if (! isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include required files
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/models/User.php';
require_once __DIR__ . '/../../src/models/Notice.php';

// Get database connection
global $db;
$connection = $db->getConnection();

$user   = new User();
$notice = new Notice();

// Get current admin info
$adminId   = $_SESSION['user_id'];
$adminInfo = $user->findById($adminId);

if (! $adminInfo) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

// Get admin's sector assignment
$adminSectorQuery = "
    SELECT aa.sector_id, s.name as sector_name
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    WHERE aa.admin_id = ? AND aa.is_active = 1
    LIMIT 1";

$stmt = $connection->prepare($adminSectorQuery);
$stmt->bind_param('i', $adminId);
$stmt->execute();
$adminSector = $stmt->get_result()->fetch_assoc();

if (! $adminSector) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin is not assigned to any sector']);
    exit;
}

$sectorId = $adminSector['sector_id'];

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            // Create new notice
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (empty($input['title']) || empty($input['content'])) {
                throw new Exception('Title and content are required');
            }

            // Prepare notice data
            $noticeData = [
                'title'           => trim($input['title']),
                'content'         => trim($input['content']),
                'type'            => $input['type'] ?? 'general',
                'priority'        => $input['priority'] ?? 'medium',
                'target_audience' => $input['target_audience'] ?? 'all',
                'sector_id'       => $sectorId,
                'publish_date'    => ! empty($input['publish_date']) ? $input['publish_date'] : null,
                'expiry_date'     => ! empty($input['expiry_date']) ? $input['expiry_date'] : null,
                'status'          => $input['status'] ?? 'draft',
                'created_by'      => $adminId,
            ];

            // Create notice using direct query (Notice model might have issues)
            $createQuery = "
                INSERT INTO notices (title, content, type, priority, target_audience, sector_id,
                                   publish_date, expiry_date, status, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $connection->prepare($createQuery);
            $stmt->bind_param('sssssisssi',
                $noticeData['title'],
                $noticeData['content'],
                $noticeData['type'],
                $noticeData['priority'],
                $noticeData['target_audience'],
                $noticeData['sector_id'],
                $noticeData['publish_date'],
                $noticeData['expiry_date'],
                $noticeData['status'],
                $noticeData['created_by']
            );

            if ($stmt->execute()) {
                $noticeId = $connection->insert_id;
                echo json_encode([
                    'success'   => true,
                    'message'   => 'Notice created successfully',
                    'notice_id' => $noticeId,
                ]);
            } else {
                throw new Exception('Failed to create notice');
            }
            break;

        case 'PUT':
            // Update existing notice
            $input    = json_decode(file_get_contents('php://input'), true);
            $noticeId = $input['id'] ?? null;

            if (! $noticeId) {
                throw new Exception('Notice ID is required for updates');
            }

            // Verify notice belongs to this admin
            $verifyQuery = "SELECT id FROM notices WHERE id = ? AND created_by = ?";
            $stmt        = $connection->prepare($verifyQuery);
            $stmt->bind_param('ii', $noticeId, $adminId);
            $stmt->execute();

            if (! $stmt->get_result()->fetch_assoc()) {
                throw new Exception('Notice not found or access denied');
            }

            // Update notice
            $updateQuery = "
                UPDATE notices
                SET title = ?, content = ?, type = ?, priority = ?, target_audience = ?,
                    publish_date = ?, expiry_date = ?, status = ?, updated_at = NOW()
                WHERE id = ? AND created_by = ?";

            $stmt = $connection->prepare($updateQuery);
            $stmt->bind_param('ssssssssii',
                $input['title'],
                $input['content'],
                $input['type'],
                $input['priority'],
                $input['target_audience'],
                $input['publish_date'],
                $input['expiry_date'],
                $input['status'],
                $noticeId,
                $adminId
            );

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Notice updated successfully']);
            } else {
                throw new Exception('Failed to update notice');
            }
            break;

        case 'DELETE':
            // Delete notice
            $noticeId = $_GET['id'] ?? null;

            if (! $noticeId) {
                throw new Exception('Notice ID is required for deletion');
            }

            // Delete notice (only if created by this admin)
            $deleteQuery = "DELETE FROM notices WHERE id = ? AND created_by = ?";
            $stmt        = $connection->prepare($deleteQuery);
            $stmt->bind_param('ii', $noticeId, $adminId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Notice deleted successfully']);
            } else {
                throw new Exception('Notice not found or access denied');
            }
            break;

        case 'GET':
            // Get single notice for editing
            $noticeId = $_GET['id'] ?? null;

            if (! $noticeId) {
                throw new Exception('Notice ID is required');
            }

            // Get notice details
            $getQuery = "
                SELECT * FROM notices
                WHERE id = ? AND created_by = ?";

            $stmt = $connection->prepare($getQuery);
            $stmt->bind_param('ii', $noticeId, $adminId);
            $stmt->execute();
            $noticeData = $stmt->get_result()->fetch_assoc();

            if ($noticeData) {
                echo json_encode(['success' => true, 'notice' => $noticeData]);
            } else {
                throw new Exception('Notice not found or access denied');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
