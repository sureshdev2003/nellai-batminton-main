<?php
require_once 'admin/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Debug: Log received data
        error_log("Received POST data: " . print_r($_POST, true));
        
        // Get form data
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $aadhaar = $_POST['aadhaar'];
        $booking_type = $_POST['booking_type'];
        $court_slot = $_POST['selectedSlot'];
        $time_slot = $_POST['time_slot'] ?? null;
        $members_count = $_POST['members_count'] ?? 1;
        $payment_method = $_POST['payment_method'];
        
        // Handle file upload for payment screenshot (only required for online payments)
        $payment_screenshot = null;
        if ($payment_method === 'online') {
            if (isset($_FILES['paymentScreenshot']) && $_FILES['paymentScreenshot']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                $file_type = $_FILES['paymentScreenshot']['type'];
                
                if (in_array($file_type, $allowed_types)) {
                    $file_extension = pathinfo($_FILES['paymentScreenshot']['name'], PATHINFO_EXTENSION);
                    $file_name = 'payment_' . time() . '_' . $court_slot . '.' . $file_extension;
                    $upload_path = 'uploads/' . $file_name;
                    
                    if (move_uploaded_file($_FILES['paymentScreenshot']['tmp_name'], $upload_path)) {
                        $payment_screenshot = $file_name;
                    }
                }
            }
        }
        
        // Prepare data based on booking type
        $start_date = null;
        $start_month = null;
        $duration = null;
        
        if ($booking_type == 'daily') {
            $start_date = $_POST['start_date'] ?? $_POST['date'];
            // For daily bookings, time_slot is required
            if (empty($time_slot)) {
                throw new Exception('Time slot is required for daily bookings');
            }
        } else {
            $start_month = $_POST['startMonth'];
            $duration = $_POST['duration'];
            // For monthly bookings, set time_slot to a default value
            $time_slot = '00:00';
        }
        
        // Insert booking into database
        $stmt = $pdo->prepare("
            INSERT INTO bookings (name, email, phone, aadhaar, booking_type, court_slot, 
                                time_slot, members_count, start_date, start_month, duration, 
                                payment_method, payment_screenshot)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name, $email, $phone, $aadhaar, $booking_type, $court_slot,
            $time_slot, $members_count, $start_date, $start_month, $duration, 
            $payment_method, $payment_screenshot
        ]);
        
        // Send success response
        $response = [
            'success' => true,
            'message' => 'Booking submitted successfully! We will contact you shortly to confirm your booking.',
            'booking_id' => $pdo->lastInsertId()
        ];
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Booking error: " . $e->getMessage());
        
        $response = [
            'success' => false,
            'message' => 'An error occurred while processing your booking. Please try again.',
            'debug' => $e->getMessage() // Remove this in production
        ];
        
        echo json_encode($response);
    }
} else {
    header('Location: booking.php');
    exit();
}
?>
