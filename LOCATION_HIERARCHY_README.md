# Rwanda Administrative Hierarchy System

This system implements Rwanda's administrative hierarchy (Province > District > Sector > Cell) for the Umuganda Digital platform, enabling sector-level administration and proper location management.

## 🏗️ Database Schema

### New Tables Created

1. **provinces** - Rwanda's 5 provinces
2. **districts** - Districts within each province
3. **sectors** - Sectors within each district
4. **cells** - Cells within each sector
5. **admin_assignments** - Maps admins to sectors they manage

### Modified Tables

- **users** - Added foreign key columns (cell_id, sector_id, district_id, province_id)
- **umuganda_events** - Added location foreign keys
- **notices** - Added location foreign keys

## 📦 Installation

### 1. Run Database Setup Scripts

Execute the SQL scripts in this order:

```bash
mysql -u [username] -p [database_name] < location_hierarchy_schema.sql
mysql -u [username] -p [database_name] < location_hierarchy_migration.sql
mysql -u [username] -p [database_name] < location_helper_functions.sql
```

Or run the master script:

```bash
mysql -u [username] -p [database_name] < setup_location_hierarchy.sql
```

### 2. Copy PHP Classes

Place the PHP classes in your project:

- `src/models/LocationManager.php`
- `src/models/AdminManager.php`

### 3. Set Up API Endpoints

Copy the API file:

- `public/api/locations.php`

### 4. Include JavaScript Library

Add to your project:

- `public/js/location-hierarchy.js`

## 🚀 Usage

### PHP Usage

#### Location Management

```php
<?php
require_once 'src/models/LocationManager.php';

$locationManager = new LocationManager($pdo);

// Get all provinces
$provinces = $locationManager->getProvinces();

// Get districts in a province
$districts = $locationManager->getDistrictsByProvince($provinceId);

// Get sectors in a district
$sectors = $locationManager->getSectorsByDistrict($districtId);

// Get cells in a sector
$cells = $locationManager->getCellsBySector($sectorId);

// Get full location hierarchy for a cell
$hierarchy = $locationManager->getLocationHierarchy($cellId);

// Get location path string
$path = $locationManager->getLocationPath($cellId);
// Returns: "Kigali City > Nyarugenge > Kimisagara > Kivugiza"

// Update user location
$locationManager->updateUserLocation($userId, $cellId);
?>
```

#### Admin Management

```php
<?php
require_once 'src/models/AdminManager.php';
require_once 'src/models/LocationManager.php';

$adminManager = new AdminManager($pdo);

// Assign admin to sector
$adminManager->assignAdminToSector($adminId, $sectorId, $assignedBy, 'Assignment notes');

// Get sectors managed by admin
$sectors = $adminManager->getAdminSectors($adminId);

// Get residents managed by admin
$residents = $adminManager->getAdminManagedResidents($adminId, [
    'status' => 'active',
    'sector_id' => $sectorId
]);

// Check if admin can manage a resident
$canManage = $adminManager->canAdminManageResident($adminId, $residentId);

// Get admin statistics
$stats = $adminManager->getAdminStats($adminId);
?>
```

### JavaScript Usage

#### Basic Form Integration

```html
<!-- Location selection form -->
<form id="registrationForm">
  <select id="province" name="province" required>
    <option value="">Select Province</option>
  </select>

  <select id="district" name="district" required disabled>
    <option value="">Select District</option>
  </select>

  <select id="sector" name="sector" required disabled>
    <option value="">Select Sector</option>
  </select>

  <select id="cell" name="cell" required disabled>
    <option value="">Select Cell</option>
  </select>

  <!-- Hidden fields for IDs -->
  <input type="hidden" name="province_id" />
  <input type="hidden" name="district_id" />
  <input type="hidden" name="sector_id" />
  <input type="hidden" name="cell_id" />
</form>

<script>
  // Initialize location hierarchy
  const locationHierarchy =
    LocationUtils.initRegistrationForm("#registrationForm");

  // Listen for location changes
  $("#registrationForm").on("locationChanged", function (event, location) {
    console.log("Location changed:", location);

    // Validate form or perform other actions
    if (locationHierarchy.isValid()) {
      $("#submitBtn").prop("disabled", false);
    }
  });
</script>
```

#### Advanced Usage

```javascript
// Custom initialization
const locationManager = new LocationHierarchy({
  provinceSelect: "#myProvince",
  districtSelect: "#myDistrict",
  sectorSelect: "#mySector",
  cellSelect: "#myCell",
  apiEndpoint: "/api/locations.php",
  onLocationChange: function (location) {
    // Custom logic when location changes
    updateMap(location);
    loadNearbyServices(location);
  },
});

// Set location programmatically
locationManager.setLocation(1, 2, 3, 4); // province, district, sector, cell IDs

// Search locations
const results = await locationManager.searchLocations("Kigali", "province");

// Validate location hierarchy
const isValid = await locationManager.validateHierarchy(
  cellId,
  sectorId,
  districtId,
  provinceId
);

// Get location path
const pathInfo = await locationManager.getLocationPath(cellId);
```

## 🔧 API Endpoints

### Available Endpoints

- `GET /api/locations.php?action=provinces` - Get all provinces
- `GET /api/locations.php?action=districts&province_id=1` - Get districts in province
- `GET /api/locations.php?action=sectors&district_id=1` - Get sectors in district
- `GET /api/locations.php?action=cells&sector_id=1` - Get cells in sector
- `GET /api/locations.php?action=location_path&cell_id=1` - Get location path
- `GET /api/locations.php?action=search&q=Kigali&type=province` - Search locations
- `GET /api/locations.php?action=validate_hierarchy&cell_id=1&sector_id=1&district_id=1&province_id=1` - Validate hierarchy

