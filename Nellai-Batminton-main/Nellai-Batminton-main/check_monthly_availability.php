<?php
header('Content-Type: application/json');
require_once 'admin/config.php';

$startMonth = $_GET['start_month'] ?? $_POST['start_month'] ?? ''; // format: YYYY-MM
$duration = intval($_GET['duration'] ?? $_POST['duration'] ?? 0);   // months
$timeSlot = $_GET['time_slot'] ?? $_POST['time_slot'] ?? ''; // format: HH:MM

if (empty($startMonth) || $duration <= 0 || empty($timeSlot)) {
    echo json_encode(['success' => false, 'error' => 'start_month (YYYY-MM), duration (months), and time_slot (HH:MM) are required']);
    exit;
}

try {
    // Helper to compute overlap between two month ranges
    $parseMonth = function (string $ym): DateTime {
        return DateTime::createFromFormat('Y-m', $ym) ?: new DateTime('1970-01');
    };

    $addMonths = function (DateTime $dt, int $months): DateTime {
        $copy = (clone $dt);
        $copy->modify("first day of this month");
        $copy->modify("+{$months} months"); // exclusive end
        return $copy;
    };

    $selStart = $parseMonth($startMonth);
    $selEnd = $addMonths($selStart, $duration); // exclusive end

    // Get slot availability from the slots table (like daily booking)
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
    
    $stmt->execute([$startMonth, $startMonth, $timeSlot]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create availability array for all 8 courts
    $availability = [];
    for ($i = 1; $i <= 8; $i++) {
        $availability[$i] = [
            'court' => $i,
            'capacity' => 6, // Default capacity
            'available' => 6, // Default available
            'booked' => 0,
            'status' => 'Available'
        ];
    }
    
    // Update with actual data from database
    foreach ($slots as $slot) {
        $courtNumber = $slot['court_number'];
        $monthlyBookedMembers = (int)$slot['monthly_booked_members'];
        $maxMembers = (int)$slot['max_members'];
        $availableMembers = $maxMembers - $monthlyBookedMembers;
        
        $availability[$courtNumber] = [
            'court' => $courtNumber,
            'capacity' => $maxMembers,
            'available' => $availableMembers,
            'booked' => $monthlyBookedMembers,
            'status' => $availableMembers > 0 ? 'Available' : 'Full'
        ];
    }

    echo json_encode([
        'success' => true,
        'start_month' => $startMonth,
        'duration' => $duration,
        'time_slot' => $timeSlot,
        'availability' => $availability,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
