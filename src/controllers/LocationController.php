<?php
/**
 * Location Controller
 * Handles location-related API endpoints
 */

require_once __DIR__ . '/../models/LocationManager.php';

class LocationController
{
    private $locationManager;

    public function __construct()
    {
        global $pdo;
        $this->locationManager = new LocationManager($pdo);
    }

    /**
     * Get all provinces
     * GET /api/locations/provinces
     */
    public function getProvinces()
    {
        try {
            $provinces = $this->locationManager->getProvinces();
            successResponse($provinces);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get districts by province
     * GET /api/locations/districts?province_id=X
     */
    public function getDistricts()
    {
        try {
            $provinceId = $_GET['province_id'] ?? null;
            if (! $provinceId) {
                errorResponse('Province ID is required', 400);
            }

            $districts = $this->locationManager->getDistrictsByProvince($provinceId);
            successResponse($districts);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get sectors by district
     * GET /api/locations/sectors?district_id=X
     */
    public function getSectors()
    {
        try {
            $districtId = $_GET['district_id'] ?? null;
            if (! $districtId) {
                errorResponse('District ID is required', 400);
            }

            $sectors = $this->locationManager->getSectorsByDistrict($districtId);
            successResponse($sectors);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get cells by sector
     * GET /api/locations/cells?sector_id=X
     */
    public function getCells()
    {
        try {
            $sectorId = $_GET['sector_id'] ?? null;
            if (! $sectorId) {
                errorResponse('Sector ID is required', 400);
            }

            $cells = $this->locationManager->getCellsBySector($sectorId);
            successResponse($cells);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Handle dynamic method calls based on action parameter
     */
    public function index()
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'provinces':
                $this->getProvinces();
                break;
            case 'districts':
                $this->getDistricts();
                break;
            case 'sectors':
                $this->getSectors();
                break;
            case 'cells':
                $this->getCells();
                break;
            default:
                errorResponse('Invalid action. Supported actions: provinces, districts, sectors, cells', 400);
        }
    }

    /**
     * GET /api/locations - same as index()
     */
    public function getIndex()
    {
        $this->index();
    }

    // Handle alternative URL structures
    public function provinces()
    {
        $this->getProvinces();
    }

    public function districts()
    {
        $this->getDistricts();
    }

    public function sectors()
    {
        $this->getSectors();
    }

    public function cells()
    {
        $this->getCells();
    }
}
