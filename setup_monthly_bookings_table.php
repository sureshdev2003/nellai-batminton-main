<?php
require_once 'admin/config.php';

try {
    // Create monthly_bookings table if it doesn't exist
    $sql = "
    CREATE TABLE IF NOT EXISTS monthly_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        court_slot INT NOT NULL,
        time_slot VARCHAR(10) NOT NULL,
        start_month VARCHAR(7) NOT NULL,
        duration INT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        UNIQUE KEY unique_monthly_booking (court_slot, time_slot, start_month, duration)
    )";
    
    $pdo->exec($sql);
    echo "Monthly bookings table created successfully!\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'monthly_bookings'");
    if ($stmt->rowCount() > 0) {
        echo "Monthly bookings table exists and is ready to use.\n";
    } else {
        echo "Error: Monthly bookings table was not created.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
