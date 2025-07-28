<?php
/**
 * Process Fine Payment
 * Handles fine payment processing
 */

session_start();

// Check if user is logged in
if (! isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Include required files
require_once __DIR__ . '/../../../src/models/Fine.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (! $input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
$requiredFields = ['fine_id', 'payment_method'];
foreach ($requiredFields as $field) {
    if (! isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$fineId        = (int) $input['fine_id'];
$paymentMethod = trim($input['payment_method']);
$phoneNumber   = isset($input['phone_number']) ? trim($input['phone_number']) : null;
$bankAccount   = isset($input['bank_account']) ? trim($input['bank_account']) : null;

try {
    $fineModel = new Fine();

    // Get the fine details first to verify it belongs to the user and is unpaid
    $fine = $fineModel->findById($fineId);

    if (! $fine) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Fine not found']);
        exit;
    }

    // Verify the fine belongs to the current user
    if ($fine['user_id'] != $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    // Check if fine is already paid
    if ($fine['status'] === 'paid') {
        echo json_encode(['success' => false, 'message' => 'Fine is already paid']);
        exit;
    }

    // Validate payment method specific fields
    if ($paymentMethod === 'mobile_money' && (! $phoneNumber || strlen($phoneNumber) < 10)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid phone number is required for mobile money payment']);
        exit;
    }

    if ($paymentMethod === 'bank_transfer' && (! $bankAccount || strlen($bankAccount) < 5)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid bank account is required for bank transfer']);
        exit;
    }

    // Generate payment reference
    $paymentReference = 'PAY-' . date('Ymd') . '-' . str_pad($fineId, 6, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

    // Prepare payment details based on method
    $paymentDetails = [];
    switch ($paymentMethod) {
        case 'mobile_money':
            $paymentDetails = [
                'phone_number' => $phoneNumber,
                'provider'     => 'MTN/Airtel',
            ];
            break;
        case 'bank_transfer':
            $paymentDetails = [
                'bank_account' => $bankAccount,
            ];
            break;
        case 'cash':
            $paymentDetails = [
                'location' => 'Local Office',
            ];
            break;
    }

    // Update the fine status to paid
    $additionalData = [
        'payment_method'    => $paymentMethod,
        'payment_reference' => $paymentReference,
    ];

    $success = $fineModel->updateStatus($fineId, 'paid', $additionalData);

    if ($success) {
        echo json_encode([
            'success'           => true,
            'message'           => 'Payment processed successfully',
            'payment_reference' => $paymentReference,
            'amount'            => $fine['amount'],
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to process payment']);
    }

} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing payment']);
}