### Response Format

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kigali City",
      "code": "KGL"
    }
  ]
}
```

## 🗃️ Database Views

### Available Views

- `users_with_location` - Users with full location hierarchy
- `events_with_location` - Events with location details
- `notices_with_location` - Notices with location targeting
- `location_hierarchy` - Complete location hierarchy
- `admin_sectors` - Admin sector assignments
- `resident_counts_by_location` - Resident statistics by location

### Example Usage

```sql
-- Get all users with their location details
SELECT * FROM users_with_location WHERE sector_name = 'Kimisagara';

-- Get resident counts by cell
SELECT * FROM resident_counts_by_location WHERE district_name = 'Nyarugenge';

-- Get admin assignments
SELECT * FROM admin_sectors WHERE admin_id = 1;
```

## 🔧 Stored Procedures

### Available Procedures

- `AssignAdminToSector(admin_id, sector_id, assigned_by, notes)`
- `RemoveAdminFromSector(admin_id, sector_id)`
- `GetAdminManagedResidents(admin_id)`
- `SetUserLocation(user_id, cell_id)`
- `GetAdminLocationStats(admin_id)`

### Example Usage

```sql
-- Assign admin to sector
CALL AssignAdminToSector(1, 5, 1, 'Initial assignment');

-- Get residents managed by admin
CALL GetAdminManagedResidents(1);

-- Set user location
CALL SetUserLocation(123, 45);
```

## 📊 Admin Dashboard Integration

### Update Admin Dashboard

To show sector-specific data in the admin dashboard:

```php
<?php
// In your admin dashboard controller
session_start();
$adminId = $_SESSION['user_id'];

$adminManager = new AdminManager($pdo);
$locationManager = new LocationManager($pdo);

// Get admin's managed sectors
$sectors = $adminManager->getAdminSectors($adminId);

// Get admin statistics
$stats = $adminManager->getAdminStats($adminId);

// Get residents in admin's sectors
$residents = $adminManager->getAdminManagedResidents($adminId, [
    'status' => 'active'
]);

// Pass to view
?>
```

### Update Dashboard View

```php
<!-- Show admin's assigned sectors -->
<div class="sectors-card">
    <h3>Your Assigned Sectors</h3>
    <?php foreach ($sectors as $sector): ?>
        <div class="sector-item">
            <strong><?= htmlspecialchars($sector['sector_name']) ?></strong>
            <small><?= htmlspecialchars($sector['district_name']) ?>, <?= htmlspecialchars($sector['province_name']) ?></small>
        </div>
    <?php endforeach; ?>
</div>

<!-- Show statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <h4>Total Residents</h4>
        <span class="stat-number"><?= $stats['total_residents'] ?></span>
    </div>
    <div class="stat-card">
        <h4>Active Residents</h4>
        <span class="stat-number"><?= $stats['active_residents'] ?></span>
    </div>
    <div class="stat-card">
        <h4>Sectors Managed</h4>
        <span class="stat-number"><?= $stats['sectors_managed'] ?></span>
    </div>
</div>
```

## 🔒 Security Considerations

1. **Admin Authorization**: Always verify admin can manage specific locations
2. **Data Validation**: Validate location hierarchy before saving
3. **SQL Injection**: All queries use prepared statements
4. **Cross-Sector Access**: Prevent admins from accessing other sectors

```php
// Example security check
if (!$adminManager->canAdminManageResident($adminId, $residentId)) {
    throw new Exception('Unauthorized access to resident data');
}
```

## 🔧 Migration from Legacy System

If you have existing data with string-based locations:

```sql
-- Update existing users (example for Kimisagara)
UPDATE users
SET cell_id = (SELECT id FROM cells WHERE name = 'Kivugiza' LIMIT 1),
    sector_id = (SELECT id FROM sectors WHERE name = 'Kimisagara' LIMIT 1),
    district_id = (SELECT id FROM districts WHERE name = 'Nyarugenge' LIMIT 1),
    province_id = (SELECT id FROM provinces WHERE name = 'Kigali City' LIMIT 1)
WHERE cell = 'Kivugiza' AND sector = 'Kimisagara';
```

## 🐛 Troubleshooting

### Common Issues

1. **Dropdowns not loading**: Check API endpoint path and permissions
2. **Location validation failing**: Ensure hierarchy is properly linked
3. **Admin can't see residents**: Verify admin assignment to correct sector

### Debug Commands

```sql
-- Check location hierarchy
SELECT GetLocationPath(45) as location_path;

-- Verify admin assignments
SELECT * FROM admin_assignments WHERE admin_id = 1 AND is_active = 1;

-- Check user location consistency
SELECT u.*, GetLocationPath(u.cell_id) as full_location
FROM users u WHERE u.id = 123;
```

## 📈 Performance Optimization

1. **Indexing**: All foreign keys are indexed
2. **Caching**: Consider caching location hierarchy in application
3. **Lazy Loading**: Load location levels on demand
4. **Pagination**: Use pagination for large resident lists

## 🎯 Next Steps

1. Add location-based reporting
2. Implement geographic boundaries (coordinates)
3. Add village/umudugudu level if needed
4. Create mobile app integration
5. Add location-based notifications

## 🤝 Contributing

When adding new administrative levels or modifying the hierarchy:

1. Update database schema
2. Modify PHP classes
3. Update JavaScript library
4. Test API endpoints
5. Update documentation

## 📝 License

This location hierarchy system is part of the Umuganda Digital platform.
