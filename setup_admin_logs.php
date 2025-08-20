<?php
require_once 'admin/config.php';

try {
    // Create admin_logs table if it doesn't exist
    $sql = "
    CREATE TABLE IF NOT EXISTS admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        admin_username VARCHAR(50) NOT NULL,
        action VARCHAR(100) NOT NULL,
        booking_id INT,
        booking_type ENUM('daily', 'monthly') NULL,
        old_status VARCHAR(20),
        new_status VARCHAR(20),
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "Admin logs table created successfully!\n";
    
    // Create indexes (MySQL doesn't support IF NOT EXISTS for indexes)
    try {
        $pdo->exec("CREATE INDEX idx_admin_logs_admin_id ON admin_logs(admin_id)");
        echo "Admin logs admin_id index created successfully!\n";
    } catch (Exception $e) {
        echo "Admin logs admin_id index already exists or failed to create.\n";
    }
    
    try {
        $pdo->exec("CREATE INDEX idx_admin_logs_booking_id ON admin_logs(booking_id)");
        echo "Admin logs booking_id index created successfully!\n";
    } catch (Exception $e) {
        echo "Admin logs booking_id index already exists or failed to create.\n";
    }
    
    try {
        $pdo->exec("CREATE INDEX idx_admin_logs_created_at ON admin_logs(created_at)");
        echo "Admin logs created_at index created successfully!\n";
    } catch (Exception $e) {
        echo "Admin logs created_at index already exists or failed to create.\n";
    }
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_logs'");
    if ($stmt->rowCount() > 0) {
        echo "Admin logs table exists and is ready to use.\n";
    } else {
        echo "Error: Admin logs table was not created.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
