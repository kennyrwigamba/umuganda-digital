<?php
/**
 * Notice Model
 * Handles notices-related database operations
 */

require_once __DIR__ . '/../../config/db.php';

class Notice
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Create a new notice
     */
    public function create($data)
    {
        $query = "INSERT INTO notices (title, content, type, priority, target_audience, cell, sector, district, province, publish_date, expiry_date, status, created_by)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->execute($query, [
            $data['title'],
            $data['content'],
            $data['type'] ?? 'general',
            $data['priority'] ?? 'medium',
            $data['target_audience'] ?? 'all',
            $data['cell'] ?? null,
            $data['sector'] ?? null,
            $data['district'] ?? null,
            $data['province'] ?? null,
            $data['publish_date'] ?? null,
            $data['expiry_date'] ?? null,
            $data['status'] ?? 'draft',
            $data['created_by'],
        ], 'ssssssssssssi');

        $stmt->close();
        return $this->db->getLastInsertId();
    }

    /**
     * Find notice by ID
     */
    public function findById($id)
    {
        $query = "SELECT n.*, u.first_name, u.last_name
                  FROM notices n
                  LEFT JOIN users u ON n.created_by = u.id
                  WHERE n.id = ?";
        return $this->db->fetchOne($query, [$id], 'i');
    }

    /**
     * Get notices for a user based on their location and target audience
     */
    public function getNoticesForUser($user_id, $user_cell, $user_sector, $user_district, $user_role = 'resident', $limit = 10)
    {
        $query = "SELECT n.*, u.first_name as creator_first_name, u.last_name as creator_last_name,
                         nr.read_at,
                         CASE WHEN nr.user_id IS NOT NULL THEN 1 ELSE 0 END as is_read
                  FROM notices n
                  LEFT JOIN users u ON n.created_by = u.id
                  LEFT JOIN notice_reads nr ON n.id = nr.notice_id AND nr.user_id = ?
                  WHERE n.status = 'published'
                    AND (n.expiry_date IS NULL OR n.expiry_date > NOW())
                    AND (n.target_audience = 'all'
                         OR n.target_audience = ?
                         OR (n.target_audience = 'specific_location'
                             AND (n.cell = ? OR n.cell IS NULL)
                             AND (n.sector = ? OR n.sector IS NULL)
                             AND (n.district = ? OR n.district IS NULL)))
                  ORDER BY
                    CASE n.priority
                      WHEN 'critical' THEN 1
                      WHEN 'high' THEN 2
                      WHEN 'medium' THEN 3
                      WHEN 'low' THEN 4
                    END,
                    n.publish_date DESC
                  LIMIT ?";

        return $this->db->fetchAll($query, [
            $user_id,
            $user_role,
            $user_cell,
            $user_sector,
            $user_district,
            $limit,
        ], 'issssi');
    }

    /**
     * Get recent notices for a user
     */
    public function getRecentNoticesForUser($user_id, $user_cell, $user_sector, $user_district, $user_role = 'resident', $limit = 5)
    {
        return $this->getNoticesForUser($user_id, $user_cell, $user_sector, $user_district, $user_role, $limit);
    }

    /**
     * Get unread notices count for a user
     */
    public function getUnreadNoticesCount($user_id, $user_cell, $user_sector, $user_district, $user_role = 'resident')
    {
        $query = "SELECT COUNT(*) as unread_count
                  FROM notices n
                  LEFT JOIN notice_reads nr ON n.id = nr.notice_id AND nr.user_id = ?
                  WHERE n.status = 'published'
                    AND (n.expiry_date IS NULL OR n.expiry_date > NOW())
                    AND nr.user_id IS NULL
                    AND (n.target_audience = 'all'
                         OR n.target_audience = ?
                         OR (n.target_audience = 'specific_location'
                             AND (n.cell = ? OR n.cell IS NULL)
                             AND (n.sector = ? OR n.sector IS NULL)
                             AND (n.district = ? OR n.district IS NULL)))";

        $result = $this->db->fetchOne($query, [
            $user_id,
            $user_role,
            $user_cell,
            $user_sector,
            $user_district,
        ], 'issss');

        return $result ? $result['unread_count'] : 0;
    }

    /**
     * Mark notice as read by user
     */
    public function markAsRead($notice_id, $user_id)
    {
        $query = "INSERT INTO notice_reads (notice_id, user_id) VALUES (?, ?)
                  ON DUPLICATE KEY UPDATE read_at = NOW()";

        $stmt = $this->db->execute($query, [$notice_id, $user_id], 'ii');
        $stmt->close();
        return true;
    }

    /**
     * Get all notices with filters
     */
    public function getNotices($filters = [])
    {
        $where_conditions = [];
        $params           = [];
        $types            = '';

        if (! empty($filters['status'])) {
            $where_conditions[] = "n.status = ?";
            $params[]           = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['type'])) {
            $where_conditions[] = "n.type = ?";
            $params[]           = $filters['type'];
            $types .= 's';
        }

        if (! empty($filters['priority'])) {
            $where_conditions[] = "n.priority = ?";
            $params[]           = $filters['priority'];
            $types .= 's';
        }

        if (! empty($filters['created_by'])) {
            $where_conditions[] = "n.created_by = ?";
            $params[]           = $filters['created_by'];
            $types .= 'i';
        }

        $where_clause = ! empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $query = "SELECT n.*, u.first_name, u.last_name
                  FROM notices n
                  LEFT JOIN users u ON n.created_by = u.id
                  {$where_clause}
                  ORDER BY n.created_at DESC";

        if (! empty($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= 'i';
        }

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Update notice
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
        $query      = "UPDATE notices SET $set_clause, updated_at = NOW() WHERE id = ?";

        $params[] = $id;
        $types .= 'i';

        $stmt     = $this->db->execute($query, $params, $types);
        $affected = $this->db->getAffectedRows();
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Delete notice
     */
    public function delete($id)
    {
        $query    = "DELETE FROM notices WHERE id = ?";
        $stmt     = $this->db->execute($query, [$id], 'i');
        $affected = $this->db->getAffectedRows();
        $stmt->close();
        return $affected > 0;
    }

    /**
     * Get notice read statistics
     */
    public function getNoticeReadStats($notice_id)
    {
        $query = "SELECT
                    (SELECT COUNT(*) FROM notice_reads WHERE notice_id = ?) as read_count,
                    (SELECT COUNT(DISTINCT u.id)
                     FROM users u, notices n
                     WHERE n.id = ?
                       AND (n.target_audience = 'all'
                            OR (n.target_audience = 'residents' AND u.role = 'resident')
                            OR (n.target_audience = 'admins' AND u.role = 'admin')
                            OR (n.target_audience = 'specific_location'
                                AND (n.cell = u.cell OR n.cell IS NULL)
                                AND (n.sector = u.sector OR n.sector IS NULL)
                                AND (n.district = u.district OR n.district IS NULL)))) as target_count";

        return $this->db->fetchOne($query, [$notice_id, $notice_id], 'ii');
    }
}
