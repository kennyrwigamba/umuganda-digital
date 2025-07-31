<?php
/**
 * Location Manager Class
 * Handles Rwanda's administrative hierarchy (Province > District > Sector > Cell)
 */

class LocationManager
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        $stmt = $this->db->prepare("SELECT id, name, code FROM provinces ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get districts by province
     */
    public function getDistrictsByProvince($provinceId)
    {
        $stmt = $this->db->prepare("
            SELECT id, name, code
            FROM districts
            WHERE province_id = ?
            ORDER BY name
        ");
        $stmt->execute([$provinceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get sectors by district
     */
    public function getSectorsByDistrict($districtId)
    {
        $stmt = $this->db->prepare("
            SELECT id, name, code
            FROM sectors
            WHERE district_id = ?
            ORDER BY name
        ");
        $stmt->execute([$districtId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get cells by sector
     */
    public function getCellsBySector($sectorId)
    {
        $stmt = $this->db->prepare("
            SELECT id, name, code
            FROM cells
            WHERE sector_id = ?
            ORDER BY name
        ");
        $stmt->execute([$sectorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get full location hierarchy for a cell
     */
    public function getLocationHierarchy($cellId)
    {
        $stmt = $this->db->prepare("
            SELECT
                p.id as province_id, p.name as province_name, p.code as province_code,
                d.id as district_id, d.name as district_name, d.code as district_code,
                s.id as sector_id, s.name as sector_name, s.code as sector_code,
                c.id as cell_id, c.name as cell_name, c.code as cell_code
            FROM cells c
            JOIN sectors s ON c.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            WHERE c.id = ?
        ");
        $stmt->execute([$cellId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get location path string (Province > District > Sector > Cell)
     */
    public function getLocationPath($cellId)
    {
        $hierarchy = $this->getLocationHierarchy($cellId);
        if ($hierarchy) {
            return $hierarchy['province_name'] . ' > ' .
                $hierarchy['district_name'] . ' > ' .
                $hierarchy['sector_name'] . ' > ' .
                $hierarchy['cell_name'];
        }
        return 'Unknown Location';
    }

    /**
     * Validate location hierarchy (ensure cell belongs to specified sector, district, province)
     */
    public function validateLocationHierarchy($cellId, $sectorId, $districtId, $provinceId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM cells c
            JOIN sectors s ON c.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            WHERE c.id = ? AND s.id = ? AND d.id = ? AND p.id = ?
        ");
        $stmt->execute([$cellId, $sectorId, $districtId, $provinceId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Search locations by name
     */
    public function searchLocations($searchTerm, $type = null)
    {
        $searchTerm = '%' . $searchTerm . '%';

        switch ($type) {
            case 'province':
                $stmt = $this->db->prepare("
                    SELECT id, name, code, 'province' as type
                    FROM provinces
                    WHERE name LIKE ? OR code LIKE ?
                    ORDER BY name
                ");
                $stmt->execute([$searchTerm, $searchTerm]);
                break;

            case 'district':
                $stmt = $this->db->prepare("
                    SELECT d.id, d.name, d.code, 'district' as type, p.name as province_name
                    FROM districts d
                    JOIN provinces p ON d.province_id = p.id
                    WHERE d.name LIKE ? OR d.code LIKE ?
                    ORDER BY d.name
                ");
                $stmt->execute([$searchTerm, $searchTerm]);
                break;

            case 'sector':
                $stmt = $this->db->prepare("
                    SELECT s.id, s.name, s.code, 'sector' as type,
                           d.name as district_name, p.name as province_name
                    FROM sectors s
                    JOIN districts d ON s.district_id = d.id
                    JOIN provinces p ON d.province_id = p.id
                    WHERE s.name LIKE ? OR s.code LIKE ?
                    ORDER BY s.name
                ");
                $stmt->execute([$searchTerm, $searchTerm]);
                break;

            case 'cell':
                $stmt = $this->db->prepare("
                    SELECT c.id, c.name, c.code, 'cell' as type,
                           s.name as sector_name, d.name as district_name, p.name as province_name
                    FROM cells c
                    JOIN sectors s ON c.sector_id = s.id
                    JOIN districts d ON s.district_id = d.id
                    JOIN provinces p ON d.province_id = p.id
                    WHERE c.name LIKE ? OR c.code LIKE ?
                    ORDER BY c.name
                ");
                $stmt->execute([$searchTerm, $searchTerm]);
                break;

            default:
                // Search all types
                $results = [];
                foreach (['province', 'district', 'sector', 'cell'] as $locationType) {
                    $results = array_merge($results, $this->searchLocations($searchTerm, $locationType));
                }
                return $results;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get resident count by location
     */
    public function getResidentCountByLocation($locationId, $locationType)
    {
        switch ($locationType) {
            case 'province':
                $stmt = $this->db->prepare("
                    SELECT COUNT(u.id) as count
                    FROM users u
                    WHERE u.province_id = ? AND u.role = 'resident' AND u.status = 'active'
                ");
                break;

            case 'district':
                $stmt = $this->db->prepare("
                    SELECT COUNT(u.id) as count
                    FROM users u
                    WHERE u.district_id = ? AND u.role = 'resident' AND u.status = 'active'
                ");
                break;

            case 'sector':
                $stmt = $this->db->prepare("
                    SELECT COUNT(u.id) as count
                    FROM users u
                    WHERE u.sector_id = ? AND u.role = 'resident' AND u.status = 'active'
                ");
                break;

            case 'cell':
                $stmt = $this->db->prepare("
                    SELECT COUNT(u.id) as count
                    FROM users u
                    WHERE u.cell_id = ? AND u.role = 'resident' AND u.status = 'active'
                ");
                break;

            default:
                return 0;
        }

        $stmt->execute([$locationId]);
        return $stmt->fetchColumn();
    }

    /**
     * Get complete location hierarchy as nested array (for dropdowns)
     */
    public function getCompleteHierarchy()
    {
        $stmt = $this->db->prepare("
            SELECT
                p.id as province_id, p.name as province_name, p.code as province_code,
                d.id as district_id, d.name as district_name, d.code as district_code,
                s.id as sector_id, s.name as sector_name, s.code as sector_code,
                c.id as cell_id, c.name as cell_name, c.code as cell_code
            FROM provinces p
            LEFT JOIN districts d ON p.id = d.province_id
            LEFT JOIN sectors s ON d.id = s.district_id
            LEFT JOIN cells c ON s.id = c.sector_id
            ORDER BY p.name, d.name, s.name, c.name
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize into nested structure
        $hierarchy = [];
        foreach ($data as $row) {
            if (! isset($hierarchy[$row['province_id']])) {
                $hierarchy[$row['province_id']] = [
                    'id'        => $row['province_id'],
                    'name'      => $row['province_name'],
                    'code'      => $row['province_code'],
                    'districts' => [],
                ];
            }

            if ($row['district_id'] && ! isset($hierarchy[$row['province_id']]['districts'][$row['district_id']])) {
                $hierarchy[$row['province_id']]['districts'][$row['district_id']] = [
                    'id'      => $row['district_id'],
                    'name'    => $row['district_name'],
                    'code'    => $row['district_code'],
                    'sectors' => [],
                ];
            }

            if ($row['sector_id'] && ! isset($hierarchy[$row['province_id']]['districts'][$row['district_id']]['sectors'][$row['sector_id']])) {
                $hierarchy[$row['province_id']]['districts'][$row['district_id']]['sectors'][$row['sector_id']] = [
                    'id'    => $row['sector_id'],
                    'name'  => $row['sector_name'],
                    'code'  => $row['sector_code'],
                    'cells' => [],
                ];
            }

            if ($row['cell_id']) {
                $hierarchy[$row['province_id']]['districts'][$row['district_id']]['sectors'][$row['sector_id']]['cells'][$row['cell_id']] = [
                    'id'   => $row['cell_id'],
                    'name' => $row['cell_name'],
                    'code' => $row['cell_code'],
                ];
            }
        }

        return array_values($hierarchy);
    }

    /**
     * Update user location with validation
     */
    public function updateUserLocation($userId, $cellId)
    {
        // Get location hierarchy for the cell
        $hierarchy = $this->getLocationHierarchy($cellId);
        if (! $hierarchy) {
            throw new Exception('Invalid cell ID');
        }

        $stmt = $this->db->prepare("
            UPDATE users
            SET cell_id = ?, sector_id = ?, district_id = ?, province_id = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $cellId,
            $hierarchy['sector_id'],
            $hierarchy['district_id'],
            $hierarchy['province_id'],
            $userId,
        ]);
    }
}
