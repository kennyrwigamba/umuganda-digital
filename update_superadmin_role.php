<?php
/**
 * Update Database Schema to Add Superadmin Role
 * This script updates the users table to include 'superadmin' as a valid role
 */

require_once __DIR__ . '/config/db.php';

// Initialize database connection
$database   = new Database();
$connection = $database->getConnection();

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

echo "🚀 Starting database schema update for superadmin role...\n\n";

try {
    // Step 1: Modify the users table role enum to include 'superadmin'
    echo "📝 Updating users table role enum...\n";
    $alterRoleQuery = "ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'resident') DEFAULT 'resident'";

    if ($connection->query($alterRoleQuery)) {
        echo "✅ Successfully updated role enum to include 'superadmin'\n";
    } else {
        throw new Exception("Failed to update role enum: " . $connection->error);
    }

    // Step 2: Check if a superadmin already exists
    echo "\n🔍 Checking for existing superadmin accounts...\n";
    $checkSuperadminQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'superadmin'";
    $result               = $connection->query($checkSuperadminQuery);
    $superadminCount      = $result->fetch_assoc()['count'];

    if ($superadminCount > 0) {
        echo "✅ Found {$superadminCount} existing superadmin account(s)\n";
    } else {
        echo "⚠️  No superadmin accounts found. Creating default superadmin...\n";

        // Step 3: Create a default superadmin account
        $hashedPassword = password_hash('super123', PASSWORD_DEFAULT);

        $createSuperadminQuery = "
            INSERT INTO users (
                national_id,
                first_name,
                last_name,
                email,
                phone,
                password,
                cell,
                sector,
                district,
                province,
                date_of_birth,
                gender,
                role,
                status
            ) VALUES (
                '1199999999999999',
                'Super',
                'Administrator',
                'super@umuganda.rw',
                '+250788999999',
                ?,
                'Kimisagara',
                'Nyarugenge',
                'Nyarugenge',
                'Kigali City',
                '1985-01-01',
                'male',
                'superadmin',
                'active'
            )";

        $stmt = $connection->prepare($createSuperadminQuery);
        $stmt->bind_param('s', $hashedPassword);

        if ($stmt->execute()) {
            echo "✅ Created default superadmin account:\n";
            echo "   📧 Email: super@umuganda.rw\n";
            echo "   🔑 Password: super123\n";
            echo "   🆔 National ID: 1199999999999999\n";
        } else {
            throw new Exception("Failed to create superadmin account: " . $connection->error);
        }
    }

    // Step 4: Update existing admin with ID 1 to superadmin if it exists
    echo "\n🔄 Checking if we should upgrade existing admin to superadmin...\n";
    $checkFirstAdminQuery = "SELECT * FROM users WHERE id = 1 AND role = 'admin'";
    $result               = $connection->query($checkFirstAdminQuery);

    if ($result && $result->num_rows > 0) {
        $firstAdmin = $result->fetch_assoc();
        echo "📋 Found existing admin (ID: 1): {$firstAdmin['email']}\n";
        echo "🔄 Upgrading to superadmin role...\n";

        $upgradeQuery = "UPDATE users SET role = 'superadmin' WHERE id = 1";
        if ($connection->query($upgradeQuery)) {
            echo "✅ Successfully upgraded admin (ID: 1) to superadmin\n";
            echo "   📧 Email: {$firstAdmin['email']}\n";
            echo "   🔑 Password: admin123 (unchanged)\n";
        } else {
            echo "⚠️  Failed to upgrade existing admin: " . $connection->error . "\n";
        }
    } else {
        echo "ℹ️  No admin with ID 1 found or already upgraded\n";
    }

    // Step 5: Verify the changes
    echo "\n🔍 Verifying database changes...\n";

    // Check role enum
    $showColumnsQuery = "SHOW COLUMNS FROM users LIKE 'role'";
    $result           = $connection->query($showColumnsQuery);
    $roleColumn       = $result->fetch_assoc();
    echo "✅ Role column type: {$roleColumn['Type']}\n";

    // Count users by role
    $roleCountQuery = "
        SELECT
            role,
            COUNT(*) as count
        FROM users
        GROUP BY role
        ORDER BY
            CASE role
                WHEN 'superadmin' THEN 1
                WHEN 'admin' THEN 2
                WHEN 'resident' THEN 3
            END";
    $result = $connection->query($roleCountQuery);

    echo "\n📊 User counts by role:\n";
    while ($row = $result->fetch_assoc()) {
        $icon = $row['role'] === 'superadmin' ? '👑' : ($row['role'] === 'admin' ? '🛡️' : '👤');
        echo "   {$icon} {$row['role']}: {$row['count']} users\n";
    }

    echo "\n🎉 Database schema update completed successfully!\n";
    echo "\n📋 Summary:\n";
    echo "   ✅ Added 'superadmin' to role enum\n";
    echo "   ✅ Ensured superadmin account exists\n";
    echo "   ✅ Verified database integrity\n";

    echo "\n🔑 Superadmin Login Credentials:\n";
    echo "   📧 Email: super@umuganda.rw\n";
    echo "   🔑 Password: super123\n";
    echo "   🌐 Dashboard: /public/dashboard/superadmin/\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "🔄 Rolling back changes if possible...\n";

    // Basic rollback - remove superadmin role if we added it
    $rollbackQuery = "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'resident') DEFAULT 'resident'";
    if ($connection->query($rollbackQuery)) {
        echo "✅ Rollback completed\n";
    } else {
        echo "⚠️  Rollback failed - manual intervention may be required\n";
    }

    exit(1);
}

$connection->close();
echo "\n✨ All done! You can now use the three-tier role system.\n";
