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
        $query = "INSERT INTO users (national_id, first_name, last_name, email, phone, password,
                         cell_id, sector_id, district_id, province_id, cell, sector, district, province,
                         date_of_birth, gender, role, status, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->execute($query, [
            $data['national_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['password'],
            $data['cell_id'] ?? null,
            $data['sector_id'] ?? null,
            $data['district_id'] ?? null,
            $data['province_id'] ?? null,
            $data['cell'] ?? 'Not Set',
            $data['sector'] ?? 'Not Set',
            $data['district'] ?? 'Not Set',
            $data['province'] ?? 'Not Set',
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? 'Not Set',
            $data['role'] ?? 'resident',
            $data['status'] ?? 'active',
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ], 'ssssssiiissssssssss');

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

        // Base query - include location hierarchy
        $query = "SELECT u.id, u.national_id, u.first_name, u.last_name, u.email, u.phone,
                         u.cell_id, u.sector_id, u.district_id, u.province_id,
                         u.cell, u.sector, u.district, u.province,
                         c.name as cell_name, s.name as sector_name,
                         d.name as district_name, p.name as province_name,
                         u.date_of_birth, u.gender, u.role, u.status,
                         u.created_at, u.updated_at, u.last_login
                  FROM users u
                  LEFT JOIN cells c ON u.cell_id = c.id
                  LEFT JOIN sectors s ON u.sector_id = s.id
                  LEFT JOIN districts d ON u.district_id = d.id
                  LEFT JOIN provinces p ON u.province_id = p.id";

        // Apply filters
        if (! empty($filters['role'])) {
            $where_clauses[] = "u.role = ?";
            $params[]        = $filters['role'];
            $types .= 's';
        }

        if (! empty($filters['status'])) {
            $where_clauses[] = "u.status = ?";
            $params[]        = $filters['status'];
            $types .= 's';
        }

        // Updated to use new location hierarchy
        if (! empty($filters['cell_id'])) {
            $where_clauses[] = "u.cell_id = ?";
            $params[]        = $filters['cell_id'];
            $types .= 'i';
        }

        if (! empty($filters['sector_id'])) {
            $where_clauses[] = "u.sector_id = ?";
            $params[]        = $filters['sector_id'];
            $types .= 'i';
        }

        if (! empty($filters['district_id'])) {
            $where_clauses[] = "u.district_id = ?";
            $params[]        = $filters['district_id'];
            $types .= 'i';
        }

        if (! empty($filters['province_id'])) {
            $where_clauses[] = "u.province_id = ?";
            $params[]        = $filters['province_id'];
            $types .= 'i';
        }

        // Legacy filters for backward compatibility
        if (! empty($filters['cell'])) {
            $where_clauses[] = "u.cell = ?";
            $params[]        = $filters['cell'];
            $types .= 's';
        }

        if (! empty($filters['sector'])) {
            $where_clauses[] = "u.sector = ?";
            $params[]        = $filters['sector'];
            $types .= 's';
        }

        if (! empty($filters['search'])) {
            $where_clauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.national_id LIKE ?)";
            $search_term     = '%' . $filters['search'] . '%';
            $params          = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
            $types .= 'ssss';
        }

        // Add WHERE clause if filters exist
        if (! empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        // Add ordering
        $order_by  = $filters['order_by'] ?? 'u.created_at';
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

        // Updated to use new location hierarchy
        if (! empty($filters['cell_id'])) {
            $where_clauses[] = "cell_id = ?";
            $params[]        = $filters['cell_id'];
            $types .= 'i';
        }

        if (! empty($filters['sector_id'])) {
            $where_clauses[] = "sector_id = ?";
            $params[]        = $filters['sector_id'];
            $types .= 'i';
        }

        if (! empty($filters['district_id'])) {
            $where_clauses[] = "district_id = ?";
            $params[]        = $filters['district_id'];
            $types .= 'i';
        }

        if (! empty($filters['province_id'])) {
            $where_clauses[] = "province_id = ?";
            $params[]        = $filters['province_id'];
            $types .= 'i';
        }

        // Legacy filters for backward compatibility
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

    /**
     * Update user location using the new hierarchy
     */
    public function updateLocation($user_id, $cell_id)
    {
        // Get the full location hierarchy for the cell
        $query = "SELECT c.id as cell_id, s.id as sector_id, d.id as district_id, p.id as province_id,
                         c.name as cell_name, s.name as sector_name, d.name as district_name, p.name as province_name
                  FROM cells c
                  JOIN sectors s ON c.sector_id = s.id
                  JOIN districts d ON s.district_id = d.id
                  JOIN provinces p ON d.province_id = p.id
                  WHERE c.id = ?";

        $hierarchy = $this->db->fetchOne($query, [$cell_id], 'i');

        if (! $hierarchy) {
            throw new Exception('Invalid cell ID');
        }

        // Update user with both new hierarchy IDs and legacy string fields
        $update_query = "UPDATE users SET
                        cell_id = ?, sector_id = ?, district_id = ?, province_id = ?,
                        cell = ?, sector = ?, district = ?, province = ?,
                        updated_at = NOW()
                        WHERE id = ?";

        $stmt = $this->db->execute($update_query, [
            $hierarchy['cell_id'],
            $hierarchy['sector_id'],
            $hierarchy['district_id'],
            $hierarchy['province_id'],
            $hierarchy['cell_name'],
            $hierarchy['sector_name'],
            $hierarchy['district_name'],
            $hierarchy['province_name'],
            $user_id,
        ], 'iiiisssssi');

        $result = $stmt->affected_rows > 0;
        $stmt->close();
        return $result;
    }

    /**
     * Get user with full location hierarchy
     */
    public function findByIdWithLocation($user_id)
    {
        $query = "SELECT u.*,
                         c.name as cell_name, c.code as cell_code,
                         s.name as sector_name, s.code as sector_code,
                         d.name as district_name, d.code as district_code,
                         p.name as province_name, p.code as province_code
                  FROM users u
                  LEFT JOIN cells c ON u.cell_id = c.id
                  LEFT JOIN sectors s ON u.sector_id = s.id
                  LEFT JOIN districts d ON u.district_id = d.id
                  LEFT JOIN provinces p ON u.province_id = p.id
                  WHERE u.id = ?";

        return $this->db->fetchOne($query, [$user_id], 'i');
    }

    /**
     * Get users by sector ID (for admin management)
     */
    public function getBySectorId($sector_id, $filters = [])
    {
        $where_clauses = ['u.sector_id = ?'];
        $params        = [$sector_id];
        $types         = 'i';

        // Additional filters
        if (! empty($filters['status'])) {
            $where_clauses[] = 'u.status = ?';
            $params[]        = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['role'])) {
            $where_clauses[] = 'u.role = ?';
            $params[]        = $filters['role'];
            $types .= 's';
        }

        if (! empty($filters['search'])) {
            $where_clauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.national_id LIKE ?)";
            $search_term     = '%' . $filters['search'] . '%';
            $params          = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
            $types .= 'ssss';
        }

        $where_clause = implode(' AND ', $where_clauses);

        $query = "SELECT u.*,
                         c.name as cell_name, s.name as sector_name,
                         d.name as district_name, p.name as province_name
                  FROM users u
                  LEFT JOIN cells c ON u.cell_id = c.id
                  LEFT JOIN sectors s ON u.sector_id = s.id
                  LEFT JOIN districts d ON u.district_id = d.id
                  LEFT JOIN provinces p ON u.province_id = p.id
                  WHERE {$where_clause}
                  ORDER BY u.last_name, u.first_name";

        // Add pagination if provided
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
     * Get users by cell ID
     */
    public function getByCellId($cell_id, $filters = [])
    {
        $where_clauses = ['u.cell_id = ?'];
        $params        = [$cell_id];
        $types         = 'i';

        // Additional filters
        if (! empty($filters['status'])) {
            $where_clauses[] = 'u.status = ?';
            $params[]        = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['role'])) {
            $where_clauses[] = 'u.role = ?';
            $params[]        = $filters['role'];
            $types .= 's';
        }

        $where_clause = implode(' AND ', $where_clauses);

        $query = "SELECT u.*,
                         c.name as cell_name, s.name as sector_name,
                         d.name as district_name, p.name as province_name
                  FROM users u
                  LEFT JOIN cells c ON u.cell_id = c.id
                  LEFT JOIN sectors s ON u.sector_id = s.id
                  LEFT JOIN districts d ON u.district_id = d.id
                  LEFT JOIN provinces p ON u.province_id = p.id
                  WHERE {$where_clause}
                  ORDER BY u.last_name, u.first_name";

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Get location statistics for a specific area
     */
    public function getLocationStats($location_id, $location_type)
    {
        $where_condition = '';
        switch ($location_type) {
            case 'province':
                $where_condition = 'u.province_id = ?';
                break;
            case 'district':
                $where_condition = 'u.district_id = ?';
                break;
            case 'sector':
                $where_condition = 'u.sector_id = ?';
                break;
            case 'cell':
                $where_condition = 'u.cell_id = ?';
                break;
            default:
                throw new Exception('Invalid location type');
        }

        $query = "SELECT
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_users,
                    COUNT(CASE WHEN u.status = 'inactive' THEN 1 END) as inactive_users,
                    COUNT(CASE WHEN u.status = 'suspended' THEN 1 END) as suspended_users,
                    COUNT(CASE WHEN u.role = 'resident' THEN 1 END) as residents,
                    COUNT(CASE WHEN u.role = 'admin' THEN 1 END) as admins
                  FROM users u
                  WHERE {$where_condition}";

        return $this->db->fetchOne($query, [$location_id], 'i');
    }

    /**
     * Get unique locations for dropdown population
     */
    public function getUniqueLocations($type)
    {
        $query = '';
        switch ($type) {
            case 'provinces':
                $query = "SELECT DISTINCT p.id, p.name FROM users u
                         JOIN provinces p ON u.province_id = p.id
                         ORDER BY p.name";
                break;
            case 'districts':
                $query = "SELECT DISTINCT d.id, d.name FROM users u
                         JOIN districts d ON u.district_id = d.id
                         ORDER BY d.name";
                break;
            case 'sectors':
                $query = "SELECT DISTINCT s.id, s.name FROM users u
                         JOIN sectors s ON u.sector_id = s.id
                         ORDER BY s.name";
                break;
            case 'cells':
                $query = "SELECT DISTINCT c.id, c.name FROM users u
                         JOIN cells c ON u.cell_id = c.id
                         ORDER BY c.name";
                break;
            default:
                throw new Exception('Invalid location type');
        }

        return $this->db->fetchAll($query, [], '');
    }
}
