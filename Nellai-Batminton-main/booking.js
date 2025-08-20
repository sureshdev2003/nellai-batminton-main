let selectedPlan = null;

// Plan Selection
document.querySelectorAll('.plan-option').forEach(option => {
    option.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.plan-option').forEach(opt => opt.classList.remove('selected'));
        // Add selection to clicked option
        this.classList.add('selected');
        selectedPlan = this.dataset.plan;
        // Enable continue button
        document.getElementById('continueBtn').disabled = false;
    });
});

// Continue Button
document.getElementById('continueBtn').addEventListener('click', function() {
    console.log('Continue button clicked, selected plan:', selectedPlan);
    
    if (selectedPlan) {
        const popup = document.getElementById('planPopup');
        const changePlanBtn = document.getElementById('changePlanBtn');
        
        // Hide popup
        if (popup) {
            popup.style.display = 'none';
            popup.classList.add('hidden');
        }
        
        // Show change plan button
        if (changePlanBtn) {
            changePlanBtn.style.display = 'inline-block';
            changePlanBtn.classList.add('show');
        }
        
        // Show appropriate form
        if (selectedPlan === 'daily') {
            document.getElementById('dailyBookingForm').classList.add('active');
            document.getElementById('monthlyBookingForm').classList.remove('active');
        } else {
            document.getElementById('monthlyBookingForm').classList.add('active');
            document.getElementById('dailyBookingForm').classList.remove('active');
        }
    }
});

// Change Plan Button
document.getElementById('changePlanBtn').addEventListener('click', function() {
    console.log('Change plan button clicked');
    
    const popup = document.getElementById('planPopup');
    const changePlanBtn = document.getElementById('changePlanBtn');
    
    // Reset forms
    document.getElementById('dailyBookingForm').classList.remove('active');
    document.getElementById('monthlyBookingForm').classList.remove('active');
    
    if (changePlanBtn) {
        changePlanBtn.style.display = 'none';
        changePlanBtn.classList.remove('show');
    }
    
    // Reset selections and form data
    document.querySelectorAll('.slot-option').forEach(opt => opt.classList.remove('selected'));
    document.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
    document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="date"], input[type="month"], select, textarea').forEach(input => input.value = '');
    
    // Hide payment sections
    document.getElementById('dailyOnlinePaymentSection').style.display = 'none';
    document.getElementById('dailyOfflinePaymentSection').style.display = 'none';
    document.getElementById('monthlyOnlinePaymentSection').style.display = 'none';
    document.getElementById('monthlyOfflinePaymentSection').style.display = 'none';
    
    // Reset file uploads
    resetFileUploads();
    
    // Show popup again
    if (popup) {
        popup.style.display = 'flex';
        popup.classList.remove('hidden');
    }
    
    document.querySelectorAll('.plan-option').forEach(opt => opt.classList.remove('selected'));
    document.getElementById('continueBtn').disabled = true;
    selectedPlan = null;
    
    // Remove selected slot display if it exists
    const slotInfo = document.querySelector('.selected-slot-info');
    if (slotInfo) {
        slotInfo.remove();
    }
});

// Daily Slot Selection
document.querySelectorAll('#dailySlotSelection .slot-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('#dailySlotSelection .slot-option').forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('dailySelectedSlot').value = this.dataset.slot;
    });
});

// Monthly Slot Selection
document.querySelectorAll('#monthlySlotSelection .slot-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('#monthlySlotSelection .slot-option').forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('monthlySelectedSlot').value = this.dataset.slot;
    });
});

// Daily Payment Method Toggle
document.querySelectorAll('#dailyBookingForm input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const onlineSection = document.getElementById('dailyOnlinePaymentSection');
        const offlineSection = document.getElementById('dailyOfflinePaymentSection');
        
        if (this.value === 'online') {
            onlineSection.style.display = 'block';
            offlineSection.style.display = 'none';
        } else {
            onlineSection.style.display = 'none';
            offlineSection.style.display = 'block';
        }
    });
});

// Monthly Payment Method Toggle
document.querySelectorAll('#monthlyBookingForm input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const onlineSection = document.getElementById('monthlyOnlinePaymentSection');
        const offlineSection = document.getElementById('monthlyOfflinePaymentSection');
        
        if (this.value === 'online') {
            onlineSection.style.display = 'block';
            offlineSection.style.display = 'none';
        } else {
            onlineSection.style.display = 'none';
            offlineSection.style.display = 'block';
        }
    });
});

