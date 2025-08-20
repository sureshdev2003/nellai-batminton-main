<?php
require_once 'admin/config.php';

echo "<h1>Slot Availability Test</h1>";

try {
    // Test 1: Check current slot availability
    echo "<h2>Test 1: Current Slot Availability</h2>";
    $stmt = $pdo->prepare("SELECT * FROM slots WHERE court_number = 1 AND time_slot = '06:00'");
    $stmt->execute();
    $slot = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($slot) {
        echo "<p>Court 1, 06:00 AM - Current available members: {$slot['available_members']}</p>";
    }
    
    // Test 2: Check approved monthly bookings
    echo "<h2>Test 2: Approved Monthly Bookings</h2>";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count, SUM(members_count) as total_members 
        FROM bookings 
        WHERE booking_type = 'monthly' AND status = 'approved'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total approved monthly bookings: {$result['count']}</p>";
    echo "<p>Total members in approved monthly bookings: {$result['total_members']}</p>";
    
    // Test 3: Check pending bookings
    echo "<h2>Test 3: Pending Bookings</h2>";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE status = 'pending'
    ");
    $stmt->execute();
    $pendingCount = $stmt->fetchColumn();
    
    echo "<p>Total pending bookings: {$pendingCount}</p>";
    
    // Test 4: Test slot availability calculation
    echo "<h2>Test 4: Slot Availability Calculation</h2>";
    $date = date('Y-m-d');
    $time = '06:00';
    
    $stmt = $pdo->prepare("
        SELECT 
            s.court_number,
            s.max_members,
            s.available_members as slot_available_members,
            COALESCE(SUM(
                CASE 
                    WHEN b.booking_type = 'daily' AND b.status = 'approved' THEN b.members_count
                    ELSE 0 
                END
            ), 0) as daily_booked_members,
            COALESCE(SUM(
                CASE 
                    WHEN b.booking_type = 'monthly' AND b.status = 'approved' THEN b.members_count
                    ELSE 0 
                END
            ), 0) as monthly_booked_members
        FROM slots s
        LEFT JOIN bookings b ON s.court_number = b.court_slot 
            AND s.time_slot = b.time_slot 
            AND (
                (b.booking_type = 'daily' AND b.start_date = ?) OR
                (b.booking_type = 'monthly' AND 
                 DATE_FORMAT(?, '%Y-%m') >= b.start_month AND 
                 DATE_FORMAT(?, '%Y-%m') < DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(b.start_month, '-01'), '%Y-%m-%d'), INTERVAL b.duration MONTH), '%Y-%m'))
            )
        WHERE s.time_slot = ? AND s.court_number = 1
        GROUP BY s.court_number, s.max_members, s.available_members
    ");
    
    $stmt->execute([$date, $date, $date, $time]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $slotAvailableMembers = (int)$result['slot_available_members'];
        $dailyBookedMembers = (int)$result['daily_booked_members'];
        $monthlyBookedMembers = (int)$result['monthly_booked_members'];
        $finalAvailableMembers = $slotAvailableMembers - $dailyBookedMembers;
        
        echo "<p>Court 1, 06:00 AM for {$date}:</p>";
        echo "<ul>";
        echo "<li>Slot available members: {$slotAvailableMembers}</li>";
        echo "<li>Daily booked members: {$dailyBookedMembers}</li>";
        echo "<li>Monthly booked members: {$monthlyBookedMembers}</li>";
        echo "<li>Final available members: {$finalAvailableMembers}</li>";
        echo "</ul>";
    }
    
    // Test 5: Show recent admin logs
    echo "<h2>Test 5: Recent Admin Activity</h2>";
    $stmt = $pdo->prepare("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($logs) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Admin</th><th>Action</th><th>Booking ID</th><th>Details</th><th>Time</th></tr>";
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td>{$log['admin_username']}</td>";
            echo "<td>{$log['action']}</td>";
            echo "<td>{$log['booking_id']}</td>";
            echo "<td>{$log['details']}</td>";
            echo "<td>{$log['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No admin logs found.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}
h1, h2 {
    color: #333;
}
p, ul {
    background: white;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
}
table {
    background: white;
    margin: 10px 0;
}
th, td {
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f2f2f2;
}
</style>
