<?php
/**
 * Fine Model
 * Handles fines-related database operations
 */

require_once __DIR__ . '/../../config/db.php';

class Fine
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Create a new fine
     */
    public function create($data)
    {
        $query = "INSERT INTO fines (user_id, event_id, attendance_id, amount, reason, reason_description, status, due_date, created_by)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->execute($query, [
            $data['user_id'],
            $data['event_id'],
            $data['attendance_id'] ?? null,
            $data['amount'],
            $data['reason'],
            $data['reason_description'] ?? null,
            $data['status'] ?? 'pending',
            $data['due_date'] ?? null,
            $data['created_by'],
        ], 'iiidssssi');

        $stmt->close();
        return $this->db->getLastInsertId();
    }

    /**
     * Find fine by ID
     */
    public function findById($id)
    {
        $query = "SELECT f.*, u.first_name, u.last_name, e.title as event_title, e.event_date
                  FROM fines f
                  LEFT JOIN users u ON f.user_id = u.id
                  LEFT JOIN umuganda_events e ON f.event_id = e.id
                  WHERE f.id = ?";
        return $this->db->fetchOne($query, [$id], 'i');
    }

    /**
     * Get user's fines
     */
    public function getUserFines($user_id, $filters = [])
    {
        $where_conditions = ['f.user_id = ?'];
        $params           = [$user_id];
        $types            = 'i';

        if (! empty($filters['status'])) {
            $where_conditions[] = "f.status = ?";
            $params[]           = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['date_from'])) {
            $where_conditions[] = "f.created_at >= ?";
            $params[]           = $filters['date_from'];
            $types .= 's';
        }

        if (! empty($filters['date_to'])) {
            $where_conditions[] = "f.created_at <= ?";
            $params[]           = $filters['date_to'];
            $types .= 's';
        }

        $where_clause = implode(' AND ', $where_conditions);

        $query = "SELECT f.*, e.title as event_title, e.event_date
                  FROM fines f
                  LEFT JOIN umuganda_events e ON f.event_id = e.id
                  WHERE {$where_clause}
                  ORDER BY f.created_at DESC";

        if (! empty($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= 'i';
        }

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Get user's outstanding fines
     */
    public function getUserOutstandingFines($user_id)
    {
        $query = "SELECT f.*, e.title as event_title, e.event_date
                  FROM fines f
                  LEFT JOIN umuganda_events e ON f.event_id = e.id
                  WHERE f.user_id = ? AND f.status = 'pending'
                  ORDER BY f.due_date ASC";

        return $this->db->fetchAll($query, [$user_id], 'i');
    }

    /**
     * Get user's total outstanding amount
     */
    public function getUserOutstandingAmount($user_id)
    {
        $query = "SELECT COALESCE(SUM(amount), 0) as total_outstanding
                  FROM fines
                  WHERE user_id = ? AND status = 'pending'";

        $result = $this->db->fetchOne($query, [$user_id], 'i');
        return $result ? $result['total_outstanding'] : 0;
    }

    /**
     * Get user's fine statistics
     */
    public function getUserFineStats($user_id)
    {
        $query = "SELECT
                    COUNT(*) as total_fines,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_fines,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_fines,
                    SUM(CASE WHEN status = 'waived' THEN 1 ELSE 0 END) as waived_fines,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_outstanding,
                    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_paid
                  FROM fines
                  WHERE user_id = ?";

        return $this->db->fetchOne($query, [$user_id], 'i');
    }

    /**
     * Update fine status
     */
    public function updateStatus($id, $status, $additional_data = [])
    {
        $set_clauses = ['status = ?'];
        $params      = [$status];
        $types       = 's';

        // Handle payment data
        if ($status === 'paid' && ! empty($additional_data['payment_method'])) {
            $set_clauses[] = 'paid_date = NOW()';
            $set_clauses[] = 'payment_method = ?';
            $set_clauses[] = 'payment_reference = ?';
            $params[]      = $additional_data['payment_method'];
            $params[]      = $additional_data['payment_reference'] ?? null;
            $types .= 'ss';
        }

        // Handle waiver data
        if ($status === 'waived') {
            $set_clauses[] = 'waived_date = NOW()';
            if (! empty($additional_data['waived_by'])) {
                $set_clauses[] = 'waived_by = ?';
                $params[]      = $additional_data['waived_by'];
                $types .= 'i';
            }
            if (! empty($additional_data['waived_reason'])) {
                $set_clauses[] = 'waived_reason = ?';
                $params[]      = $additional_data['waived_reason'];
                $types .= 's';
            }
        }

        $set_clause = implode(', ', $set_clauses);
        $query      = "UPDATE fines SET {$set_clause}, updated_at = NOW() WHERE id = ?";

        $params[] = $id;
        $types .= 'i';

        $stmt     = $this->db->execute($query, $params, $types);
        $affected = $this->db->getAffectedRows();
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Get all fines with filters
     */
    public function getFines($filters = [])
    {
        $where_conditions = [];
        $params           = [];
        $types            = '';

        if (! empty($filters['user_id'])) {
            $where_conditions[] = "f.user_id = ?";
            $params[]           = $filters['user_id'];
            $types .= 'i';
        }

        if (! empty($filters['status'])) {
            $where_conditions[] = "f.status = ?";
            $params[]           = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['event_id'])) {
            $where_conditions[] = "f.event_id = ?";
            $params[]           = $filters['event_id'];
            $types .= 'i';
        }

        $where_clause = ! empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $query = "SELECT f.*, u.first_name, u.last_name, u.national_id, e.title as event_title, e.event_date
                  FROM fines f
                  LEFT JOIN users u ON f.user_id = u.id
                  LEFT JOIN umuganda_events e ON f.event_id = e.id
                  {$where_clause}
                  ORDER BY f.created_at DESC";

        if (! empty($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= 'i';
        }

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Delete fine
     */
    public function delete($id)
    {
        $query    = "DELETE FROM fines WHERE id = ?";
        $stmt     = $this->db->execute($query, [$id], 'i');
        $affected = $this->db->getAffectedRows();
        $stmt->close();
        return $affected > 0;
    }
}
