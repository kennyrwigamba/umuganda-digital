<?php
/**
 * Attendance Model
 * Handles attendance-related database operations
 */

require_once __DIR__ . '/../../config/db.php';

class Attendance
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Record attendance for a user
     */
    public function recordAttendance($data)
    {
        $query = "INSERT INTO attendance (user_id, event_id, check_in_time, check_out_time, status, excuse_reason, excuse_document, notes, recorded_by)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE
                  check_in_time = VALUES(check_in_time),
                  check_out_time = VALUES(check_out_time),
                  status = VALUES(status),
                  excuse_reason = VALUES(excuse_reason),
                  excuse_document = VALUES(excuse_document),
                  notes = VALUES(notes),
                  recorded_by = VALUES(recorded_by),
                  updated_at = NOW()";

        $stmt = $this->db->execute($query, [
            $data['user_id'],
            $data['event_id'],
            $data['check_in_time'] ?? null,
            $data['check_out_time'] ?? null,
            $data['status'],
            $data['excuse_reason'] ?? null,
            $data['excuse_document'] ?? null,
            $data['notes'] ?? null,
            $data['recorded_by'] ?? null,
        ], 'iissssssi');

        $stmt->close();
        return $this->db->getLastInsertId() ?: true;
    }

    /**
     * Mark user as present (check-in)
     */
    public function checkIn($user_id, $event_id, $recorded_by = null)
    {
        $check_in_time = date('Y-m-d H:i:s');

        return $this->recordAttendance([
            'user_id'       => $user_id,
            'event_id'      => $event_id,
            'check_in_time' => $check_in_time,
            'status'        => 'present',
            'recorded_by'   => $recorded_by,
        ]);
    }

    /**
     * Mark user check-out
     */
    public function checkOut($user_id, $event_id, $recorded_by = null)
    {
        $query = "UPDATE attendance
                  SET check_out_time = NOW(), updated_at = NOW(), recorded_by = ?
                  WHERE user_id = ? AND event_id = ?";

        $stmt     = $this->db->execute($query, [$recorded_by, $user_id, $event_id], 'iii');
        $affected = $this->db->getAffectedRows();
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Mark user as absent
     */
    public function markAbsent($user_id, $event_id, $recorded_by = null)
    {
        return $this->recordAttendance([
            'user_id'     => $user_id,
            'event_id'    => $event_id,
            'status'      => 'absent',
            'recorded_by' => $recorded_by,
        ]);
    }

    /**
     * Mark user as late
     */
    public function markLate($user_id, $event_id, $check_in_time = null, $recorded_by = null)
    {
        return $this->recordAttendance([
            'user_id'       => $user_id,
            'event_id'      => $event_id,
            'check_in_time' => $check_in_time ?: date('Y-m-d H:i:s'),
            'status'        => 'late',
            'recorded_by'   => $recorded_by,
        ]);
    }

    /**
     * Submit excuse for absence
     */
    public function submitExcuse($user_id, $event_id, $reason, $document = null)
    {
        return $this->recordAttendance([
            'user_id'         => $user_id,
            'event_id'        => $event_id,
            'status'          => 'excused',
            'excuse_reason'   => $reason,
            'excuse_document' => $document,
            'recorded_by'     => $user_id,
        ]);
    }

    /**
     * Get attendance record for user and event
     */
    public function getAttendance($user_id, $event_id)
    {
        $query = "SELECT a.*, u.first_name, u.last_name, u.email, e.title as event_title, e.event_date
                  FROM attendance a
                  JOIN users u ON a.user_id = u.id
                  JOIN umuganda_events e ON a.event_id = e.id
                  WHERE a.user_id = ? AND a.event_id = ?";

        return $this->db->fetchOne($query, [$user_id, $event_id], 'ii');
    }

    /**
     * Get attendance records with filters
     */
    public function getAttendanceRecords($filters = [])
    {
        $where_clauses = [];
        $params        = [];
        $types         = '';

        $query = "SELECT a.*,
                         u.first_name, u.last_name, u.email, u.national_id, u.phone, u.cell,
                         e.title as event_title, e.event_date, e.start_time, e.end_time,
                         recorder.first_name as recorded_by_name
                  FROM attendance a
                  JOIN users u ON a.user_id = u.id
                  JOIN umuganda_events e ON a.event_id = e.id
                  LEFT JOIN users recorder ON a.recorded_by = recorder.id";

        // Apply filters
        if (! empty($filters['user_id'])) {
            $where_clauses[] = "a.user_id = ?";
            $params[]        = $filters['user_id'];
            $types .= 'i';
        }

        if (! empty($filters['event_id'])) {
            $where_clauses[] = "a.event_id = ?";
            $params[]        = $filters['event_id'];
            $types .= 'i';
        }

        if (! empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $placeholders    = str_repeat('?,', count($filters['status']) - 1) . '?';
                $where_clauses[] = "a.status IN ($placeholders)";
                $params          = array_merge($params, $filters['status']);
                $types .= str_repeat('s', count($filters['status']));
            } else {
                $where_clauses[] = "a.status = ?";
                $params[]        = $filters['status'];
                $types .= 's';
            }
        }

        if (! empty($filters['event_date_from'])) {
            $where_clauses[] = "e.event_date >= ?";
            $params[]        = $filters['event_date_from'];
            $types .= 's';
        }

        if (! empty($filters['event_date_to'])) {
            $where_clauses[] = "e.event_date <= ?";
            $params[]        = $filters['event_date_to'];
            $types .= 's';
        }

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
        $order_by  = $filters['order_by'] ?? 'e.event_date';
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
     * Get attendance statistics
     */
    public function getAttendanceStats($filters = [])
    {
        $where_clauses = [];
        $params        = [];
        $types         = '';

        $query = "SELECT
                    COUNT(*) as total_records,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                    SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count,
                    ROUND((SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate
                  FROM attendance a
                  JOIN users u ON a.user_id = u.id
                  JOIN umuganda_events e ON a.event_id = e.id";

        // Apply same filters as getAttendanceRecords
        if (! empty($filters['user_id'])) {
            $where_clauses[] = "a.user_id = ?";
            $params[]        = $filters['user_id'];
            $types .= 'i';
        }

        if (! empty($filters['event_date_from'])) {
            $where_clauses[] = "e.event_date >= ?";
            $params[]        = $filters['event_date_from'];
            $types .= 's';
        }

        if (! empty($filters['event_date_to'])) {
            $where_clauses[] = "e.event_date <= ?";
            $params[]        = $filters['event_date_to'];
            $types .= 's';
        }

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

        if (! empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        return $this->db->fetchOne($query, $params, $types);
    }

    /**
     * Get user attendance history
     */
    public function getUserAttendanceHistory($user_id, $filters = [])
    {
        $where_clauses = ["a.user_id = ?"];
        $params        = [$user_id];
        $types         = 'i';

        // Add filters
        if (! empty($filters['event_date_from'])) {
            $where_clauses[] = "e.event_date >= ?";
            $params[]        = $filters['event_date_from'];
            $types .= 's';
        }

        if (! empty($filters['event_date_to'])) {
            $where_clauses[] = "e.event_date <= ?";
            $params[]        = $filters['event_date_to'];
            $types .= 's';
        }

        if (! empty($filters['year'])) {
            $where_clauses[] = "YEAR(e.event_date) = ?";
            $params[]        = $filters['year'];
            $types .= 'i';
        }

        if (! empty($filters['month'])) {
            $where_clauses[] = "MONTH(e.event_date) = ?";
            $params[]        = $filters['month'];
            $types .= 'i';
        }

        if (! empty($filters['status'])) {
            $where_clauses[] = "a.status = ?";
            $params[]        = $filters['status'];
            $types .= 's';
        }

        $query = "SELECT a.*, e.title as event_title, e.event_date, e.start_time, e.end_time, e.location
                  FROM attendance a
                  JOIN umuganda_events e ON a.event_id = e.id
                  WHERE " . implode(' AND ', $where_clauses) . "
                  ORDER BY e.event_date DESC";

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
     * Get monthly attendance summary for user
     */
    public function getMonthlyAttendanceSummary($user_id, $year = null, $month = null)
    {
        $year  = $year ?: date('Y');
        $month = $month ?: date('m');

        $query = "SELECT
                    COUNT(*) as total_events,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                    SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count
                  FROM attendance a
                  JOIN umuganda_events e ON a.event_id = e.id
                  WHERE a.user_id = ? AND YEAR(e.event_date) = ? AND MONTH(e.event_date) = ?";

        return $this->db->fetchOne($query, [$user_id, $year, $month], 'iii');
    }

    /**
     * Delete attendance record
     */
    public function deleteAttendance($user_id, $event_id)
    {
        $query    = "DELETE FROM attendance WHERE user_id = ? AND event_id = ?";
        $stmt     = $this->db->execute($query, [$user_id, $event_id], 'ii');
        $affected = $this->db->getAffectedRows();
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Get attendance trends (monthly data for charts)
     */
    public function getAttendanceTrends($filters = [])
    {
        $where_clauses = [];
        $params        = [];
        $types         = '';

        $query = "SELECT
                    DATE_FORMAT(e.event_date, '%Y-%m') as month,
                    COUNT(*) as total_records,
                    SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) as attended,
                    ROUND((SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate
                  FROM attendance a
                  JOIN umuganda_events e ON a.event_id = e.id
                  JOIN users u ON a.user_id = u.id";

        // Apply filters
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

        // Default to last 12 months
        $date_from       = $filters['date_from'] ?? date('Y-m-d', strtotime('-12 months'));
        $where_clauses[] = "e.event_date >= ?";
        $params[]        = $date_from;
        $types .= 's';

        if (! empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $query .= " GROUP BY DATE_FORMAT(e.event_date, '%Y-%m') ORDER BY month ASC";

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Get count of attendance records for pagination
     */
    public function getUserAttendanceCount($user_id, $filters = [])
    {
        $where_clauses = ["a.user_id = ?"];
        $params        = [$user_id];
        $types         = 'i';

        // Add filters
        if (! empty($filters['event_date_from'])) {
            $where_clauses[] = "e.event_date >= ?";
            $params[]        = $filters['event_date_from'];
            $types .= 's';
        }

        if (! empty($filters['event_date_to'])) {
            $where_clauses[] = "e.event_date <= ?";
            $params[]        = $filters['event_date_to'];
            $types .= 's';
        }

        if (! empty($filters['status'])) {
            $where_clauses[] = "a.status = ?";
            $params[]        = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['year'])) {
            $where_clauses[] = "YEAR(e.event_date) = ?";
            $params[]        = $filters['year'];
            $types .= 'i';
        }

        if (! empty($filters['month'])) {
            $where_clauses[] = "MONTH(e.event_date) = ?";
            $params[]        = $filters['month'];
            $types .= 'i';
        }

        $query = "SELECT COUNT(*) as count
                  FROM attendance a
                  JOIN umuganda_events e ON a.event_id = e.id
                  WHERE " . implode(' AND ', $where_clauses);

        $result = $this->db->fetchOne($query, $params, $types);
        return $result['count'] ?? 0;
    }
}
