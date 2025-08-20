// All JavaScript functionality wrapped in DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // Gallery Navigation
    const track = document.querySelector('.slide-track');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    if (track && prevBtn && nextBtn) {
        let currentIndex = 0;
        const slides = track.querySelectorAll('img');
        const slideWidth = slides[0].offsetWidth;
        
        function updateSlidePosition() {
            track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
        }
        
        prevBtn.addEventListener('click', () => {
            currentIndex = Math.max(currentIndex - 1, 0);
            updateSlidePosition();
        });
        
        nextBtn.addEventListener('click', () => {
            currentIndex = Math.min(currentIndex + 1, slides.length - 1);
            updateSlidePosition();
        });
    }

    // Time and Date Selection for Slot Availability
    const timeDropdown = document.getElementById('timeSlot');
    const datePicker = document.getElementById('bookingDate');
    const slotAvailability = document.getElementById('slotAvailability');
    const defaultSlots = document.getElementById('defaultSlots');
    const selectedTimeSpan = document.getElementById('selectedTime');
    const selectedDateSpan = document.getElementById('selectedDate');

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    if (datePicker) {
        datePicker.min = today;
    }

    function updateSlotAvailability() {
        const selectedTime = timeDropdown ? timeDropdown.value : '';
        const selectedDate = datePicker ? datePicker.value : '';
        
        if (!selectedTime || !selectedDate) {
            if (slotAvailability) slotAvailability.style.display = 'none';
            if (defaultSlots) defaultSlots.style.display = 'block';
            return;
        }

        // Update display
        if (slotAvailability) slotAvailability.style.display = 'block';
        if (defaultSlots) defaultSlots.style.display = 'none';
        
        // Format date for display
        const dateObj = new Date(selectedDate);
        const formattedDate = dateObj.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        if (selectedDateSpan) selectedDateSpan.textContent = formattedDate;
        if (selectedTimeSpan) selectedTimeSpan.textContent = selectedTime;

        // Fetch availability from backend
        fetch(`check_slot_availability.php?time=${encodeURIComponent(selectedTime)}&date=${encodeURIComponent(selectedDate)}`)
            .then(response => response.json())
            .then(data => {
                console.log('Availability data:', data);
                
                if (data.success && data.availability) {
                    // Update each court's availability
                    document.querySelectorAll('.slot-booking > div').forEach(slot => {
                        const court = slot.getAttribute('data-court');
                        const courtData = data.availability[court];
                        
                        if (courtData) {
                            const statusElement = slot.querySelector('.availability-status');
                            const memberElement = slot.querySelector('.member-info');
                            const bookButton = slot.querySelector('.btn');
                            
                            if (statusElement) {
                                if (courtData.available_members > 0) {
                                    statusElement.textContent = `Available: ${courtData.available_members} members`;
                                    statusElement.className = 'availability-status court-available';
                                    slot.classList.remove('disabled');
                                    if (bookButton) {
                                        bookButton.href = `booking.php?court=${court}&time=${selectedTime}&date=${selectedDate}`;
                                        bookButton.style.display = 'inline-block';
                                    }
                                } else {
                                    statusElement.textContent = 'Fully Booked';
                                    statusElement.className = 'availability-status court-booked';
                                    slot.classList.add('disabled');
                                    if (bookButton) {
                                        bookButton.style.display = 'none';
                                    }
                                }
                            }
                            
                            if (memberElement) {
                                memberElement.textContent = `Booked: ${courtData.booked_members}/6 members`;
                            }
                        }
                    });
                } else {
                    console.error('Failed to fetch availability:', data.message);
                    fallbackAvailabilityCheck(selectedTime, selectedDate);
                }
            })
            .catch(error => {
                console.error('Error fetching availability:', error);
                fallbackAvailabilityCheck(selectedTime, selectedDate);
            });
    }

    // Fallback availability check (simulated data)
    function fallbackAvailabilityCheck(time, date) {
        console.log('Using fallback availability check for:', time, date);
        
        document.querySelectorAll('.slot-booking > div').forEach(slot => {
            const court = slot.getAttribute('data-court');
            const statusElement = slot.querySelector('.availability-status');
            const memberElement = slot.querySelector('.member-info');
            const bookButton = slot.querySelector('.btn');
            
            // Simulate random availability
            const availableMembers = Math.floor(Math.random() * 7); // 0-6 members
            const bookedMembers = 6 - availableMembers;
            
            if (statusElement) {
                if (availableMembers > 0) {
                    statusElement.textContent = `Available: ${availableMembers} members`;
                    statusElement.className = 'availability-status court-available';
                    slot.classList.remove('disabled');
                    if (bookButton) {
                        bookButton.href = `booking.php?court=${court}&time=${time}&date=${date}`;
                        bookButton.style.display = 'inline-block';
                    }
                } else {
                    statusElement.textContent = 'Fully Booked';
                    statusElement.className = 'availability-status court-booked';
                    slot.classList.add('disabled');
                    if (bookButton) {
                        bookButton.style.display = 'none';
                    }
                }
            }
            
            if (memberElement) {
                memberElement.textContent = `Booked: ${bookedMembers}/6 members`;
            }
        });
    }

    // Event listeners for time and date changes
    if (timeDropdown) {
        timeDropdown.addEventListener('change', updateSlotAvailability);
    }
    
    if (datePicker) {
        datePicker.addEventListener('change', updateSlotAvailability);
    }

    // Initialize with current date if no date is selected
    if (datePicker && !datePicker.value) {
        datePicker.value = today;
    }
  });

