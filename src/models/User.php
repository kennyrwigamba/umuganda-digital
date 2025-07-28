<?php
/**
 * User Model
 * Handles user-related database operations
 */

require_once __DIR__ . '/../../config/db.php';

class User
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Create a new user
     */
    public function create($data)
    {
        $query = "INSERT INTO users (national_id, first_name, last_name, email, phone, password, cell, sector, district, province, date_of_birth, gender, role, status, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->execute($query, [
            $data['national_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['password'],
            $data['cell'] ?? 'Not Set',
            $data['sector'] ?? 'Not Set',
            $data['district'] ?? 'Not Set',
            $data['province'] ?? 'Not Set',
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? 'Not Set',
            $data['role'] ?? 'resident',
            $data['status'] ?? 'active',
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ], 'sssssssssssssss');

        $stmt->close();
        return $this->db->getLastInsertId();
    }

    /**
     * Find user by ID
     */
    public function findById($id)
    {
        $query = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($query, [$id], 'i');
    }

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $query = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetchOne($query, [$email], 's');
    }

    /**
     * Find user by national ID
     */
    public function findByNationalId($national_id)
    {
        $query = "SELECT * FROM users WHERE national_id = ?";
        return $this->db->fetchOne($query, [$national_id], 's');
    }

    /**
     * Authenticate user
     */
    public function authenticate($email, $password)
    {
        $user = $this->findByEmail($email);

        if ($user && verifyPassword($password, $user['password'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            return $user;
        }

        return false;
    }

    /**
     * Update user information
     */
    public function update($id, $data)
    {
        $set_clauses = [];
        $params      = [];
        $types       = '';

        foreach ($data as $field => $value) {
            if ($field !== 'id') {
                $set_clauses[] = "$field = ?";
                $params[]      = $value;
                $types .= 's';
            }
        }

        if (empty($set_clauses)) {
            return false;
        }

        $set_clause = implode(', ', $set_clauses);
        $query      = "UPDATE users SET $set_clause, updated_at = NOW() WHERE id = ?";

        $params[] = $id;
        $types .= 'i';

        $stmt     = $this->db->execute($query, $params, $types);
        $affected = $this->db->getAffectedRows();
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin($id)
    {
        $query = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt  = $this->db->execute($query, [$id], 'i');
        $stmt->close();
    }

    /**
     * Change user password
     */
    public function changePassword($id, $new_password)
    {
        $hashed_password = hashPassword($new_password);
        $query           = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";

        $stmt     = $this->db->execute($query, [$hashed_password, $id], 'si');
        $affected = $this->db->getAffectedRows();
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Get all users with filters
     */
    public function getAll($filters = [])
    {
        $where_clauses = [];
        $params        = [];
        $types         = '';

        // Base query
        $query = "SELECT id, national_id, first_name, last_name, email, phone, cell, sector, district, province,
                         date_of_birth, gender, role, status, created_at, updated_at, last_login
                  FROM users";

        // Apply filters
        if (! empty($filters['role'])) {
            $where_clauses[] = "role = ?";
            $params[]        = $filters['role'];
            $types .= 's';
        }

        if (! empty($filters['status'])) {
            $where_clauses[] = "status = ?";
            $params[]        = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['cell'])) {
            $where_clauses[] = "cell = ?";
            $params[]        = $filters['cell'];
            $types .= 's';
        }

        if (! empty($filters['sector'])) {
            $where_clauses[] = "sector = ?";
            $params[]        = $filters['sector'];
            $types .= 's';
        }

        if (! empty($filters['search'])) {
            $where_clauses[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR national_id LIKE ?)";
            $search_term     = '%' . $filters['search'] . '%';
            $params          = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
            $types .= 'ssss';
        }

        // Add WHERE clause if filters exist
        if (! empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        // Add ordering
        $order_by  = $filters['order_by'] ?? 'created_at';
        $order_dir = $filters['order_dir'] ?? 'DESC';
        $query .= " ORDER BY $order_by $order_dir";

        // Add pagination
        if (isset($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = (int) $filters['limit'];
            $types .= 'i';

            if (isset($filters['offset'])) {
                $query .= " OFFSET ?";
                $params[] = (int) $filters['offset'];
                $types .= 'i';
            }
        }

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Get user count with filters
     */
    public function getCount($filters = [])
    {
        $where_clauses = [];
        $params        = [];
        $types         = '';

        $query = "SELECT COUNT(*) as total FROM users";

        // Apply same filters as getAll method
        if (! empty($filters['role'])) {
            $where_clauses[] = "role = ?";
            $params[]        = $filters['role'];
            $types .= 's';
        }

        if (! empty($filters['status'])) {
            $where_clauses[] = "status = ?";
            $params[]        = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['cell'])) {
            $where_clauses[] = "cell = ?";
            $params[]        = $filters['cell'];
            $types .= 's';
        }

        if (! empty($filters['search'])) {
            $where_clauses[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR national_id LIKE ?)";
            $search_term     = '%' . $filters['search'] . '%';
            $params          = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
            $types .= 'ssss';
        }

        if (! empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $result = $this->db->fetchOne($query, $params, $types);
        return (int) $result['total'];
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $query    = "DELETE FROM users WHERE id = ?";
        $stmt     = $this->db->execute($query, [$id], 'i');
        $affected = $this->db->getAffectedRows();
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $exclude_id = null)
    {
        $query  = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        $types  = 's';

        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= 'i';
        }

        $result = $this->db->fetchOne($query, $params, $types);
        return (int) $result['count'] > 0;
    }

    /**
     * Check if national ID exists
     */
    public function nationalIdExists($national_id, $exclude_id = null)
    {
        $query  = "SELECT COUNT(*) as count FROM users WHERE national_id = ?";
        $params = [$national_id];
        $types  = 's';

        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= 'i';
        }

        $result = $this->db->fetchOne($query, $params, $types);
        return (int) $result['count'] > 0;
    }

    /**
     * Verify user password
     */
    public function verifyPassword($user_id, $password)
    {
        $query = "SELECT password FROM users WHERE id = ?";
        $user  = $this->db->fetchOne($query, [$user_id], 'i');

        if ($user) {
            return password_verify($password, $user['password']);
        }
        return false;
    }

    /**
     * Update user password
     */
    public function updatePassword($user_id, $new_password)
    {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query           = "UPDATE users SET password = ?, updated_at = ? WHERE id = ?";

        $stmt = $this->db->execute($query, [
            $hashed_password,
            date('Y-m-d H:i:s'),
            $user_id,
        ], 'ssi');

        $result = $stmt->affected_rows > 0;
        $stmt->close();
        return $result;
    }

    /**
     * Update user preferences
     */
    public function updatePreferences($user_id, $preferences)
    {
        $query = "UPDATE users SET preferences = ?, updated_at = ? WHERE id = ?";

        $stmt = $this->db->execute($query, [
            json_encode($preferences),
            date('Y-m-d H:i:s'),
            $user_id,
        ], 'ssi');

        $result = $stmt->affected_rows > 0;
        $stmt->close();
        return $result;
    }
}
