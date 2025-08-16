<?php
require_once 'admin/config.php';

echo "<h2>NBA Database Setup</h2>";

try {
    // Create slots table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS slots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            court_number INT NOT NULL,
            time_slot VARCHAR(10) NOT NULL,
            max_members INT DEFAULT 6,
            available_members INT DEFAULT 6,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_court_time (court_number, time_slot)
        )
    ");
    echo "<p>✅ Slots table created/verified</p>";
    
    // Add columns to bookings table if they don't exist
    try {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN time_slot VARCHAR(10) AFTER court_slot");
        echo "<p>✅ Added time_slot column to bookings table</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ time_slot column already exists</p>";
    }
    
    try {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN members_count INT DEFAULT 1 AFTER time_slot");
        echo "<p>✅ Added members_count column to bookings table</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ members_count column already exists</p>";
    }
    
    // Insert sample slots
    $timeSlots = ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', 
                  '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];
    
    $inserted = 0;
    foreach (range(1, 8) as $court) {
        foreach ($timeSlots as $time) {
            try {
                $stmt = $pdo->prepare("INSERT INTO slots (court_number, time_slot, max_members, available_members) VALUES (?, ?, 6, 6)");
                $stmt->execute([$court, $time]);
                $inserted++;
            } catch (Exception $e) {
                // Slot already exists, skip
            }
        }
    }
    
    echo "<p>✅ Inserted/verified $inserted slot records</p>";
    
    // Test the availability API
    echo "<h3>Testing Availability API:</h3>";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM slots WHERE time_slot = ?");
    $stmt->execute(['10:00']);
    $count = $stmt->fetchColumn();
    echo "<p>Found $count slots for 10:00 AM</p>";
    
    echo "<h3>✅ Database setup complete!</h3>";
    echo "<p><a href='index.php'>Go back to homepage</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
