<?php
/**
 * Admin Manager Class
 * Handles sector-level administration and admin assignments
 */

class AdminManager
{
    private $db;
    private $locationManager;

    public function __construct($database, $locationManager = null)
    {
        $this->db              = $database;
        $this->locationManager = $locationManager ?: new LocationManager($database);
    }

    /**
     * Assign an admin to manage a sector
     */
    public function assignAdminToSector($adminId, $sectorId, $assignedBy, $notes = null)
    {
        // Validate admin user
        if (! $this->isValidAdmin($adminId)) {
            throw new Exception('User is not a valid admin');
        }

        // Validate sector
        if (! $this->isValidSector($sectorId)) {
            throw new Exception('Invalid sector ID');
        }

        try {
            $this->db->beginTransaction();

            // Deactivate any existing assignments for this admin-sector combination
            $stmt = $this->db->prepare("
                UPDATE admin_assignments
                SET is_active = 0
                WHERE admin_id = ? AND sector_id = ?
            ");
            $stmt->execute([$adminId, $sectorId]);

            // Create new assignment
            $stmt = $this->db->prepare("
                INSERT INTO admin_assignments (admin_id, sector_id, assigned_by, notes, is_active)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([$adminId, $sectorId, $assignedBy, $notes]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Remove admin assignment from a sector
     */
    public function removeAdminFromSector($adminId, $sectorId)
    {
        $stmt = $this->db->prepare("
            UPDATE admin_assignments
            SET is_active = 0
            WHERE admin_id = ? AND sector_id = ?
        ");
        return $stmt->execute([$adminId, $sectorId]);
    }

    /**
     * Get sectors assigned to an admin
     */
    public function getAdminSectors($adminId)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.id as sector_id,
                s.name as sector_name,
                s.code as sector_code,
                d.name as district_name,
                p.name as province_name,
                aa.assigned_at,
                aa.notes
            FROM admin_assignments aa
            JOIN sectors s ON aa.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            WHERE aa.admin_id = ? AND aa.is_active = 1
            ORDER BY s.name
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all residents that an admin can manage
     */
    public function getAdminManagedResidents($adminId, $filters = [])
    {
        $whereConditions = ['aa.admin_id = ?', 'aa.is_active = 1', 'u.role = "resident"'];
        $params          = [$adminId];

        // Add filters
        if (! empty($filters['status'])) {
            $whereConditions[] = 'u.status = ?';
            $params[]          = $filters['status'];
        }

        if (! empty($filters['sector_id'])) {
            $whereConditions[] = 's.id = ?';
            $params[]          = $filters['sector_id'];
        }

        if (! empty($filters['cell_id'])) {
            $whereConditions[] = 'c.id = ?';
            $params[]          = $filters['cell_id'];
        }

        if (! empty($filters['search'])) {
            $whereConditions[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.national_id LIKE ?)';
            $searchTerm        = '%' . $filters['search'] . '%';
            $params            = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = implode(' AND ', $whereConditions);

        $stmt = $this->db->prepare("
            SELECT DISTINCT
                u.id,
                u.national_id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.status,
                u.date_of_birth,
                u.gender,
                u.created_at,
                u.last_login,
                c.name as cell_name,
                s.name as sector_name,
                d.name as district_name,
                p.name as province_name
            FROM users u
            JOIN cells c ON u.cell_id = c.id
            JOIN sectors s ON c.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            JOIN admin_assignments aa ON s.id = aa.sector_id
            WHERE {$whereClause}
            ORDER BY s.name, c.name, u.last_name, u.first_name
        ");

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if admin can manage a specific resident
     */
    public function canAdminManageResident($adminId, $residentId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM users u
            JOIN cells c ON u.cell_id = c.id
            JOIN admin_assignments aa ON c.sector_id = aa.sector_id
            WHERE aa.admin_id = ? AND u.id = ? AND aa.is_active = 1
        ");
        $stmt->execute([$adminId, $residentId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if admin can manage a specific cell
     */
    public function canAdminManageCell($adminId, $cellId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM admin_assignments aa
            JOIN cells c ON aa.sector_id = c.sector_id
            WHERE aa.admin_id = ? AND c.id = ? AND aa.is_active = 1
        ");
        $stmt->execute([$adminId, $cellId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get admin statistics
     */
    public function getAdminStats($adminId)
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT s.id) as sectors_managed,
                COUNT(DISTINCT c.id) as cells_managed,
                COUNT(DISTINCT u.id) as total_residents,
                COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.id END) as active_residents,
                COUNT(DISTINCT CASE WHEN u.status = 'inactive' THEN u.id END) as inactive_residents,
                COUNT(DISTINCT CASE WHEN u.status = 'suspended' THEN u.id END) as suspended_residents
            FROM admin_assignments aa
            JOIN sectors s ON aa.sector_id = s.id
            LEFT JOIN cells c ON s.id = c.sector_id
            LEFT JOIN users u ON c.id = u.cell_id AND u.role = 'resident'
            WHERE aa.admin_id = ? AND aa.is_active = 1
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get detailed statistics by sector for an admin
     */
    public function getAdminSectorStats($adminId)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.id as sector_id,
                s.name as sector_name,
                d.name as district_name,
                p.name as province_name,
                COUNT(DISTINCT c.id) as total_cells,
                COUNT(u.id) as total_residents,
                COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_residents,
                COUNT(CASE WHEN u.status = 'inactive' THEN 1 END) as inactive_residents,
                COUNT(CASE WHEN u.status = 'suspended' THEN 1 END) as suspended_residents
            FROM admin_assignments aa
            JOIN sectors s ON aa.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            LEFT JOIN cells c ON s.id = c.sector_id
            LEFT JOIN users u ON c.id = u.cell_id AND u.role = 'resident'
            WHERE aa.admin_id = ? AND aa.is_active = 1
            GROUP BY s.id, d.id, p.id
            ORDER BY s.name
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all admins with their sector assignments
     */
    public function getAllAdminsWithSectors()
    {
        $stmt = $this->db->prepare("
            SELECT
                u.id as admin_id,
                u.first_name,
                u.last_name,
                u.email,
                u.status,
                GROUP_CONCAT(
                    CONCAT(s.name, ' (', d.name, ', ', p.name, ')')
                    ORDER BY s.name SEPARATOR '; '
                ) as sectors_managed,
                COUNT(aa.id) as sector_count
            FROM users u
            LEFT JOIN admin_assignments aa ON u.id = aa.admin_id AND aa.is_active = 1
            LEFT JOIN sectors s ON aa.sector_id = s.id
            LEFT JOIN districts d ON s.district_id = d.id
            LEFT JOIN provinces p ON d.province_id = p.id
            WHERE u.role = 'admin'
            GROUP BY u.id
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get events for sectors managed by admin
     */
    public function getAdminSectorEvents($adminId, $status = null)
    {
        $whereConditions = ['aa.admin_id = ?', 'aa.is_active = 1'];
        $params          = [$adminId];

        if ($status) {
            $whereConditions[] = 'e.status = ?';
            $params[]          = $status;
        }

        $whereClause = implode(' AND ', $whereConditions);

        $stmt = $this->db->prepare("
            SELECT DISTINCT
                e.id,
                e.title,
                e.description,
                e.event_date,
                e.start_time,
                e.end_time,
                e.location,
                e.status,
                s.name as sector_name,
                d.name as district_name,
                p.name as province_name
            FROM umuganda_events e
            JOIN sectors s ON e.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            JOIN admin_assignments aa ON s.id = aa.sector_id
            WHERE {$whereClause}
            ORDER BY e.event_date DESC, e.start_time
        ");

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get attendance statistics for admin's sectors
     */
    public function getAdminAttendanceStats($adminId, $eventId = null)
    {
        $whereConditions = ['aa.admin_id = ?', 'aa.is_active = 1'];
        $params          = [$adminId];

        if ($eventId) {
            $whereConditions[] = 'e.id = ?';
            $params[]          = $eventId;
        }

        $whereClause = implode(' AND ', $whereConditions);

        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT u.id) as total_residents,
                COUNT(DISTINCT att.id) as total_attendance_records,
                COUNT(DISTINCT CASE WHEN att.status = 'present' THEN att.id END) as present_count,
                COUNT(DISTINCT CASE WHEN att.status = 'absent' THEN att.id END) as absent_count,
                COUNT(DISTINCT CASE WHEN att.status = 'late' THEN att.id END) as late_count,
                COUNT(DISTINCT CASE WHEN att.status = 'excused' THEN att.id END) as excused_count
            FROM admin_assignments aa
            JOIN sectors s ON aa.sector_id = s.id
            JOIN cells c ON s.id = c.sector_id
            JOIN users u ON c.id = u.cell_id AND u.role = 'resident'
            LEFT JOIN attendance att ON u.id = att.user_id
            LEFT JOIN umuganda_events e ON att.event_id = e.id
            WHERE {$whereClause}
        ");

        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Validate if user is a valid admin
     */
    private function isValidAdmin($adminId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM users
            WHERE id = ? AND role = 'admin' AND status = 'active'
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Validate if sector exists
     */
    private function isValidSector($sectorId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sectors WHERE id = ?");
        $stmt->execute([$sectorId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Create new admin user and assign to sector
     */
    public function createAdminForSector($userData, $sectorId, $assignedBy)
    {
        try {
            $this->db->beginTransaction();

            // Validate sector
            if (! $this->isValidSector($sectorId)) {
                throw new Exception('Invalid sector ID');
            }

            // Get sector location hierarchy
            $sectorInfo = $this->locationManager->getLocationHierarchy($sectorId);
            if (! $sectorInfo) {
                throw new Exception('Could not get sector information');
            }

            // Create admin user
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    national_id, first_name, last_name, email, phone, password,
                    date_of_birth, gender, role, status,
                    sector_id, district_id, province_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin', 'active', ?, ?, ?)
            ");

            $stmt->execute([
                $userData['national_id'],
                $userData['first_name'],
                $userData['last_name'],
                $userData['email'],
                $userData['phone'],
                password_hash($userData['password'], PASSWORD_DEFAULT),
                $userData['date_of_birth'],
                $userData['gender'],
                $sectorId,
                $sectorInfo['district_id'],
                $sectorInfo['province_id'],
            ]);

            $adminId = $this->db->lastInsertId();

            // Assign admin to sector
            $this->assignAdminToSector($adminId, $sectorId, $assignedBy, 'Initial sector assignment');

            $this->db->commit();
            return $adminId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
