<?php
require_once __DIR__ . '/config/db.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Check if admin_settings table exists
    $result = $db->query("SHOW TABLES LIKE 'admin_settings'");
    if ($result->num_rows > 0) {
        echo "✅ admin_settings table exists\n";

        // Show table structure
        $result = $db->query("DESCRIBE admin_settings");
        echo "\nTable structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "❌ admin_settings table does not exist\n";
        echo "Creating admin_settings table...\n";

        $sql = "CREATE TABLE admin_settings (
            admin_id INT PRIMARY KEY,
            notification_enabled TINYINT(1) DEFAULT 1,
            email_notifications TINYINT(1) DEFAULT 1,
            sms_notifications TINYINT(1) DEFAULT 0,
            default_fine_amount DECIMAL(10,2) DEFAULT 1000.00,
            session_duration INT DEFAULT 60,
            language VARCHAR(5) DEFAULT 'en',
            timezone VARCHAR(50) DEFAULT 'Africa/Kigali',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
        )";

        if ($db->query($sql)) {
            echo "✅ admin_settings table created successfully\n";
        } else {
            echo "❌ Error creating table: " . $db->error . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
