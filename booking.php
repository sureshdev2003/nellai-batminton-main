<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Slot - Nellai Batmintation Academy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=League+Gothic&family=Oswald:wght@200..700&family=Outfit:wght@100..900&family=Rubik+Wet+Paint&family=Varela&display=swap"
        rel="stylesheet">
    <link rel="shortcut icon" href="./assets/fav-icon.png" type="image/x-icon">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./booking.css">
</head>

<body>
    <!-- Header -->
    <div class="conatiner">

    <!-- Plan Selection Popup -->
    <div class="plan-popup" id="planPopup">
        <div class="plan-popup-content">
            <h2>Choose Your Booking Plan</h2>
            <p>Select the type of booking that suits your needs</p>
            
            <div class="plan-options">
                <div class="plan-option" data-plan="daily">
                    <h3>Daily Booking</h3>
                    <div class="price">₹500</div>
                    <div class="duration">Per Day</div>
                </div>
                <div class="plan-option" data-plan="monthly">
                    <h3>Monthly Booking</h3>
                    <div class="price">₹12,000</div>
                    <div class="duration">Per Month</div>
                </div>
            </div>
            
            <button class="btn" id="continueBtn" disabled>Continue with Selected Plan</button>
        </div>
    </div>

    <!-- Booking Section -->
    <div class="booking-container">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        
        <button class="change-plan-btn" id="changePlanBtn" style="display: none;">
            <i class="fas fa-exchange-alt"></i> Change Plan
        </button>
        
        <div class="booking-header">
            <h1>Book Your Badminton Court</h1>
            <p>Select your preferred slot and complete your booking</p>
        </div>

        <!-- Daily Booking Form -->
        <form class="booking-form" id="dailyBookingForm" action="process_booking.php" method="POST" enctype="multipart/form-data">
            <h3 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #f5c542; padding-bottom: 10px;">
                <i class="fas fa-calendar-day"></i> Daily Booking Form
            </h3>
            
            <!-- Personal Information -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-user"></i> Personal Information
            </h4>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="dailyName">Full Name *</label>
                    <input type="text" id="dailyName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="dailyEmail">Email Address *</label>
                    <input type="email" id="dailyEmail" name="email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dailyPhone">Phone Number *</label>
                    <input type="tel" id="dailyPhone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="dailyAadhaar">Aadhaar Number *</label>
                    <input type="text" id="dailyAadhaar" name="aadhaar" maxlength="12" pattern="[0-9]{12}" required>
                </div>
            </div>

            <!-- Date Display -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-calendar"></i> Booking Date
            </h4>

            <div class="form-group">
                <label>Preferred Date</label>
                <div class="date-display" style="padding: 12px 15px; background: #f8f9fa; border: 2px solid #e1e5e9; border-radius: 8px;">
                    <?php 
                    // Get date from URL parameter or use current date
                    $bookingDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                    echo date('F d, Y', strtotime($bookingDate));
                    ?>
                </div>
                <input type="hidden" name="date" value="<?php echo $bookingDate; ?>">
            </div>
            
            <input type="hidden" name="booking_type" value="daily">


            <input type="hidden" id="dailySelectedSlot" name="selectedSlot" required>
            <input type="hidden" id="dailySelectedTime" name="time_slot" required>
            <input type="hidden" id="dailySelectedDate" name="start_date" required>

            <!-- Member Count Selection -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-users"></i> Number of Members
            </h4>
            
            <div class="form-group">
                <label for="dailyMembersCount">How many members will be playing? *</label>
                <select id="dailyMembersCount" name="members_count" required>
                    <option value="">Select number of members</option>
                    <option value="1">1 Member</option>
                    <option value="2">2 Members</option>
                    <option value="3">3 Members</option>
                    <option value="4">4 Members</option>
                    <option value="5">5 Members</option>
                    <option value="6">6 Members</option>
                </select>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Maximum 6 members per slot. Price remains ₹500 regardless of member count.
                </small>
            </div>

            <!-- Payment Method -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-credit-card"></i> Payment Method
            </h4>
            
            <div class="form-group">
                <label>
                    <input type="radio" name="payment_method" value="online" required>
                    Online Payment (₹500)
                </label>
                <label style="margin-left: 20px;">
                    <input type="radio" name="payment_method" value="offline" required>
                    Offline Payment (₹500)
                </label>
            </div>

            <!-- Online Payment Section -->
            <div id="dailyOnlinePaymentSection" class="payment-section" style="display: none;">
                <h5 style="margin-bottom: 15px; color: #333;">
                    <i class="fas fa-qrcode"></i> Scan QR Code to Pay
                </h5>
                <div class="qr-code">
                    <img src="./assets/qr-code.png" alt="Payment QR Code" id="dailyQrCode">
                    <p style="margin-top: 10px; color: #666;">
                        Scan this QR code with any UPI app to complete your payment
                    </p>
                    <p style="margin-top: 5px; font-weight: 600; color: #333;">
                        Amount: ₹500
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="dailyPaymentScreenshot">Upload Payment Screenshot *</label>
                    <div class="file-upload" onclick="document.getElementById('dailyPaymentScreenshot').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #666; margin-bottom: 10px;"></i>
                        <p>Click to upload payment screenshot</p>
                        <p style="font-size: 12px; color: #999;">Supports: JPG, PNG, PDF (Max: 5MB)</p>
                    </div>
                    <input type="file" id="dailyPaymentScreenshot" name="paymentScreenshot" accept="image/*,.pdf">
                </div>
            </div>

            <!-- Offline Payment Section -->
            <div id="dailyOfflinePaymentSection" class="payment-section" style="display: none;">
                <h5 style="margin-bottom: 15px; color: #333;">
                    <i class="fas fa-money-bill-wave"></i> Offline Payment Details
                </h5>
                <p style="color: #666; line-height: 1.6;">
                    For offline payment, please visit our academy during business hours (9:00 AM - 8:00 PM). 
                    You can pay in cash or by card at our reception desk. Please bring a valid ID proof for verification.
                </p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <p style="margin: 0; color: #333;"><strong>Address:</strong> 123 Main Street, Chennai, India</p>
                    <p style="margin: 5px 0 0 0; color: #333;"><strong>Timings:</strong> 9:00 AM - 8:00 PM (Daily)</p>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-check"></i> Confirm Daily Booking
            </button>
        </form>

        <!-- Monthly Booking Form -->
        <form class="booking-form" id="monthlyBookingForm" action="process_booking.php" method="POST" enctype="multipart/form-data">
            <h3 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #f5c542; padding-bottom: 10px;">
                <i class="fas fa-calendar-alt"></i> Monthly Booking Form
            </h3>
            
            <!-- Personal Information -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-user"></i> Personal Information
            </h4>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="monthlyName">Full Name *</label>
                    <input type="text" id="monthlyName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="monthlyEmail">Email Address *</label>
                    <input type="email" id="monthlyEmail" name="email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="monthlyPhone">Phone Number *</label>
                    <input type="tel" id="monthlyPhone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="monthlyAadhaar">Aadhaar Number *</label>
                    <input type="text" id="monthlyAadhaar" name="aadhaar" maxlength="12" pattern="[0-9]{12}" required>
                </div>
            </div>

            <!-- Month Selection -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-calendar"></i> Select Month
            </h4>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Start Month *</label>
                    <div style="display:flex; gap:10px;">
                        <select id="monthlyMonthName" required>
                            <option value="">Select Month</option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <select id="monthlyYear" required></select>
                    </div>
                    <input type="hidden" id="monthlyStartMonthHidden" name="startMonth" required>
                </div>
                <div class="form-group">
                    <label for="monthlyDuration">Duration *</label>
                    <select id="monthlyDuration" name="duration" required>
                        <option value="">Select Duration</option>
                        <option value="1">1 Month</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                    </select>
                </div>
            </div>
            
            <input type="hidden" name="booking_type" value="monthly">

            <!-- Time Slot Selection -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-clock"></i> Select Your Preferred Time Slot
            </h4>
            
            <div class="form-group">
                <label for="monthlyTimeSlot">Time Slot *</label>
                <select id="monthlyTimeSlot" name="time_slot" required>
                    <option value="">Select Time Slot</option>
                    <option value="06:00">06:00 AM</option>
                    <option value="07:00">07:00 AM</option>
                    <option value="08:00">08:00 AM</option>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="12:00">12:00 PM</option>
                    <option value="13:00">01:00 PM</option>
                    <option value="14:00">02:00 PM</option>
                    <option value="15:00">03:00 PM</option>
                    <option value="16:00">04:00 PM</option>
                    <option value="17:00">05:00 PM</option>
                    <option value="18:00">06:00 PM</option>
                    <option value="19:00">07:00 PM</option>
                    <option value="20:00">08:00 PM</option>
                    <option value="21:00">09:00 PM</option>
                </select>
                <small style="color: #666; display: block; margin-top: 5px;">
                    You will be registered for this time slot for the entire month.
                </small>
            </div>

            <!-- Court Selection -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-map-marker-alt"></i> Select Your Court
            </h4>
            
            <div class="slot-selection" id="monthlySlotSelection">
                <div class="slot-option" data-slot="1">
                    <h4>Court 1</h4>
                    <p class="monthly-avail">Available: 6/6 members</p>
                    <p>₹12000/month</p>
                </div>
                <div class="slot-option" data-slot="2">
                    <h4>Court 2</h4>
                    <p class="monthly-avail">Available: 6/6 members</p>
                    <p>₹12000/month</p>
                </div>
                <div class="slot-option" data-slot="3">
                    <h4>Court 3</h4>
                    <p class="monthly-avail">Available: 6/6 members</p>
                    <p>₹12000/month</p>
                </div>
                <div class="slot-option" data-slot="4">
                    <h4>Court 4</h4>
                    <p class="monthly-avail">Available: 6/6 members</p>
                    <p>₹12000/month</p>
                </div>
                <div class="slot-option" data-slot="5">
                    <h4>Court 5</h4>
                    <p class="monthly-avail">Available: 6/6 members</p>
                    <p>₹12000/month</p>
                </div>
                <div class="slot-option" data-slot="6">
                    <h4>Court 6</h4>
                    <p class="monthly-avail">Available: 6/6 members</p>
                    <p>₹12000/month</p>
                </div>
                <div class="slot-option" data-slot="7">
                    <h4>Court 7</h4>
                    <p class="monthly-avail">Available: 6/6 members</p>
                    <p>₹12000/month</p>
                </div>
                <div class="slot-option" data-slot="8">
                    <h4>Court 8</h4>
                    <p class="monthly-avail">Available: 6/6 members</p>
                    <p>₹12000/month</p>
                </div>
            </div>

            <input type="hidden" id="monthlySelectedSlot" name="selectedSlot" required>
            <input type="hidden" id="monthlyStartDate" name="start_date" required>

            <!-- Payment Method -->
            <h4 style="margin: 20px 0 15px 0; color: #333;">
                <i class="fas fa-credit-card"></i> Payment Method
            </h4>
            
            <div class="form-group">
                <label>
                    <input type="radio" name="payment_method" value="online" required>
                    Online Payment (₹12000)
                </label>
                <label style="margin-left: 20px;">
                    <input type="radio" name="payment_method" value="offline" required>
                    Offline Payment (₹12000)
                </label>
            </div>

            <!-- Online Payment Section -->
            <div id="monthlyOnlinePaymentSection" class="payment-section" style="display: none;">
                <h5 style="margin-bottom: 15px; color: #333;">
                    <i class="fas fa-qrcode"></i> Scan QR Code to Pay
                </h5>
                <div class="qr-code">
                    <img src="./assets/qr-code.png" alt="Payment QR Code" id="monthlyQrCode">
                    <p style="margin-top: 10px; color: #666;">
                        Scan this QR code with any UPI app to complete your payment
                    </p>
                    <p style="margin-top: 5px; font-weight: 600; color: #333;">
                        Amount: ₹12000
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="monthlyPaymentScreenshot">Upload Payment Screenshot *</label>
                    <div class="file-upload" onclick="document.getElementById('monthlyPaymentScreenshot').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #666; margin-bottom: 10px;"></i>
                        <p>Click to upload payment screenshot</p>
                        <p style="font-size: 12px; color: #999;">Supports: JPG, PNG, PDF (Max: 5MB)</p>
                    </div>
                    <input type="file" id="monthlyPaymentScreenshot" name="paymentScreenshot" accept="image/*,.pdf">
                </div>
            </div>

            <!-- Offline Payment Section -->
            <div id="monthlyOfflinePaymentSection" class="payment-section" style="display: none;">
                <h5 style="margin-bottom: 15px; color: #333;">
                    <i class="fas fa-money-bill-wave"></i> Offline Payment Details
                </h5>
                <p style="color: #666; line-height: 1.6;">
                    For offline payment, please visit our academy during business hours (9:00 AM - 8:00 PM). 
                    You can pay in cash or by card at our reception desk. Please bring a valid ID proof for verification.
                </p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <p style="margin: 0; color: #333;"><strong>Address:</strong> 123 Main Street, Chennai, India</p>
                    <p style="margin: 5px 0 0 0; color: #333;"><strong>Timings:</strong> 9:00 AM - 8:00 PM (Daily)</p>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-check"></i> Confirm Monthly Booking
            </button>
        </form>

    </div>

    <script src="booking.js"></script>d
</body>

</html>
