<?php
/**
 * QR Attendance Handler
 * Handles QR code-based attendance marking through the API router
 */

// This is called by the API router, so session and headers are already set
// Just include our existing QR attendance logic

require_once __DIR__ . '/../../public/api/qr-attendance.php';
