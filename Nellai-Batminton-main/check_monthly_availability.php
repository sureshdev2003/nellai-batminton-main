<?php
header('Content-Type: application/json');
require_once 'admin/config.php';

$startMonth = $_GET['start_month'] ?? $_POST['start_month'] ?? ''; // format: YYYY-MM
$duration = intval($_GET['duration'] ?? $_POST['duration'] ?? 0);   // months

if (empty($startMonth) || $duration <= 0) {
    echo json_encode(['success' => false, 'error' => 'start_month (YYYY-MM) and duration (months) are required']);
    exit;
}

try {
    // Capacity per court (based on initial UI text): Court 1..8
    $courtCapacities = [
        1 => 10,
        2 => 8,
        3 => 5,
        4 => 12,
        5 => 7,
        6 => 9,
        7 => 6,
        8 => 11,
    ];

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

    $availability = [];
    for ($court = 1; $court <= 8; $court++) {
        $availability[$court] = [
            'court' => $court,
            'capacity' => $courtCapacities[$court] ?? 8,
            'booked' => 0,
            'available' => 0,
        ];
    }

    // Fetch all monthly bookings for courts in the potentially overlapping window
    // We'll fetch a broader range to minimize logic in SQL, then compute overlap in PHP
    $stmt = $pdo->prepare("SELECT court_slot, start_month, duration FROM bookings WHERE booking_type = 'monthly' AND status IN ('pending','approved')");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $court = intval($row['court_slot']);
        if ($court < 1 || $court > 8) continue;

        $bStart = $parseMonth($row['start_month'] ?? '');
        if (!$bStart) continue;
        $bDuration = intval($row['duration'] ?? 0);
        if ($bDuration <= 0) continue;
        $bEnd = $addMonths($bStart, $bDuration); // exclusive end

        // Overlap check of [selStart, selEnd) and [bStart, bEnd)
        if ($selStart < $bEnd && $bStart < $selEnd) {
            $availability[$court]['booked'] += 1;
        }
    }

    // Compute remaining
    foreach ($availability as $court => $data) {
        $cap = $data['capacity'];
        $booked = $data['booked'];
        $availability[$court]['available'] = max(0, $cap - $booked);
    }

    echo json_encode([
        'success' => true,
        'start_month' => $startMonth,
        'duration' => $duration,
        'availability' => $availability,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
