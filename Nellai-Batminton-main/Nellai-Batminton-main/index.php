<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nellai Batmintation Academy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=League+Gothic&family=Oswald:wght@200..700&family=Outfit:wght@100..900&family=Rubik+Wet+Paint&family=Varela&display=swap"
        rel="stylesheet">
    <link rel="shortcut icon" href="./assets/fav-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>
    <!-- header -->
    <div class="conatiner">
        <header class="header">
            <div class="logo">
                <img src="./assets/badminton.png" class="logo-img">
                <h1>NBA</h1>
            </div>
            <nav class="navbar">
                <!-- Hamburger Menu Icon -->
                <div class="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <!-- <li><a href="booking.php">Slot Booking</a></li> -->
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="admin/login.php">Admin</a></li>
                </ul>
            </nav>
            <!-- Mobile Menu Overlay -->


            <!-- Main Content with Slider Above -->
            <div class="home-section">
                <div class="slider">
                    <div class="slides">
                        <img src="./assets/slot.jpg" alt="Slide 1">
                        <img src="./assets/back-1.jpg" alt="Slide 2">
                        <img src="./assets/back.jpg" alt="Slide 3">
                    </div>
                </div>

                <!-- Text Content Above Slider -->
                <div class="slider-content">
                    <h1>Nellai Badminton Academy</h1>
                    <p>Welcome to the Court of Champions,<br>Where Every Shot Tells a Story!</p>
                    <a href="#contact" class="btn">Contact Us</a>
                </div>
            </div>



        </header>

    </div>
    <!-- About Section -->
    <section id="about" class="about">
            <h2>About Us</h2>
            <div class="left">
            <p>At Nellai Batmintation Academy, we are dedicated to nurturing the next generation of badminton champions.
                Our academy offers top-notch coaching, state-of-the-art facilities, and a supportive community for
                players of all levels.</p>
            <p>Whether you're a beginner looking to learn the basics or an experienced player aiming to refine your
                skills, our expert coaches are here to guide you every step of the way.</p>
            <p>Join us in our mission to promote the sport of badminton and help you achieve your athletic goals.
                Together, we can elevate your game to new heights!</p>
            <button class="btn">Learn More</button>
            </div>
            <div class="right">
                    <img src="./assets/slot.jpg" class="about-img">
            </div>
          

        </section>
    <!-- Slot Booking -->
    <section id="slot" class="slot">
        <h2>Slot Booking</h2>
        
        <!-- Time and Date Selection -->
        <div class="time-selection">
            <div style="display: flex; gap: 20px; justify-content: center; align-items: center; flex-wrap: wrap;">
                <div>
                    <label for="bookingDate">Select Date:</label>
                    <input type="date" id="bookingDate" class="date-picker" min="">
                </div>
                <div>
                    <label for="timeSlot">Select Time:</label>
                    <select id="timeSlot" class="time-dropdown">
                        <option value="">Choose a time slot</option>
                        <option value="06:00">06:00 AM - 07:00 AM</option>
                        <option value="07:00">07:00 AM - 08:00 AM</option>
                        <option value="08:00">08:00 AM - 09:00 AM</option>
                        <option value="09:00">09:00 AM - 10:00 AM</option>
                        <option value="10:00">10:00 AM - 11:00 AM</option>
                        <option value="11:00">11:00 AM - 12:00 PM</option>
                        <option value="12:00">12:00 PM - 01:00 PM</option>
                        <option value="13:00">01:00 PM - 02:00 PM</option>
                        <option value="14:00">02:00 PM - 03:00 PM</option>
                        <option value="15:00">03:00 PM - 04:00 PM</option>
                        <option value="16:00">04:00 PM - 05:00 PM</option>
                        <option value="17:00">05:00 PM - 06:00 PM</option>
                        <option value="18:00">06:00 PM - 07:00 PM</option>
                        <option value="19:00">07:00 PM - 08:00 PM</option>
                        <option value="20:00">08:00 PM - 09:00 PM</option>
                        <option value="21:00">09:00 PM - 10:00 PM</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Slot Availability Display -->
        <div class="slot-availability" id="slotAvailability" style="display: none;">
            <h3>Available Courts for <span id="selectedDate"></span> at <span id="selectedTime"></span></h3>
            <div class="slot-booking">
                <div class="slot-1" data-court="1">
                    <img src="./assets/slot.jpg" alt="Slot 1">
                    <h3>Court-1</h3>
                    <p class="availability-status">Select date and time to check availability</p>
                    <p class="member-info">Max: 6 members per slot</p>
                    <a href="booking.php" class="btn">Book Now</a>
                </div>
                <div class="slot-2" data-court="2">
                    <img src="./assets/slot.jpg" alt="Slot 2">
                    <h3>Court-2</h3>
                    <p class="availability-status">Select date and time to check availability</p>
                    <p class="member-info">Max: 6 members per slot</p>
                    <a href="booking.php" class="btn">Book Now</a>
                </div>
                <div class="slot-3" data-court="3">
                    <img src="./assets/slot.jpg" alt="Slot 3">
                    <h3>Court-3</h3>
                    <p class="availability-status">Select date and time to check availability</p>
                    <p class="member-info">Max: 6 members per slot</p>
                    <a href="booking.php" class="btn">Book Now</a>
                </div>
                <div class="slot-4" data-court="4">
                    <img src="./assets/slot.jpg" alt="Slot 4">
                    <h3>Court-4</h3>
                    <p class="availability-status">Select date and time to check availability</p>
                    <p class="member-info">Max: 6 members per slot</p>
                    <a href="booking.php" class="btn">Book Now</a>
                </div>
                <div class="slot-5" data-court="5">
                    <img src="./assets/slot.jpg" alt="Slot 5">
                    <h3>Court-5</h3>
                    <p class="availability-status">Select date and time to check availability</p>
                    <p class="member-info">Max: 6 members per slot</p>
                    <a href="booking.php" class="btn">Book Now</a>
                </div>
                <div class="slot-6" data-court="6">
                    <img src="./assets/slot.jpg" alt="Slot 6">
                    <h3>Court-6</h3>
                    <p class="availability-status">Select date and time to check availability</p>
                    <p class="member-info">Max: 6 members per slot</p>
                    <a href="booking.php" class="btn">Book Now</a>
                </div>
                <div class="slot-7" data-court="7">
                    <img src="./assets/slot.jpg" alt="Slot 7">
                    <h3>Court-7</h3>
                    <p class="availability-status">Select date and time to check availability</p>
                    <p class="member-info">Max: 6 members per slot</p>
                    <a href="booking.php" class="btn">Book Now</a>
                </div>
                <div class="slot-8" data-court="8">
                    <img src="./assets/slot.jpg" alt="Slot 8">
                    <h3>Court-8</h3>
                    <p class="availability-status">Select date and time to check availability</p>
                    <p class="member-info">Max: 6 members per slot</p>
                    <a href="booking.php" class="btn">Book Now</a>
                </div>
            </div>
        </div>
        
        <!-- Default Slot Display (when no time is selected) -->
        <div class="default-slots" id="defaultSlots">
            <p class="select-time-message">Please select a date and time slot to view court availability</p>
        </div>
    </section>
    <!-- gallery -->
 <section id="gallery" class="gallery">
    <h2>Gallery</h2>
    <div class="gallery-slider">
        <div class="slide-track">
            <img src="./assets/slot.jpg" alt="Gallery 1">
            <img src="./assets/slot.jpg" alt="Gallery 2">
            <img src="./assets/slot.jpg" alt="Gallery 3">
            <img src="./assets/slot.jpg" alt="Gallery 4">
            <img src="./assets/slot.jpg" alt="Gallery 5">
            <img src="./assets/slot.jpg" alt="Gallery 6">
            <img src="./assets/slot.jpg" alt="Gallery 7">
            <img src="./assets/slot.jpg" alt="Gallery 8">
            <!-- duplicate for seamless loop -->
            <img src="./assets/slot.jpg" alt="Gallery 1">
            <img src="./assets/slot.jpg" alt="Gallery 2">
            <img src="./assets/slot.jpg" alt="Gallery 3">
            <img src="./assets/slot.jpg" alt="Gallery 4">
            <img src="./assets/slot.jpg" alt="Gallery 5">
            <img src="./assets/slot.jpg" alt="Gallery 6">
            <img src="./assets/slot.jpg" alt="Gallery 7">
            <img src="./assets/slot.jpg" alt="Gallery 8">
        </div>
    </div>
</section>


    <!-- contact -->
   <section id="contact" class="contact">
    <footer class="footer">
        <div class="footer-container">

            <!-- Map -->
            <div class="footer-map">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3890.123456789!2d80.2707!3d13.0827!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a5263ff45e3d123%3A0x9f8a1abf6cf84b1!2sYour%20Business%20Name!5e0!3m2!1sen!2sin!4v1682410242345!5m2!1sen!2sin"
                    width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy">
                </iframe>
            </div>

            <!-- Quick Links -->
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p>📍 123 Main Street, Chennai, India</p>
                <p>📞 +91 98765 43210</p>
                <p>✉️ info@example.com</p>
            </div>

        </div>

        <!-- Bottom Note -->
        <div class="footer-bottom">
            <p>© 2025 Your Company. All Rights Reserved.</p>
        </div>
    </footer>
</section>


    <script src="script.js"></script>
</body>

</html>