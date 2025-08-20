<?php
require_once 'admin/config.php';

echo "<h2>Testing Availability API</h2>";

$time = '10:00';
$date = date('Y-m-d');

echo "<p>Time: $time</p>";
echo "<p>Date: $date</p>";

try {
    // Check if slots table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'slots'");
    if ($stmt->rowCount() == 0) {
        echo "<p>❌ Slots table does not exist. Please run setup_database.php first.</p>";
        echo "<p><a href='setup_database.php'>Run Database Setup</a></p>";
        exit;
    }
    
    // Check if we have slots data
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM slots WHERE time_slot = ?");
    $stmt->execute([$time]);
    $slotCount = $stmt->fetchColumn();
    
    echo "<p>Found $slotCount slots for time $time</p>";
    
    if ($slotCount == 0) {
        echo "<p>❌ No slots found for time $time. Please run setup_database.php to create slot data.</p>";
        echo "<p><a href='setup_database.php'>Run Database Setup</a></p>";
        exit;
    }
    
    // Test the availability query directly
    $stmt = $pdo->prepare("
        SELECT 
            s.court_number,
            s.max_members,
            s.available_members,
            COALESCE(SUM(b.members_count), 0) as booked_members
        FROM slots s
        LEFT JOIN bookings b ON s.court_number = b.court_slot 
            AND s.time_slot = b.time_slot 
            AND b.start_date = ? 
            AND b.status IN ('pending', 'approved')
        WHERE s.time_slot = ? AND s.status = 'active'
        GROUP BY s.court_number, s.max_members, s.available_members
        ORDER BY s.court_number
    ");
    
    $stmt->execute([$date, $time]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Raw Database Results:</h3>";
    echo "<pre>" . print_r($slots, true) . "</pre>";
    
    // Create availability array for all 8 courts
    $availability = [];
    for ($i = 1; $i <= 8; $i++) {
        $availability[$i] = [
            'court' => $i,
            'available' => false,
            'max_members' => 6,
            'available_members' => 0,
            'booked_members' => 0,
            'status' => 'Not Available'
        ];
    }
    
    // Update with actual data from database
    foreach ($slots as $slot) {
        $courtNumber = $slot['court_number'];
        $bookedMembers = (int)$slot['booked_members'];
        $maxMembers = (int)$slot['max_members'];
        $availableMembers = $maxMembers - $bookedMembers;
        
        $availability[$courtNumber] = [
            'court' => $courtNumber,
            'available' => $availableMembers > 0,
            'max_members' => $maxMembers,
            'available_members' => $availableMembers,
            'booked_members' => $bookedMembers,
            'status' => $availableMembers > 0 ? "Available ($availableMembers members)" : "Full"
        ];
    }
    
    echo "<h3>Processed Availability Data:</h3>";
    echo "<pre>" . print_r($availability, true) . "</pre>";
    
    echo "<h3>✅ API Logic Working!</h3>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><a href='setup_database.php'>Run Database Setup</a></p>";
}
?>