// File Upload Preview Functions
function resetFileUploads() {
    const dailyUpload = document.querySelector('#dailyBookingForm .file-upload');
    const monthlyUpload = document.querySelector('#monthlyBookingForm .file-upload');
    
    if (dailyUpload) {
        dailyUpload.innerHTML = `
            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #666; margin-bottom: 10px;"></i>
            <p>Click to upload payment screenshot</p>
            <p style="font-size: 12px; color: #999;">Supports: JPG, PNG, PDF (Max: 5MB)</p>
        `;
    }
    
    if (monthlyUpload) {
        monthlyUpload.innerHTML = `
            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #666; margin-bottom: 10px;"></i>
            <p>Click to upload payment screenshot</p>
            <p style="font-size: 12px; color: #999;">Supports: JPG, PNG, PDF (Max: 5MB)</p>
        `;
    }
}

// Daily File Upload Preview
document.getElementById('dailyPaymentScreenshot')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileUpload = document.querySelector('#dailyBookingForm .file-upload');
        if (fileUpload) {
            fileUpload.innerHTML = `
                <i class="fas fa-check-circle" style="font-size: 2rem; color: #28a745; margin-bottom: 10px;"></i>
                <p>File selected: ${file.name}</p>
                <p style="font-size: 12px; color: #999;">Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
            `;
        }
    }
});

// Monthly File Upload Preview
document.getElementById('monthlyPaymentScreenshot')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileUpload = document.querySelector('#monthlyBookingForm .file-upload');
        if (fileUpload) {
            fileUpload.innerHTML = `
                <i class="fas fa-check-circle" style="font-size: 2rem; color: #28a745; margin-bottom: 10px;"></i>
                <p>File selected: ${file.name}</p>
                <p style="font-size: 12px; color: #999;">Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
            `;
        }
    }
});

