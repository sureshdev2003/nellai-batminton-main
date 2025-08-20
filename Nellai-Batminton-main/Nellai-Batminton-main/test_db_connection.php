<?php
require_once 'admin/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test database connection
    echo "<p>✅ Database connection successful</p>";
    
    // Check if tables exist
    $tables = ['slots', 'bookings', 'admins'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Table '$table' exists</p>";
            
            // Count records in slots table
            if ($table === 'slots') {
                $count = $pdo->query("SELECT COUNT(*) FROM slots")->fetchColumn();
                echo "<p>📊 Slots table has $count records</p>";
            }
        } else {
            echo "<p>❌ Table '$table' does not exist</p>";
        }
    }
    
    // Test slots query
    echo "<h3>Testing Slots Query:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM slots WHERE time_slot = ? LIMIT 5");
    $stmt->execute(['10:00']);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($slots) > 0) {
        echo "<p>✅ Slots query working - Found " . count($slots) . " slots for 10:00</p>";
        echo "<pre>" . print_r($slots, true) . "</pre>";
    } else {
        echo "<p>❌ No slots found for 10:00</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
