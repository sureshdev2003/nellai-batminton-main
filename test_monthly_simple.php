<?php
require_once 'admin/config.php';

echo "<h1>Simple Monthly Booking Test</h1>\n";

// Test 1: Check if we can calculate start date correctly
echo "<h2>Test 1: Start Date Calculation</h2>\n";
$testMonth = '2025-01';
$year = 2025;
$month = 1;

$startDate = new DateTime($year . '-' . $month . '-01');
echo "Start date for {$testMonth}: " . $startDate->format('Y-m-d') . "<br>\n";

// Test 2: Check monthly availability for a specific time
echo "<h2>Test 2: Monthly Availability for 18:00</h2>\n";
try {
    $stmt = $pdo->prepare("
        SELECT 
            s.court_number,
            s.max_members,
            s.available_members,
            COALESCE(SUM(
                CASE 
                    WHEN b.booking_type = 'monthly' THEN b.members_count
                    ELSE 0 
                END
            ), 0) as monthly_booked_members
        FROM slots s
        LEFT JOIN bookings b ON s.court_number = b.court_slot 
            AND s.time_slot = b.time_slot 
            AND b.status IN ('pending', 'approved')
            AND b.booking_type = 'monthly'
            AND (
                DATE_FORMAT(STR_TO_DATE(?, '%Y-%m'), '%Y-%m') >= b.start_month AND 
                DATE_FORMAT(STR_TO_DATE(?, '%Y-%m'), '%Y-%m') < DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(b.start_month, '-01'), '%Y-%m-%d'), INTERVAL b.duration MONTH), '%Y-%m')
            )
        WHERE s.time_slot = ? AND s.status = 'active'
        GROUP BY s.court_number, s.max_members, s.available_members
        ORDER BY s.court_number
    ");
    
    $stmt->execute([$testMonth, $testMonth, '18:00']);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Monthly availability for {$testMonth} at 18:00:<br>\n";
    foreach ($slots as $slot) {
        $courtNumber = $slot['court_number'];
        $monthlyBookedMembers = (int)$slot['monthly_booked_members'];
        $maxMembers = (int)$slot['max_members'];
        $availableMembers = $maxMembers - $monthlyBookedMembers;
        
        echo "Court {$courtNumber}: {$availableMembers}/{$maxMembers} members available<br>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}

// Test 3: Check existing monthly bookings
echo "<h2>Test 3: Existing Monthly Bookings</h2>\n";
try {
    $stmt = $pdo->query("SELECT * FROM bookings WHERE booking_type = 'monthly' ORDER BY created_at DESC LIMIT 3");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($bookings) > 0) {
        echo "Recent monthly bookings:<br>\n";
        foreach ($bookings as $booking) {
            echo "- ID: {$booking['id']}, Court: {$booking['court_slot']}, Time: {$booking['time_slot']}, Month: {$booking['start_month']}, Duration: {$booking['duration']}<br>\n";
        }
    } else {
        echo "No monthly bookings found<br>\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}

echo "<h2>Test Complete</h2>\n";
echo "<p>The monthly booking system should now:</p>\n";
echo "<ul>\n";
echo "<li>Show proper availability (6/6 members) instead of 'Available: -'</li>\n";
echo "<li>Calculate and store start dates for monthly bookings</li>\n";
echo "<li>Display start dates in admin dashboard</li>\n";
echo "</ul>\n";
?>
