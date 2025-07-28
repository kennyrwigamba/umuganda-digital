<?php
/**
 * Authentication Controller
 * Handles login, logout, and registration
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Handle login request
     */
    public function postLogin()
    {
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (! $input) {
                $input = $_POST;
            }

            // Validate input
            if (empty($input['email']) || empty($input['password'])) {
                errorResponse('Email and password are required');
            }

            // Verify CSRF token for non-AJAX requests
            if (! isAjaxRequest() && ! verifyCSRFToken($input['csrf_token'] ?? '')) {
                errorResponse('Invalid CSRF token', 403);
            }

            $email    = sanitize($input['email']);
            $password = $input['password'];

            // Validate email format
            if (! isValidEmail($email)) {
                errorResponse('Invalid email format');
            }

            // Attempt authentication
            $user = $this->userModel->authenticate($email, $password);

            if (! $user) {
                logActivity("Failed login attempt for email: $email", 'warning');
                errorResponse('Invalid email or password');
            }

            // Check if user is active
            if ($user['status'] !== 'active') {
                errorResponse('Account is not active');
            }

            // Set session variables
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];
            $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['login_time'] = time();

            // Log successful login
            logActivity("User logged in: {$user['email']}", 'info');

            // Return success response
            successResponse([
                'user'     => [
                    'id'    => $user['id'],
                    'email' => $user['email'],
                    'name'  => $user['first_name'] . ' ' . $user['last_name'],
                    'role'  => $user['role'],
                ],
                'redirect' => $user['role'] === 'admin' ? 'dashboard/admin/index.php' : 'dashboard/resident/index.php',
            ], 'Login successful');

        } catch (Exception $e) {
            logActivity('Login error: ' . $e->getMessage(), 'error');
            errorResponse('Login failed', 500);
        }
    }

    /**
     * Handle registration request
     */
    public function postRegister()
    {
        try {
            // Get form input (registration will use form data, not JSON)
            $input = $_POST;

            // Validate required fields for basic registration
            $required_fields = ['first_name', 'last_name', 'email', 'phone', 'national_id', 'password', 'confirm_password'];

            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    errorResponse("Field '$field' is required");
                }
            }

            // Sanitize input
            $data = [];
            foreach ($input as $key => $value) {
                if ($key !== 'password' && $key !== 'confirm_password') {
                    $data[$key] = sanitize($value);
                } else {
                    $data[$key] = $value;
                }
            }

            // Validate email format
            if (! isValidEmail($data['email'])) {
                errorResponse('Invalid email format');
            }

            // Validate password strength
            if (strlen($data['password']) < 6) {
                errorResponse('Password must be at least 6 characters long');
            }

            // Check password confirmation
            if ($data['password'] !== $data['confirm_password']) {
                errorResponse('Passwords do not match');
            }

            // Check if email already exists
            if ($this->userModel->emailExists($data['email'])) {
                errorResponse('Email already exists');
            }

            // Check if national ID already exists
            if ($this->userModel->nationalIdExists($data['national_id'])) {
                errorResponse('National ID already exists');
            }

            // Validate national ID format (Rwanda national ID is 16 digits)
            if (! preg_match('/^\d{16}$/', $data['national_id'])) {
                errorResponse('Invalid national ID format');
            }

            // Validate phone number
            if (! preg_match('/^(\+?25)?[0-9]{9,10}$/', $data['phone'])) {
                errorResponse('Invalid phone number format');
            }

            // Hash password
            $data['password'] = hashPassword($data['password']);

            // Remove confirm_password
            unset($data['confirm_password']);

            // Set default values - only residents can register
            $data['role']   = 'resident';
            $data['status'] = 'active';

            // Set default location values (can be updated later in profile)
            $data['cell']          = 'Not Set';
            $data['sector']        = 'Not Set';
            $data['district']      = 'Not Set';
            $data['province']      = 'Not Set';
            $data['date_of_birth'] = '1990-01-01'; // Default birth date - user can update later
            $data['gender']        = 'Not Set';
            $data['created_at']    = date('Y-m-d H:i:s');

            // Create user
            $user_id = $this->userModel->create($data);

            if (! $user_id) {
                errorResponse('Failed to create user account');
            }

            // Log registration
            logActivity("New resident registered: {$data['email']} (ID: $user_id)", 'info');

            // Return success response
            successResponse([
                'user_id' => $user_id,
                'message' => 'Account created successfully',
            ], 'Registration successful');

        } catch (Exception $e) {
            logActivity('Registration error: ' . $e->getMessage(), 'error');
            errorResponse('Registration failed. Please try again.', 500);
        }
    }

    /**
     * Handle logout request
     */
    public function postLogout()
    {
        try {
            if (isLoggedIn()) {
                $user_email = $_SESSION['user_email'] ?? 'unknown';

                // Clear session
                session_unset();
                session_destroy();

                // Log logout
                logActivity("User logged out: $user_email", 'info');
            }

            successResponse([], 'Logged out successfully');

        } catch (Exception $e) {
            logActivity('Logout error: ' . $e->getMessage(), 'error');
            errorResponse('Logout failed', 500);
        }
    }

    /**
     * Check authentication status
     */
    public function getStatus()
    {
        try {
            if (isLoggedIn()) {
                $user = $this->userModel->findById($_SESSION['user_id']);

                if ($user && $user['status'] === 'active') {
                    successResponse([
                        'authenticated' => true,
                        'user'          => [
                            'id'    => $user['id'],
                            'email' => $user['email'],
                            'name'  => $user['first_name'] . ' ' . $user['last_name'],
                            'role'  => $user['role'],
                        ],
                    ]);
                } else {
                    // User account was deactivated or deleted
                    session_unset();
                    session_destroy();

                    successResponse([
                        'authenticated' => false,
                        'message'       => 'Account is no longer active',
                    ]);
                }
            } else {
                successResponse([
                    'authenticated' => false,
                ]);
            }

        } catch (Exception $e) {
            logActivity('Auth status check error: ' . $e->getMessage(), 'error');
            errorResponse('Failed to check authentication status', 500);
        }
    }

    /**
     * Generate CSRF token
     */
    public function getCsrfToken()
    {
        try {
            $token = generateCSRFToken();
            successResponse(['csrf_token' => $token]);

        } catch (Exception $e) {
            logActivity('CSRF token generation error: ' . $e->getMessage(), 'error');
            errorResponse('Failed to generate CSRF token', 500);
        }
    }

    /**
     * Handle password reset request
     */
    public function postResetPassword()
    {
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (! $input) {
                $input = $_POST;
            }

            // Validate input
            if (empty($input['email'])) {
                errorResponse('Email is required');
            }

            $email = sanitize($input['email']);

            if (! isValidEmail($email)) {
                errorResponse('Invalid email format');
            }

            // Check if user exists
            $user = $this->userModel->findByEmail($email);

            if (! $user) {
                // Don't reveal if email exists or not for security
                successResponse([], 'If the email exists, password reset instructions have been sent');
                return;
            }

            // In a real application, you would:
            // 1. Generate a secure reset token
            // 2. Store it in the database with expiration
            // 3. Send email with reset link

            // For now, just log the request
            logActivity("Password reset requested for: $email", 'info');

            successResponse([], 'If the email exists, password reset instructions have been sent');

        } catch (Exception $e) {
            logActivity('Password reset error: ' . $e->getMessage(), 'error');
            errorResponse('Password reset failed', 500);
        }
    }
}
