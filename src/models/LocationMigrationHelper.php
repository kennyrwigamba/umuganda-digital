<?php
/**
 * Location Migration Helper
 * Helps transition from string-based locations to ID-based location hierarchy
 */

class LocationMigrationHelper
{
    private $db;
    private $locationManager;

    public function __construct($database, $locationManager = null)
    {
        $this->db              = $database;
        $this->locationManager = $locationManager ?: new LocationManager($database);
    }

    /**
     * Migrate a single user from string-based to ID-based location
     * Handles the case where users have incorrect hierarchy:
     * - User province "Kigali" -> DB province "Kigali City"
     * - User district "Kigali" -> ignore
     * - User sector (Gasabo/Kicukiro/Nyarugenge) -> DB district
     * - User cell (Kimihurura/Remera etc.) -> DB sector
     */
    public function migrateUserLocation($userId, $cellName, $sectorName, $districtName, $provinceName)
    {
        try {
            // Handle the data structure mismatch for Kigali users
            if ($provinceName === 'Kigali' && $districtName === 'Kigali') {
                // For Kigali users: sector field contains district, cell field contains sector
                $actualProvince = 'Kigali City';
                $actualDistrict = $sectorName; // User's "sector" is actually district
                $actualSector   = $cellName;   // User's "cell" is actually sector

                // Find a default cell in this sector
                $stmt = $this->db->prepare("
                    SELECT c.id as cell_id, s.id as sector_id, d.id as district_id, p.id as province_id,
                           c.name as cell_name
                    FROM cells c
                    JOIN sectors s ON c.sector_id = s.id
                    JOIN districts d ON s.district_id = d.id
                    JOIN provinces p ON d.province_id = p.id
                    WHERE s.name = ? AND d.name = ? AND p.name = ?
                    LIMIT 1
                ");

                $stmt->execute([$actualSector, $actualDistrict, $actualProvince]);
                $location = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($location) {
                    // Update user with the new location IDs
                    $updateStmt = $this->db->prepare("
                        UPDATE users
                        SET cell_id = ?, sector_id = ?, district_id = ?, province_id = ?
                        WHERE id = ?
                    ");

                    $updateStmt->execute([
                        $location['cell_id'],
                        $location['sector_id'],
                        $location['district_id'],
                        $location['province_id'],
                        $userId,
                    ]);

                    error_log("Migrated user $userId: $provinceName/$districtName/$sectorName/$cellName -> {$actualProvince}/{$actualDistrict}/{$actualSector}/{$location['cell_name']}");
                    return true;
                } else {
                    // Sector doesn't exist, try to find any cell in this district as fallback
                    $fallbackStmt = $this->db->prepare("
                        SELECT c.id as cell_id, s.id as sector_id, d.id as district_id, p.id as province_id,
                               c.name as cell_name, s.name as sector_name
                        FROM cells c
                        JOIN sectors s ON c.sector_id = s.id
                        JOIN districts d ON s.district_id = d.id
                        JOIN provinces p ON d.province_id = p.id
                        WHERE d.name = ? AND p.name = ?
                        ORDER BY s.name, c.name
                        LIMIT 1
                    ");

                    $fallbackStmt->execute([$actualDistrict, $actualProvince]);
                    $fallback = $fallbackStmt->fetch(PDO::FETCH_ASSOC);

                    if ($fallback) {
                        // Update user with fallback location
                        $updateStmt = $this->db->prepare("
                            UPDATE users
                            SET cell_id = ?, sector_id = ?, district_id = ?, province_id = ?
                            WHERE id = ?
                        ");

                        $updateStmt->execute([
                            $fallback['cell_id'],
                            $fallback['sector_id'],
                            $fallback['district_id'],
                            $fallback['province_id'],
                            $userId,
                        ]);

                        error_log("Migrated user $userId (fallback): $provinceName/$districtName/$sectorName/$cellName -> {$actualProvince}/{$actualDistrict}/{$fallback['sector_name']}/{$fallback['cell_name']} [sector '$actualSector' not found]");
                        return true;
                    } else {
                        error_log("Location not found for user $userId after mapping: $actualProvince/$actualDistrict/$actualSector");
                        return false;
                    }
                }
            } else {
                // Try original logic for other provinces
                return $this->migrateUserLocationOriginal($userId, $cellName, $sectorName, $districtName, $provinceName);
            }

        } catch (Exception $e) {
            error_log("Error migrating user $userId location: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Original migration logic for exact matches
     */
    private function migrateUserLocationOriginal($userId, $cellName, $sectorName, $districtName, $provinceName)
    {
        try {
            // Find the cell ID based on the hierarchy
            $stmt = $this->db->prepare("
                SELECT c.id as cell_id, s.id as sector_id, d.id as district_id, p.id as province_id
                FROM cells c
                JOIN sectors s ON c.sector_id = s.id
                JOIN districts d ON s.district_id = d.id
                JOIN provinces p ON d.province_id = p.id
                WHERE c.name = ? AND s.name = ? AND d.name = ? AND p.name = ?
            ");

            $stmt->execute([$cellName, $sectorName, $districtName, $provinceName]);
            $location = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($location) {
                // Update user with the new location IDs
                $updateStmt = $this->db->prepare("
                    UPDATE users
                    SET cell_id = ?, sector_id = ?, district_id = ?, province_id = ?
                    WHERE id = ?
                ");

                $updateStmt->execute([
                    $location['cell_id'],
                    $location['sector_id'],
                    $location['district_id'],
                    $location['province_id'],
                    $userId,
                ]);

                return true;
            } else {
                // Log unmatched location for manual review
                error_log("Location not found for user $userId: $cellName, $sectorName, $districtName, $provinceName");
                return false;
            }

        } catch (Exception $e) {
            error_log("Error migrating user $userId location: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Migrate all users with string-based locations
     */
    public function migrateAllUsers()
    {
        $migratedCount = 0;
        $errorCount    = 0;

        try {
            // Get all users with string-based locations that don't have ID-based locations
            $stmt = $this->db->prepare("
                SELECT id, cell, sector, district, province
                FROM users
                WHERE cell_id IS NULL
                AND cell != 'Not Set'
                AND cell IS NOT NULL
            ");

            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user) {
                if ($this->migrateUserLocation(
                    $user['id'],
                    $user['cell'],
                    $user['sector'],
                    $user['district'],
                    $user['province']
                )) {
                    $migratedCount++;
                } else {
                    $errorCount++;
                }
            }

            return [
                'total'    => count($users),
                'migrated' => $migratedCount,
                'errors'   => $errorCount,
            ];

        } catch (Exception $e) {
            error_log("Error in bulk migration: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find closest matching location for a user's string-based location
     */
    public function findClosestLocation($cellName, $sectorName, $districtName, $provinceName)
    {
        $matches = [];

        // Try exact match first
        $stmt = $this->db->prepare("
            SELECT c.id as cell_id, c.name as cell_name,
                   s.id as sector_id, s.name as sector_name,
                   d.id as district_id, d.name as district_name,
                   p.id as province_id, p.name as province_name,
                   'exact' as match_type
            FROM cells c
            JOIN sectors s ON c.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            WHERE c.name = ? AND s.name = ? AND d.name = ? AND p.name = ?
        ");

        $stmt->execute([$cellName, $sectorName, $districtName, $provinceName]);
        $exactMatch = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exactMatch) {
            return $exactMatch;
        }

        // Try matching by sector, district, province (ignore cell)
        $stmt = $this->db->prepare("
            SELECT c.id as cell_id, c.name as cell_name,
                   s.id as sector_id, s.name as sector_name,
                   d.id as district_id, d.name as district_name,
                   p.id as province_id, p.name as province_name,
                   'sector_match' as match_type
            FROM cells c
            JOIN sectors s ON c.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            WHERE s.name = ? AND d.name = ? AND p.name = ?
            LIMIT 1
        ");

        $stmt->execute([$sectorName, $districtName, $provinceName]);
        $sectorMatch = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sectorMatch) {
            return $sectorMatch;
        }

        // Try fuzzy matching on names
        $stmt = $this->db->prepare("
            SELECT c.id as cell_id, c.name as cell_name,
                   s.id as sector_id, s.name as sector_name,
                   d.id as district_id, d.name as district_name,
                   p.id as province_id, p.name as province_name,
                   'fuzzy_match' as match_type
            FROM cells c
            JOIN sectors s ON c.sector_id = s.id
            JOIN districts d ON s.district_id = d.id
            JOIN provinces p ON d.province_id = p.id
            WHERE (c.name LIKE ? OR s.name LIKE ? OR d.name LIKE ? OR p.name LIKE ?)
            LIMIT 5
        ");

        $fuzzyTerm1 = '%' . $cellName . '%';
        $fuzzyTerm2 = '%' . $sectorName . '%';
        $fuzzyTerm3 = '%' . $districtName . '%';
        $fuzzyTerm4 = '%' . $provinceName . '%';

        $stmt->execute([$fuzzyTerm1, $fuzzyTerm2, $fuzzyTerm3, $fuzzyTerm4]);
        $fuzzyMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $fuzzyMatches;
    }

    /**
     * Generate migration report
     */
    public function generateMigrationReport()
    {
        $report = [];

        // Count users with string-based locations only
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM users
            WHERE cell_id IS NULL AND cell != 'Not Set' AND cell IS NOT NULL
        ");
        $stmt->execute();
        $report['users_to_migrate'] = $stmt->fetchColumn();

        // Count users already migrated
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM users
            WHERE cell_id IS NOT NULL
        ");
        $stmt->execute();
        $report['users_migrated'] = $stmt->fetchColumn();

        // Get unique string locations that need mapping
        $stmt = $this->db->prepare("
            SELECT DISTINCT cell, sector, district, province, COUNT(*) as user_count
            FROM users
            WHERE cell_id IS NULL AND cell != 'Not Set' AND cell IS NOT NULL
            GROUP BY cell, sector, district, province
            ORDER BY user_count DESC
        ");
        $stmt->execute();
        $report['unique_locations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $report;
    }

    /**
     * Validate migration results
     */
    public function validateMigration()
    {
        $validation = [];

        // Check for users with inconsistent location data
        $stmt = $this->db->prepare("
            SELECT u.id, u.cell, u.sector, u.district, u.province,
                   c.name as cell_name, s.name as sector_name,
                   d.name as district_name, p.name as province_name
            FROM users u
            LEFT JOIN cells c ON u.cell_id = c.id
            LEFT JOIN sectors s ON u.sector_id = s.id
            LEFT JOIN districts d ON u.district_id = d.id
            LEFT JOIN provinces p ON u.province_id = p.id
            WHERE u.cell_id IS NOT NULL
            AND (u.cell != c.name OR u.sector != s.name OR u.district != d.name OR u.province != p.name)
        ");
        $stmt->execute();
        $validation['inconsistent_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check for users with missing location IDs
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM users
            WHERE (cell_id IS NULL OR sector_id IS NULL OR district_id IS NULL OR province_id IS NULL)
            AND cell != 'Not Set' AND cell IS NOT NULL
        ");
        $stmt->execute();
        $validation['missing_ids_count'] = $stmt->fetchColumn();

        return $validation;
    }

    /**
     * Fix inconsistent location data
     */
    public function fixInconsistentData()
    {
        $stmt = $this->db->prepare("
            UPDATE users u
            JOIN cells c ON u.cell_id = c.id
            JOIN sectors s ON u.sector_id = s.id
            JOIN districts d ON u.district_id = d.id
            JOIN provinces p ON u.province_id = p.id
            SET u.cell = c.name, u.sector = s.name, u.district = d.name, u.province = p.name
            WHERE u.cell_id IS NOT NULL
        ");

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Create location mapping suggestions for unmapped locations
     */
    public function createLocationMappingSuggestions()
    {
        $suggestions = [];

        $stmt = $this->db->prepare("
            SELECT DISTINCT cell, sector, district, province, COUNT(*) as user_count
            FROM users
            WHERE cell_id IS NULL AND cell != 'Not Set' AND cell IS NOT NULL
            GROUP BY cell, sector, district, province
        ");
        $stmt->execute();
        $unmappedLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($unmappedLocations as $location) {
            $matches = $this->findClosestLocation(
                $location['cell'],
                $location['sector'],
                $location['district'],
                $location['province']
            );

            $suggestions[] = [
                'original'    => $location,
                'suggestions' => is_array($matches) ? $matches : [$matches],
                'user_count'  => $location['user_count'],
            ];
        }

        return $suggestions;
    }

    /**
     * Migrate notices from string-based to ID-based locations
     */
    public function migrateNotices()
    {
        $migratedCount = 0;
        $errorCount    = 0;

        try {
            // Get all notices with string-based locations that don't have ID-based locations
            $stmt = $this->db->prepare("
                SELECT id, cell, sector, district, province
                FROM notices
                WHERE cell_id IS NULL
                AND cell IS NOT NULL
                AND cell != ''
            ");

            $stmt->execute();
            $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($notices as $notice) {
                if ($this->migrateNoticeLocation(
                    $notice['id'],
                    $notice['cell'],
                    $notice['sector'],
                    $notice['district'],
                    $notice['province']
                )) {
                    $migratedCount++;
                } else {
                    $errorCount++;
                }
            }

            return [
                'total'    => count($notices),
                'migrated' => $migratedCount,
                'errors'   => $errorCount,
            ];

        } catch (Exception $e) {
            error_log("Error in notice migration: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Migrate a single notice location
     */
    private function migrateNoticeLocation($noticeId, $cellName, $sectorName, $districtName, $provinceName)
    {
        try {
            // Handle Kigali locations (including cases with empty province)
            if (($provinceName === 'Kigali' && $districtName === 'Kigali') ||
                (empty($provinceName) && $districtName === 'Kigali' && ! empty($sectorName) && ! empty($cellName))) {

                $actualProvince = 'Kigali City';
                $actualDistrict = $sectorName;
                $actualSector   = $cellName;

                // Find a cell in this sector
                $stmt = $this->db->prepare("
                    SELECT c.id as cell_id, s.id as sector_id, d.id as district_id, p.id as province_id,
                           c.name as cell_name
                    FROM cells c
                    JOIN sectors s ON c.sector_id = s.id
                    JOIN districts d ON s.district_id = d.id
                    JOIN provinces p ON d.province_id = p.id
                    WHERE s.name = ? AND d.name = ? AND p.name = ?
                    LIMIT 1
                ");

                $stmt->execute([$actualSector, $actualDistrict, $actualProvince]);
                $location = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($location) {
                    // Update notice with the new location IDs
                    $updateStmt = $this->db->prepare("
                        UPDATE notices
                        SET cell_id = ?, sector_id = ?, district_id = ?, province_id = ?
                        WHERE id = ?
                    ");

                    $updateStmt->execute([
                        $location['cell_id'],
                        $location['sector_id'],
                        $location['district_id'],
                        $location['province_id'],
                        $noticeId,
                    ]);

                    error_log("Migrated notice $noticeId: '$provinceName'/'$districtName'/'$sectorName'/'$cellName' -> {$actualProvince}/{$actualDistrict}/{$actualSector}/{$location['cell_name']}");
                    return true;
                } else {
                    // Try fallback to any cell in the district
                    $fallbackStmt = $this->db->prepare("
                        SELECT c.id as cell_id, s.id as sector_id, d.id as district_id, p.id as province_id,
                               c.name as cell_name, s.name as sector_name
                        FROM cells c
                        JOIN sectors s ON c.sector_id = s.id
                        JOIN districts d ON s.district_id = d.id
                        JOIN provinces p ON d.province_id = p.id
                        WHERE d.name = ? AND p.name = ?
                        ORDER BY s.name, c.name
                        LIMIT 1
                    ");

                    $fallbackStmt->execute([$actualDistrict, $actualProvince]);
                    $fallback = $fallbackStmt->fetch(PDO::FETCH_ASSOC);

                    if ($fallback) {
                        $updateStmt = $this->db->prepare("
                            UPDATE notices
                            SET cell_id = ?, sector_id = ?, district_id = ?, province_id = ?
                            WHERE id = ?
                        ");

                        $updateStmt->execute([
                            $fallback['cell_id'],
                            $fallback['sector_id'],
                            $fallback['district_id'],
                            $fallback['province_id'],
                            $noticeId,
                        ]);

                        error_log("Migrated notice $noticeId (fallback): '$provinceName'/'$districtName'/'$sectorName'/'$cellName' -> {$actualProvince}/{$actualDistrict}/{$fallback['sector_name']}/{$fallback['cell_name']} [sector '$actualSector' not found]");
                        return true;
                    }
                }
            }

            error_log("Notice location not found for notice $noticeId: '$cellName', '$sectorName', '$districtName', '$provinceName'");
            return false;

        } catch (Exception $e) {
            error_log("Error migrating notice $noticeId location: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Migrate umuganda events from string-based to ID-based locations
     */
    public function migrateEvents()
    {
        $migratedCount = 0;
        $errorCount    = 0;

        try {
            // Get all events with string-based locations that don't have ID-based locations
            $stmt = $this->db->prepare("
                SELECT id, cell, sector, district, province
                FROM umuganda_events
                WHERE cell_id IS NULL
                AND cell IS NOT NULL
                AND cell != ''
            ");

            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($events as $event) {
                if ($this->migrateEventLocation(
                    $event['id'],
                    $event['cell'],
                    $event['sector'],
                    $event['district'],
                    $event['province']
                )) {
                    $migratedCount++;
                } else {
                    $errorCount++;
                }
            }

            return [
                'total'    => count($events),
                'migrated' => $migratedCount,
                'errors'   => $errorCount,
            ];

        } catch (Exception $e) {
            error_log("Error in event migration: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Migrate a single event location
     */
    private function migrateEventLocation($eventId, $cellName, $sectorName, $districtName, $provinceName)
    {
        try {
            // Use the same logic as user migration for Kigali locations
            if ($provinceName === 'Kigali' && $districtName === 'Kigali') {
                $actualProvince = 'Kigali City';
                $actualDistrict = $sectorName;
                $actualSector   = $cellName;

                // Find a cell in this sector
                $stmt = $this->db->prepare("
                    SELECT c.id as cell_id, s.id as sector_id, d.id as district_id, p.id as province_id,
                           c.name as cell_name
                    FROM cells c
                    JOIN sectors s ON c.sector_id = s.id
                    JOIN districts d ON s.district_id = d.id
                    JOIN provinces p ON d.province_id = p.id
                    WHERE s.name = ? AND d.name = ? AND p.name = ?
                    LIMIT 1
                ");

                $stmt->execute([$actualSector, $actualDistrict, $actualProvince]);
                $location = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($location) {
                    // Update event with the new location IDs
                    $updateStmt = $this->db->prepare("
                        UPDATE umuganda_events
                        SET cell_id = ?, sector_id = ?, district_id = ?, province_id = ?
                        WHERE id = ?
                    ");

                    $updateStmt->execute([
                        $location['cell_id'],
                        $location['sector_id'],
                        $location['district_id'],
                        $location['province_id'],
                        $eventId,
                    ]);

                    error_log("Migrated event $eventId: $provinceName/$districtName/$sectorName/$cellName -> {$actualProvince}/{$actualDistrict}/{$actualSector}/{$location['cell_name']}");
                    return true;
                } else {
                    // Try fallback to any cell in the district
                    $fallbackStmt = $this->db->prepare("
                        SELECT c.id as cell_id, s.id as sector_id, d.id as district_id, p.id as province_id,
                               c.name as cell_name, s.name as sector_name
                        FROM cells c
                        JOIN sectors s ON c.sector_id = s.id
                        JOIN districts d ON s.district_id = d.id
                        JOIN provinces p ON d.province_id = p.id
                        WHERE d.name = ? AND p.name = ?
                        ORDER BY s.name, c.name
                        LIMIT 1
                    ");

                    $fallbackStmt->execute([$actualDistrict, $actualProvince]);
                    $fallback = $fallbackStmt->fetch(PDO::FETCH_ASSOC);

                    if ($fallback) {
                        $updateStmt = $this->db->prepare("
                            UPDATE umuganda_events
                            SET cell_id = ?, sector_id = ?, district_id = ?, province_id = ?
                            WHERE id = ?
                        ");

                        $updateStmt->execute([
                            $fallback['cell_id'],
                            $fallback['sector_id'],
                            $fallback['district_id'],
                            $fallback['province_id'],
                            $eventId,
                        ]);

                        error_log("Migrated event $eventId (fallback): $provinceName/$districtName/$sectorName/$cellName -> {$actualProvince}/{$actualDistrict}/{$fallback['sector_name']}/{$fallback['cell_name']} [sector '$actualSector' not found]");
                        return true;
                    }
                }
            }

            error_log("Event location not found for event $eventId: $cellName, $sectorName, $districtName, $provinceName");
            return false;

        } catch (Exception $e) {
            error_log("Error migrating event $eventId location: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fix inconsistent location data for notices
     */
    public function fixNoticeInconsistentData()
    {
        $stmt = $this->db->prepare("
            UPDATE notices n
            JOIN cells c ON n.cell_id = c.id
            JOIN sectors s ON n.sector_id = s.id
            JOIN districts d ON n.district_id = d.id
            JOIN provinces p ON n.province_id = p.id
            SET n.cell = c.name, n.sector = s.name, n.district = d.name, n.province = p.name
            WHERE n.cell_id IS NOT NULL
        ");

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Fix inconsistent location data for events
     */
    public function fixEventInconsistentData()
    {
        $stmt = $this->db->prepare("
            UPDATE umuganda_events e
            JOIN cells c ON e.cell_id = c.id
            JOIN sectors s ON e.sector_id = s.id
            JOIN districts d ON e.district_id = d.id
            JOIN provinces p ON e.province_id = p.id
            SET e.cell = c.name, e.sector = s.name, e.district = d.name, e.province = p.name
            WHERE e.cell_id IS NOT NULL
        ");

        $stmt->execute();
        return $stmt->rowCount();
    }
}
