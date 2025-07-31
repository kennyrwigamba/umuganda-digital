<?php
/**
 * Database Migration: Add SuperAdmin Role
 * This script adds 'superadmin' to the role enum and creates a default superadmin account
 */

require_once 'config/db.php';

try {
    echo "🔄 Starting SuperAdmin Role Migration...\n\n";

    // Step 1: Update the role ENUM to include superadmin
    echo "1. Updating role ENUM to include 'superadmin'...\n";

    $alterRoleQuery = "
        ALTER TABLE users
        MODIFY COLUMN role ENUM('superadmin', 'admin', 'resident') DEFAULT 'resident'
    ";

    if ($db->query($alterRoleQuery)) {
        echo "   ✅ Role ENUM updated successfully\n";
    } else {
        throw new Exception("Failed to update role ENUM: " . $db->error);
    }

    // Step 2: Check if superadmin already exists
    echo "2. Checking for existing superadmin...\n";

    $checkSuperAdmin = $db->prepare("SELECT id, email FROM users WHERE role = 'superadmin' LIMIT 1");
    $checkSuperAdmin->execute();
    $existingSuperAdmin = $checkSuperAdmin->get_result()->fetch_assoc();

    if ($existingSuperAdmin) {
        echo "   ℹ️  SuperAdmin already exists: " . $existingSuperAdmin['email'] . "\n";
        $superAdminId = $existingSuperAdmin['id'];
    } else {
        // Step 3: Create default superadmin account
        echo "3. Creating default SuperAdmin account...\n";

        $superPassword = password_hash('super123', PASSWORD_DEFAULT);

        $createSuperAdmin = $db->prepare("
            INSERT INTO users (
                national_id, first_name, last_name, email, phone, password,
                cell, sector, district, province, date_of_birth, gender, role, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $params = [
            '1999000000000001',  // national_id
            'Super',             // first_name
            'Administrator',     // last_name
            'super@umuganda.rw', // email
            '+250788000001',     // phone
            $superPassword,      // password
            'Kimisagara',        // cell
            'Nyarugenge',        // sector
            'Nyarugenge',        // district
            'Kigali City',       // province
            '1985-01-01',        // date_of_birth
            'male',              // gender
            'superadmin',        // role
            'active',            // status
        ];

        $createSuperAdmin->bind_param(
            'ssssssssssssss',
            ...$params
        );

        if ($createSuperAdmin->execute()) {
            $superAdminId = $db->insert_id;
            echo "   ✅ SuperAdmin created successfully\n";
            echo "   📧 Email: super@umuganda.rw\n";
            echo "   🔑 Password: super123\n";
        } else {
            throw new Exception("Failed to create superadmin: " . $db->error);
        }
    }

    // Step 4: Update existing admin assignments to be created by superadmin
    echo "4. Updating admin assignments...\n";

    $updateAssignments = $db->prepare("
        UPDATE admin_assignments
        SET assigned_by = ?
        WHERE assigned_by IS NULL OR assigned_by != ?
    ");
    $updateAssignments->bind_param('ii', $superAdminId, $superAdminId);

    if ($updateAssignments->execute()) {
        $updatedRows = $updateAssignments->affected_rows;
        echo "   ✅ Updated {$updatedRows} admin assignments\n";
    }

    // Step 5: Show current admin hierarchy
    echo "\n5. Current Admin Hierarchy:\n";

    $hierarchyQuery = "
        SELECT
            u.email,
            u.role,
            u.first_name,
            u.last_name,
            CASE
                WHEN u.role = 'superadmin' THEN 'System Administrator'
                WHEN aa.sector_id IS NOT NULL THEN CONCAT('Sector Admin: ', s.name)
                ELSE 'Unassigned Admin'
            END as assignment
        FROM users u
        LEFT JOIN admin_assignments aa ON u.id = aa.admin_id
        LEFT JOIN sectors s ON aa.sector_id = s.id
        WHERE u.role IN ('superadmin', 'admin')
        ORDER BY
            CASE u.role
                WHEN 'superadmin' THEN 1
                WHEN 'admin' THEN 2
                ELSE 3
            END,
            u.email
    ";

    $hierarchyResult = $db->query($hierarchyQuery);

    echo "   👤 Admin Accounts:\n";
    while ($admin = $hierarchyResult->fetch_assoc()) {
        $roleIcon = $admin['role'] === 'superadmin' ? '👑' : '👨‍💼';
        echo "   {$roleIcon} {$admin['first_name']} {$admin['last_name']} ({$admin['email']})\n";
        echo "      📋 {$admin['assignment']}\n";
    }

    echo "\n✅ SuperAdmin Role Migration completed successfully!\n\n";
    echo "🔐 Login Credentials:\n";
    echo "   SuperAdmin: super@umuganda.rw / super123\n";
    echo "   Gasabo Admin: admin.gasabo@umuganda.rw / admin123\n";
    echo "   Nyarugenge Admin: admin.nyarugenge@umuganda.rw / admin123\n\n";
    echo "💡 The SuperAdmin can now assign admins to sectors and has full system access.\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
