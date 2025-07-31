<?php
/**
 * UmugandaEvent Model
 * Handles umuganda events-related database operations
 */

require_once __DIR__ . '/../../config/db.php';

class UmugandaEvent
{
    private $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Create a new event
     */
    public function create($data)
    {
        $query = "INSERT INTO umuganda_events (title, description, event_date, start_time, end_time, location,
                         cell_id, sector_id, district_id, province_id, cell, sector, district, province,
                         max_participants, status, created_by)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->execute($query, [
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['start_time'],
            $data['end_time'],
            $data['location'],
            $data['cell_id'] ?? null,
            $data['sector_id'] ?? null,
            $data['district_id'] ?? null,
            $data['province_id'] ?? null,
            $data['cell'] ?? null,
            $data['sector'] ?? null,
            $data['district'] ?? null,
            $data['province'] ?? null,
            $data['max_participants'] ?? null,
            $data['status'] ?? 'scheduled',
            $data['created_by'],
        ], 'ssssssiiisssssiss');

        $stmt->close();
        return $this->db->getLastInsertId();
    }

    /**
     * Find event by ID
     */
    public function findById($id)
    {
        $query = "SELECT * FROM umuganda_events WHERE id = ?";
        return $this->db->fetchOne($query, [$id], 'i');
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents($limit = 10)
    {
        $query = "SELECT * FROM umuganda_events
                  WHERE event_date >= CURDATE() AND status IN ('scheduled', 'ongoing')
                  ORDER BY event_date ASC, start_time ASC
                  LIMIT ?";
        return $this->db->fetchAll($query, [$limit], 'i');
    }

    /**
     * Get next upcoming event
     */
    public function getNextEvent()
    {
        $query = "SELECT * FROM umuganda_events
                  WHERE event_date >= CURDATE() AND status IN ('scheduled', 'ongoing')
                  ORDER BY event_date ASC, start_time ASC
                  LIMIT 1";
        return $this->db->fetchOne($query);
    }

    /**
     * Get events by filters
     */
    public function getEvents($filters = [])
    {
        $where_conditions = [];
        $params           = [];
        $types            = '';

        if (! empty($filters['status'])) {
            $where_conditions[] = "status = ?";
            $params[]           = $filters['status'];
            $types .= 's';
        }

        if (! empty($filters['date_from'])) {
            $where_conditions[] = "event_date >= ?";
            $params[]           = $filters['date_from'];
            $types .= 's';
        }

        if (! empty($filters['date_to'])) {
            $where_conditions[] = "event_date <= ?";
            $params[]           = $filters['date_to'];
            $types .= 's';
        }

        if (! empty($filters['cell'])) {
            $where_conditions[] = "cell = ?";
            $params[]           = $filters['cell'];
            $types .= 's';
        }

        if (! empty($filters['sector'])) {
            $where_conditions[] = "sector = ?";
            $params[]           = $filters['sector'];
            $types .= 's';
        }

        if (! empty($filters['district'])) {
            $where_conditions[] = "district = ?";
            $params[]           = $filters['district'];
            $types .= 's';
        }

        $where_clause = ! empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $query = "SELECT * FROM umuganda_events
                  {$where_clause}
                  ORDER BY event_date DESC, start_time DESC";

        if (! empty($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= 'i';
        }

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Update event
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
        $query      = "UPDATE umuganda_events SET $set_clause, updated_at = NOW() WHERE id = ?";

        $params[] = $id;
        $types .= 'i';

        $stmt     = $this->db->execute($query, $params, $types);
        $affected = $this->db->getAffectedRows();
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Delete event
     */
    public function delete($id)
    {
        $query    = "DELETE FROM umuganda_events WHERE id = ?";
        $stmt     = $this->db->execute($query, [$id], 'i');
        $affected = $this->db->getAffectedRows();
        $stmt->close();
        return $affected > 0;
    }

    /**
     * Get events for a specific user's location using ID-based hierarchy
     */
    public function getEventsByUserLocationIds($user_cell_id, $user_sector_id, $user_district_id, $limit = 10)
    {
        $query = "SELECT e.*,
                         c.name as cell_name, s.name as sector_name,
                         d.name as district_name, p.name as province_name
                  FROM umuganda_events e
                  LEFT JOIN cells c ON e.cell_id = c.id
                  LEFT JOIN sectors s ON e.sector_id = s.id
                  LEFT JOIN districts d ON e.district_id = d.id
                  LEFT JOIN provinces p ON e.province_id = p.id
                  WHERE (e.cell_id = ? OR e.cell_id IS NULL)
                    AND (e.sector_id = ? OR e.sector_id IS NULL)
                    AND (e.district_id = ? OR e.district_id IS NULL)
                    AND e.event_date >= CURDATE()
                    AND e.status IN ('scheduled', 'ongoing')
                  ORDER BY e.event_date ASC, e.start_time ASC
                  LIMIT ?";

        return $this->db->fetchAll($query, [$user_cell_id, $user_sector_id, $user_district_id, $limit], 'iiii');
    }

    /**
     * Get events for a specific user's location (legacy method - kept for backward compatibility)
     */
    public function getEventsByUserLocation($user_cell, $user_sector, $user_district, $limit = 10)
    {
        $query = "SELECT * FROM umuganda_events
                  WHERE (cell = ? OR cell IS NULL)
                    AND (sector = ? OR sector IS NULL)
                    AND (district = ? OR district IS NULL)
                    AND event_date >= CURDATE()
                    AND status IN ('scheduled', 'ongoing')
                  ORDER BY event_date ASC, start_time ASC
                  LIMIT ?";

        return $this->db->fetchAll($query, [$user_cell, $user_sector, $user_district, $limit], 'sssi');
    }
}
