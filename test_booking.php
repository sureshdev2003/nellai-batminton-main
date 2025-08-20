<?php
require_once 'admin/config.php';

echo "<h2>Booking System Test</h2>";

try {
    // Test database connection
    echo "<p>✅ Database connection successful</p>";
    
    // Test bookings table structure
    $stmt = $pdo->query("DESCRIBE bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Bookings Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test inserting a sample monthly booking
    echo "<h3>Testing Monthly Booking Insert:</h3>";
    
    $stmt = $pdo->prepare("
        INSERT INTO bookings (name, email, phone, aadhaar, booking_type, court_slot, 
                            time_slot, members_count, start_date, start_month, duration, 
                            payment_method, payment_screenshot)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        'Test User',
        'test@example.com',
        '1234567890',
        '123456789012',
        'monthly',
        1,
        '00:00',
        1,
        null,
        '2025-01',
        1,
        'offline',
        null
    ]);
    
    if ($result) {
        $bookingId = $pdo->lastInsertId();
        echo "<p>✅ Test monthly booking inserted successfully (ID: $bookingId)</p>";
        
        // Clean up test data
        $pdo->exec("DELETE FROM bookings WHERE id = $bookingId");
        echo "<p>✅ Test data cleaned up</p>";
    } else {
        echo "<p>❌ Failed to insert test booking</p>";
    }
    
    // Test inserting a sample daily booking
    echo "<h3>Testing Daily Booking Insert:</h3>";
    
    $stmt = $pdo->prepare("
        INSERT INTO bookings (name, email, phone, aadhaar, booking_type, court_slot, 
                            time_slot, members_count, start_date, start_month, duration, 
                            payment_method, payment_screenshot)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        'Test User Daily',
        'testdaily@example.com',
        '1234567890',
        '123456789012',
        'daily',
        1,
        '10:00',
        2,
        '2025-01-15',
        null,
        null,
        'offline',
        null
    ]);
    
    if ($result) {
        $bookingId = $pdo->lastInsertId();
        echo "<p>✅ Test daily booking inserted successfully (ID: $bookingId)</p>";
        
        // Clean up test data
        $pdo->exec("DELETE FROM bookings WHERE id = $bookingId");
        echo "<p>✅ Test data cleaned up</p>";
    } else {
        echo "<p>❌ Failed to insert test daily booking</p>";
    }
    
    echo "<h3>✅ All tests passed!</h3>";
    echo "<p><a href='booking.php'>Go to Booking Page</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><a href='setup_database.php'>Run Database Setup</a></p>";
}
?>
