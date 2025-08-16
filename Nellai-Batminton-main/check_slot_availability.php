<?php
header('Content-Type: application/json');
require_once 'admin/config.php';

$time = $_GET['time'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

if (empty($time)) {
    echo json_encode(['error' => 'Time parameter is required']);
    exit;
}

try {
    // Get slot availability for all courts at the specified time
    // This includes both daily bookings and monthly bookings that overlap with the date
    $stmt = $pdo->prepare("
        SELECT 
            s.court_number,
            s.max_members,
            s.available_members,
            COALESCE(SUM(
                CASE 
                    WHEN b.booking_type = 'daily' THEN b.members_count
                    ELSE 0 
                END
            ), 0) as daily_booked_members,
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
            AND (
                (b.booking_type = 'daily' AND b.start_date = ?) OR
                (b.booking_type = 'monthly' AND 
                 DATE_FORMAT(?, '%Y-%m') >= b.start_month AND 
                 DATE_FORMAT(?, '%Y-%m') < DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(b.start_month, '-01'), '%Y-%m-%d'), INTERVAL b.duration MONTH), '%Y-%m'))
            )
        WHERE s.time_slot = ? AND s.status = 'active'
        GROUP BY s.court_number, s.max_members, s.available_members
        ORDER BY s.court_number
    ");
    
    $stmt->execute([$date, $date, $date, $time]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
        $dailyBookedMembers = (int)$slot['daily_booked_members'];
        $monthlyBookedMembers = (int)$slot['monthly_booked_members'];
        $totalBookedMembers = $dailyBookedMembers + $monthlyBookedMembers;
        $maxMembers = (int)$slot['max_members'];
        $availableMembers = $maxMembers - $totalBookedMembers;
        
        $availability[$courtNumber] = [
            'court' => $courtNumber,
            'available' => $availableMembers > 0,
            'max_members' => $maxMembers,
            'available_members' => $availableMembers,
            'booked_members' => $totalBookedMembers,
            'daily_booked_members' => $dailyBookedMembers,
            'monthly_booked_members' => $monthlyBookedMembers,
            'status' => $availableMembers > 0 ? "Available ($availableMembers members)" : "Full"
        ];
    }
    
    echo json_encode([
        'success' => true,
        'date' => $date,
        'time' => $time,
        'availability' => $availability
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
