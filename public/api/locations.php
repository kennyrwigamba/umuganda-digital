<?php
/**
 * Location API endpoints
 * Provides AJAX endpoints for location hierarchy dropdowns
 */

require_once '../config/db.php';
require_once '../src/models/LocationManager.php';

// Set JSON header
header('Content-Type: application/json');

// Get the action from the request
$action = $_GET['action'] ?? '';

try {
    $locationManager = new LocationManager($pdo);

    switch ($action) {
        case 'provinces':
            $provinces = $locationManager->getProvinces();
            echo json_encode([
                'success' => true,
                'data'    => $provinces,
            ]);
            break;

        case 'districts':
            $provinceId = $_GET['province_id'] ?? null;
            if (! $provinceId) {
                throw new Exception('Province ID is required');
            }

            $districts = $locationManager->getDistrictsByProvince($provinceId);
            echo json_encode([
                'success' => true,
                'data'    => $districts,
            ]);
            break;

        case 'sectors':
            $districtId = $_GET['district_id'] ?? null;
            if (! $districtId) {
                throw new Exception('District ID is required');
            }

            $sectors = $locationManager->getSectorsByDistrict($districtId);
            echo json_encode([
                'success' => true,
                'data'    => $sectors,
            ]);
            break;

        case 'cells':
            $sectorId = $_GET['sector_id'] ?? null;
            if (! $sectorId) {
                throw new Exception('Sector ID is required');
            }

            $cells = $locationManager->getCellsBySector($sectorId);
            echo json_encode([
                'success' => true,
                'data'    => $cells,
            ]);
            break;

        case 'location_path':
            $cellId = $_GET['cell_id'] ?? null;
            if (! $cellId) {
                throw new Exception('Cell ID is required');
            }

            $path      = $locationManager->getLocationPath($cellId);
            $hierarchy = $locationManager->getLocationHierarchy($cellId);

            echo json_encode([
                'success' => true,
                'data'    => [
                    'path'      => $path,
                    'hierarchy' => $hierarchy,
                ],
            ]);
            break;

        case 'search':
            $searchTerm = $_GET['q'] ?? '';
            $type       = $_GET['type'] ?? null;

            if (strlen($searchTerm) < 2) {
                throw new Exception('Search term must be at least 2 characters');
            }

            $results = $locationManager->searchLocations($searchTerm, $type);
            echo json_encode([
                'success' => true,
                'data'    => $results,
            ]);
            break;

        case 'complete_hierarchy':
            $hierarchy = $locationManager->getCompleteHierarchy();
            echo json_encode([
                'success' => true,
                'data'    => $hierarchy,
            ]);
            break;

        case 'resident_count':
            $locationId   = $_GET['location_id'] ?? null;
            $locationType = $_GET['type'] ?? null;

            if (! $locationId || ! $locationType) {
                throw new Exception('Location ID and type are required');
            }

            $count = $locationManager->getResidentCountByLocation($locationId, $locationType);
            echo json_encode([
                'success' => true,
                'data'    => ['count' => $count],
            ]);
            break;

        case 'validate_hierarchy':
            $cellId     = $_GET['cell_id'] ?? null;
            $sectorId   = $_GET['sector_id'] ?? null;
            $districtId = $_GET['district_id'] ?? null;
            $provinceId = $_GET['province_id'] ?? null;

            if (! $cellId || ! $sectorId || ! $districtId || ! $provinceId) {
                throw new Exception('All location IDs are required for validation');
            }

            $isValid = $locationManager->validateLocationHierarchy($cellId, $sectorId, $districtId, $provinceId);
            echo json_encode([
                'success' => true,
                'data'    => ['valid' => $isValid],
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
    ]);
}