// Form Submissions - WITH DETAILED LOGGING
document.getElementById('dailyBookingForm')?.addEventListener('submit', function(e) {
    console.log('=== FORM SUBMISSION DEBUG ===');
    
    // Log all form data
    const formData = new FormData(this);
    console.log('Form Data:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    // Check specific fields
    const selectedSlot = document.getElementById('dailySelectedSlot').value;
    const timeSlot = document.getElementById('dailySelectedTime').value;
    const selectedDate = document.getElementById('dailySelectedDate').value;
    
    console.log('Hidden field values:');
    console.log(`selectedSlot: "${selectedSlot}"`);
    console.log(`timeSlot: "${timeSlot}"`);
    console.log(`selectedDate: "${selectedDate}"`);
    
    // Validation checks
    if (!selectedSlot) {
        console.log('ERROR: No slot selected');
        e.preventDefault();
        alert('Please select a court slot');
        return;
    }

    if (!timeSlot) {
        console.log('ERROR: No time slot selected');
        e.preventDefault();
        alert('Please select a time slot');
        return;
    }

    // Payment validation
    const paymentMethod = document.querySelector('#dailyBookingForm input[name="payment_method"]:checked');
    console.log(`Payment method: ${paymentMethod ? paymentMethod.value : 'none'}`);
    
    if (paymentMethod && paymentMethod.value === 'online') {
        const screenshot = document.getElementById('dailyPaymentScreenshot').files[0];
        console.log(`Screenshot: ${screenshot ? screenshot.name : 'none'}`);
        if (!screenshot) {
            console.log('ERROR: No payment screenshot');
            e.preventDefault();
            alert('Please upload a payment screenshot for online payment');
            return;
        }
    }
    
    console.log('Form validation passed, submitting...');
});

// Monthly form submission (simplified)
document.getElementById('monthlyBookingForm')?.addEventListener('submit', function(e) {
    const selectedSlot = document.getElementById('monthlySelectedSlot').value;
    if (!selectedSlot) {
        e.preventDefault();
        alert('Please select a court slot');
        return;
    }

    const paymentMethod = document.querySelector('#monthlyBookingForm input[name="payment_method"]:checked');
    if (paymentMethod && paymentMethod.value === 'online') {
        const screenshot = document.getElementById('monthlyPaymentScreenshot').files[0];
        if (!screenshot) {
            e.preventDefault();
            alert('Please upload a payment screenshot for online payment');
            return;
        }
    }
});

// Monthly month/year setup and availability fetch
(function initMonthlyMonthYear() {
    const yearSel = document.getElementById('monthlyYear');
    const monthSel = document.getElementById('monthlyMonthName');
    const hiddenStart = document.getElementById('monthlyStartMonthHidden');
    const durationSel = document.getElementById('monthlyDuration');

    if (!yearSel || !monthSel || !hiddenStart) return;

    const now = new Date();
    const curYear = now.getFullYear();
    const years = [curYear, curYear + 1];
    yearSel.innerHTML = '<option value="">Select Year</option>' + years.map(y => `<option value="${y}">${y}</option>`).join('');

    function updateHiddenAndFetch() {
        const y = yearSel.value;
        const m = monthSel.value;
        if (y && m) {
            hiddenStart.value = `${y}-${m}`;
            if (durationSel && durationSel.value) {
                fetchMonthlyAvailability(hiddenStart.value, durationSel.value);
            }
        } else {
            hiddenStart.value = '';
        }
    }

    monthSel.addEventListener('change', updateHiddenAndFetch);
    yearSel.addEventListener('change', updateHiddenAndFetch);
    if (durationSel) durationSel.addEventListener('change', updateHiddenAndFetch);
})();

async function fetchMonthlyAvailability(startMonth, duration) {
    try {
        const url = `check_monthly_availability.php?start_month=${encodeURIComponent(startMonth)}&duration=${encodeURIComponent(duration)}`;
        const res = await fetch(url);
        const data = await res.json();
        if (!data.success) {
            console.error('Monthly availability error:', data.error);
            return;
        }
        const container = document.getElementById('monthlySlotSelection');
        if (!container) return;
        container.querySelectorAll('.slot-option').forEach(opt => {
            const court = parseInt(opt.getAttribute('data-slot'));
            const info = data.availability && data.availability[court];
            const p = opt.querySelector('.monthly-avail');
            if (info) {
                const avail = info.available;
                const cap = info.capacity;
                if (p) p.textContent = `Available: ${avail}/${cap}`;
                if (avail <= 0) {
                    opt.classList.add('disabled');
                } else {
                    opt.classList.remove('disabled');
                }
            }
        });
    } catch (e) {
        console.error('Fetch monthly availability failed:', e);
    }
}

// Prevent selecting disabled courts
document.getElementById('monthlySlotSelection')?.addEventListener('click', function(e) {
    const opt = e.target.closest('.slot-option');
    if (!opt) return;
    if (opt.classList.contains('disabled')) {
        e.stopPropagation();
        e.preventDefault();
        return;
    }
});

// URL Parameter Functions
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Function to add selected slot display
function addSelectedSlotDisplay(courtParam, timeParam, dateParam) {
    const bookingHeader = document.querySelector('.booking-header');
    if (bookingHeader && !document.querySelector('.selected-slot-info')) {
        const slotInfoDisplay = document.createElement('div');
        slotInfoDisplay.className = 'selected-slot-info';
        slotInfoDisplay.style.cssText = `
            background: #f8f9fa;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        `;
        slotInfoDisplay.innerHTML = `
            <h4 style="margin: 0 0 15px 0; color: #28a745;">
                <i class="fas fa-check-circle"></i> Selected Slot Details
            </h4>
            <div class="slot-details-box" style="background: white; padding: 15px; border-radius: 8px;">
                <p style="margin: 5px 0;"><strong>Court:</strong> ${courtParam}</p>
                <p style="margin: 5px 0;"><strong>Time:</strong> ${timeParam}</p>
                <p style="margin: 5px 0;"><strong>Date:</strong> ${new Date(dateParam).toLocaleDateString('en-US', { 
                    weekday: 'long',
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                })}</p>
                <p style="margin: 5px 0; color: #28a745; font-weight: bold;">✓ Slot Pre-Selected</p>
            </div>
        `;
        bookingHeader.after(slotInfoDisplay);
    }
}

// Function to force show popup (for debugging)
function showPopup() {
    console.log('Forcing popup to show...');
    const popup = document.getElementById('planPopup');
    if (popup) {
        popup.style.display = 'flex';
        popup.classList.remove('hidden');
        popup.style.opacity = '1';
        popup.style.visibility = 'visible';
        popup.style.zIndex = '1000';
        console.log('Popup display forced');
    } else {
        console.log('ERROR: Popup element not found!');
    }
}

// MAIN INITIALIZATION - Handle URL parameters on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PAGE LOAD DEBUG ===');
    
    const courtParam = getUrlParameter('court');
    const timeParam = getUrlParameter('time');
    const dateParam = getUrlParameter('date');

    console.log('URL Parameters:', { courtParam, timeParam, dateParam });

    // Get popup and form elements
    const popup = document.getElementById('planPopup');
    const changePlanBtn = document.getElementById('changePlanBtn');
    const dailyForm = document.getElementById('dailyBookingForm');
    const monthlyForm = document.getElementById('monthlyBookingForm');

    console.log('Elements found:', {
        popup: !!popup,
        changePlanBtn: !!changePlanBtn,
        dailyForm: !!dailyForm,
        monthlyForm: !!monthlyForm
    });

    // Debug popup element if found
    if (popup) {
        console.log('Popup element details:', {
            id: popup.id,
            classes: popup.className,
            display: getComputedStyle(popup).display,
            visibility: getComputedStyle(popup).visibility,
            opacity: getComputedStyle(popup).opacity,
            zIndex: getComputedStyle(popup).zIndex
        });
    }

    // If URL parameters are present, auto-setup daily booking
    if (courtParam && timeParam && dateParam) {
        console.log('Setting up daily booking with URL params');

        // Auto-select daily booking plan and hide popup
        selectedPlan = 'daily';
        if (popup) {
            popup.style.display = 'none';
            popup.classList.add('hidden');
        }
        
        if (changePlanBtn) {
            changePlanBtn.style.display = 'inline-block';
            changePlanBtn.classList.add('show');
        }
        
        if (dailyForm) {
            dailyForm.classList.add('active');
        }
        
        if (monthlyForm) {
            monthlyForm.classList.remove('active');
        }

        // Set hidden fields after a brief delay
        setTimeout(() => {
            const slotField = document.getElementById('dailySelectedSlot');
            const timeField = document.getElementById('dailySelectedTime');
            const dateField = document.getElementById('dailySelectedDate');
            
            console.log('Setting hidden fields...');
            console.log('Elements found:', { 
                slotField: !!slotField, 
                timeField: !!timeField, 
                dateField: !!dateField 
            });

            if (slotField) {
                slotField.value = courtParam;
                console.log(`Set slot: ${slotField.value}`);
            }
            
            if (timeField) {
                timeField.value = timeParam;
                console.log(`Set time: ${timeField.value}`);
            }
            
            if (dateField) {
                dateField.value = dateParam;
                console.log(`Set date: ${dateField.value}`);
            }

            // Double-check values
            console.log('Final hidden field values:');
            console.log(`dailySelectedSlot: "${document.getElementById('dailySelectedSlot')?.value}"`);
            console.log(`dailySelectedTime: "${document.getElementById('dailySelectedTime')?.value}"`);
            console.log(`dailySelectedDate: "${document.getElementById('dailySelectedDate')?.value}"`);
            
        }, 100);

        // Add slot info display
        addSelectedSlotDisplay(courtParam, timeParam, dateParam);

        console.log('Daily booking auto-configuration complete');
    } else {
        console.log('No URL parameters found, showing plan selection popup');
        
        // Make sure popup is visible - ENHANCED VERSION
        if (popup) {
            // Remove any hidden class first
            popup.classList.remove('hidden');
            
            // Force display styles
            popup.style.display = 'flex';
            popup.style.opacity = '1';
            popup.style.visibility = 'visible';
            popup.style.zIndex = '1000';
            
            console.log('Popup forced to show');
            
            // Double-check after a brief delay
            setTimeout(() => {
                const currentDisplay = getComputedStyle(popup).display;
                const currentVisibility = getComputedStyle(popup).visibility;
                const currentOpacity = getComputedStyle(popup).opacity;
                
                console.log('Popup status after force show:', {
                    display: currentDisplay,
                    visibility: currentVisibility,
                    opacity: currentOpacity
                });
                
                if (currentDisplay === 'none' || currentVisibility === 'hidden' || currentOpacity === '0') {
                    console.error('POPUP STILL NOT VISIBLE! Check CSS conflicts.');
                }
            }, 200);
            
        } else {
            console.error('ERROR: Popup element not found! Check your HTML.');
        }
        
        // Hide forms and change plan button initially
        if (dailyForm) dailyForm.classList.remove('active');
        if (monthlyForm) monthlyForm.classList.remove('active');
        if (changePlanBtn) {
            changePlanBtn.style.display = 'none';
            changePlanBtn.classList.remove('show');
        }
    }
});

// Add a window.onload backup in case DOMContentLoaded doesn't work
window.addEventListener('load', function() {
    console.log('Window loaded - backup popup check');
    
    const popup = document.getElementById('planPopup');
    const courtParam = getUrlParameter('court');
    
    // If no URL params and popup exists but isn't visible, force show it
    if (!courtParam && popup) {
        const isVisible = getComputedStyle(popup).display !== 'none' && 
                         getComputedStyle(popup).visibility !== 'hidden';
        
        if (!isVisible) {
            console.log('Backup: Forcing popup to show');
            showPopup();
        }
    }
});

// Expose showPopup function globally for debugging
window.showPopup = showPopup;