// Album photo deletion
function deleteAlbumPhoto(photoId) {
    if (!confirm('Delete this photo from your album?')) return;
    
    fetch('process_delete_album_photo.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'photo_id=' + photoId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete photo');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error: ' + err);
    });
}

// Lightbox functions
function openLightbox(src) {
    document.getElementById('lightbox').style.display = 'flex';
    document.getElementById('lightbox-img').src = src + '?v=' + Date.now();
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close lightbox with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Role Switch
function openRoleSwitchModal() {
    const currentRole = document.body.dataset.sitterRole || 'boarder';
    const newRole = currentRole === 'house_sitter' ? 'boarder' : 'house_sitter';
    const roleName = newRole === 'house_sitter' ? 'House Sitter' : 'Boarder';
    
    if (confirm(`Switch to ${roleName}?\n\nThis will change your sitter profile settings.`)) {
        fetch('process_switch_sitter_role.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'new_role=' + newRole
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error switching role');
        });
    }
}

// Smooth scroll animations
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.dashboard-card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) ${index * 0.1}s`;
        observer.observe(card);
    });

    // Add pulse animation to icons on scroll
    document.querySelectorAll('.dashboard-card h2 i').forEach((icon, index) => {
        icon.style.opacity = '0';
        icon.style.transform = 'scale(0)';
        icon.style.transition = `all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) ${index * 0.1 + 0.3}s`;
    });

    const iconObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'scale(1)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.dashboard-card h2 i').forEach(icon => {
        iconObserver.observe(icon);
    });

    // Initialize date pickers
    document.querySelectorAll('input[type="date"], input.date-picker').forEach(input => {
        const mode = input.dataset.mode || 'single';
        new ModernDatePicker(input, {
            mode: mode,
            minDate: input.min ? new Date(input.min) : null,
            maxDate: input.max ? new Date(input.max) : null,
            onSelect: (startDate, endDate) => {
                console.log('Date selected:', startDate, endDate);
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
    
    // Initialize availability calendar
    if (document.getElementById('availability-calendar-grid')) {
        initializeAvailabilityCalendar();
        loadAvailabilityDates();
    }
});

/* ============================================
   MODERN CALENDAR DATE PICKER JAVASCRIPT
   ============================================ */

class ModernDatePicker {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            mode: options.mode || 'single', // 'single' or 'range'
            minDate: options.minDate || null,
            maxDate: options.maxDate || null,
            disabledDates: options.disabledDates || [],
            onSelect: options.onSelect || null,
            format: options.format || 'MMM DD, YYYY'
        };
        
        this.currentDate = new Date();
        this.selectedDate = null;
        this.rangeStart = null;
        this.rangeEnd = null;
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        this.createStructure();
        this.attachEvents();
    }
    
    createStructure() {
        // Create wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'date-picker-wrapper';
        
        // Create input field
        this.input = document.createElement('div');
        this.input.className = 'date-input-field';
        this.input.innerHTML = `
            <span class="selected-date-text">Select date${this.options.mode === 'range' ? 's' : ''}</span>
            <i class="fa-solid fa-calendar-days calendar-icon"></i>
        `;
        
        // Create calendar dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'calendar-dropdown';
        
        // Create overlay for mobile
        this.overlay = document.createElement('div');
        this.overlay.className = 'calendar-overlay';
        
        // Replace original element
        this.element.parentNode.insertBefore(this.wrapper, this.element);
        this.wrapper.appendChild(this.input);
        this.wrapper.appendChild(this.dropdown);
        document.body.appendChild(this.overlay);
        this.element.style.display = 'none';
        
        this.renderCalendar();
    }
    
    renderCalendar() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        let html = `
            <div class="calendar-header">
                <h3><i class="fa-solid fa-calendar-days"></i> Select Date${this.options.mode === 'range' ? 's' : ''}</h3>
                <div class="calendar-nav">
                    <button class="btn-prev-month" title="Previous month">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button class="btn-next-month" title="Next month">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        `;
        
        if (this.options.mode === 'range' && this.rangeStart) {
            const endText = this.rangeEnd ? this.formatDate(this.rangeEnd) : 'Select end date';
            html += `
                <div class="calendar-range-display">
                    <div class="range-label">Selected Range</div>
                    <div class="range-dates">
                        ${this.formatDate(this.rangeStart)} → ${endText}
                    </div>
                </div>
            `;
        }
        
        html += `
            <div class="calendar-month-year">
                <select class="calendar-select month-select">
                    ${this.getMonthOptions(month)}
                </select>
                <select class="calendar-select year-select">
                    ${this.getYearOptions(year)}
                </select>
            </div>
            
            <div class="calendar-weekdays">
                <div class="calendar-weekday">Su</div>
                <div class="calendar-weekday">Mo</div>
                <div class="calendar-weekday">Tu</div>
                <div class="calendar-weekday">We</div>
                <div class="calendar-weekday">Th</div>
                <div class="calendar-weekday">Fr</div>
                <div class="calendar-weekday">Sa</div>
            </div>
            
            <div class="calendar-days">
                ${this.generateCalendarDays(year, month)}
            </div>
            
            <div class="calendar-footer">
                <button class="btn-calendar-clear">Clear</button>
                <button class="btn-calendar-today">Today</button>
                <button class="btn-calendar-apply">Apply</button>
            </div>
        `;
        
        this.dropdown.innerHTML = html;
    }
    
    getMonthOptions(currentMonth) {
        const months = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
        return months.map((month, index) => 
            `<option value="${index}" ${index === currentMonth ? 'selected' : ''}>${month}</option>`
        ).join('');
    }
    
    getYearOptions(currentYear) {
        const startYear = currentYear - 5;
        const endYear = currentYear + 5;
        let options = '';
        for (let year = startYear; year <= endYear; year++) {
            options += `<option value="${year}" ${year === currentYear ? 'selected' : ''}>${year}</option>`;
        }
        return options;
    }
    
    generateCalendarDays(year, month) {
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        const today = new Date();
        
        let html = '';
        
        // Previous month days
        for (let i = firstDay - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            html += `<div class="calendar-day other-month" data-date="${year}-${month}-${day}">${day}</div>`;
        }
        
        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateStr = `${year}-${month + 1}-${day}`;
            let classes = ['calendar-day'];
            
            // Check if today
            if (this.isSameDay(date, today)) {
                classes.push('today');
            }
            
            // Check if selected
            if (this.options.mode === 'single' && this.selectedDate && this.isSameDay(date, this.selectedDate)) {
                classes.push('selected');
            }
            
            // Check if in range
            if (this.options.mode === 'range') {
                if (this.rangeStart && this.isSameDay(date, this.rangeStart)) {
                    classes.push('range-start');
                }
                if (this.rangeEnd && this.isSameDay(date, this.rangeEnd)) {
                    classes.push('range-end');
                }
                if (this.rangeStart && this.rangeEnd && date > this.rangeStart && date < this.rangeEnd) {
                    classes.push('in-range');
                }
            }
            
            // Check if disabled
            if (this.isDisabled(date)) {
                classes.push('disabled');
            }
            
            html += `<div class="${classes.join(' ')}" data-date="${dateStr}">${day}</div>`;
        }
        
        // Next month days
        const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
        const nextMonthDays = totalCells - (firstDay + daysInMonth);
        for (let day = 1; day <= nextMonthDays; day++) {
            html += `<div class="calendar-day other-month" data-date="${year}-${month + 2}-${day}">${day}</div>`;
        }
        
        return html;
    }
    
    attachEvents() {
        // Toggle calendar
        this.input.addEventListener('click', () => this.toggle());
        this.overlay.addEventListener('click', () => this.close());
        
        // Delegate events for calendar
        this.dropdown.addEventListener('click', (e) => {
            const target = e.target.closest('[class*="btn-"], .calendar-day, .calendar-select');
            if (!target) return;
            
            if (target.classList.contains('btn-prev-month')) {
                this.changeMonth(-1);
            } else if (target.classList.contains('btn-next-month')) {
                this.changeMonth(1);
            } else if (target.classList.contains('btn-calendar-clear')) {
                this.clear();
            } else if (target.classList.contains('btn-calendar-today')) {
                this.selectToday();
            } else if (target.classList.contains('btn-calendar-apply')) {
                this.apply();
            } else if (target.classList.contains('calendar-day') && !target.classList.contains('disabled') && !target.classList.contains('other-month')) {
                this.selectDate(target);
            }
        });
        
        // Month/Year select
        this.dropdown.addEventListener('change', (e) => {
            if (e.target.classList.contains('month-select')) {
                this.currentDate.setMonth(parseInt(e.target.value));
                this.renderCalendar();
            } else if (e.target.classList.contains('year-select')) {
                this.currentDate.setFullYear(parseInt(e.target.value));
                this.renderCalendar();
            }
        });
    }
    
    selectDate(dayElement) {
        const [year, month, day] = dayElement.dataset.date.split('-').map(Number);
        const selectedDate = new Date(year, month - 1, day);
        
        // Add ripple effect
        dayElement.classList.add('ripple');
        setTimeout(() => dayElement.classList.remove('ripple'), 600);
        
        if (this.options.mode === 'single') {
            this.selectedDate = selectedDate;
            this.renderCalendar();
        } else {
            if (!this.rangeStart || (this.rangeStart && this.rangeEnd)) {
                // Start new range
                this.rangeStart = selectedDate;
                this.rangeEnd = null;
            } else {
                // Complete range
                if (selectedDate < this.rangeStart) {
                    this.rangeEnd = this.rangeStart;
                    this.rangeStart = selectedDate;
                } else {
                    this.rangeEnd = selectedDate;
                }
            }
            this.renderCalendar();
        }
    }
    
    changeMonth(delta) {
        this.currentDate.setMonth(this.currentDate.getMonth() + delta);
        this.renderCalendar();
    }
    
    selectToday() {
        const today = new Date();
        this.currentDate = new Date(today);
        if (this.options.mode === 'single') {
            this.selectedDate = today;
        } else {
            this.rangeStart = today;
            this.rangeEnd = null;
        }
        this.renderCalendar();
    }
    
    clear() {
        this.selectedDate = null;
        this.rangeStart = null;
        this.rangeEnd = null;
        this.input.querySelector('.selected-date-text').textContent = `Select date${this.options.mode === 'range' ? 's' : ''}`;
        this.element.value = '';
        this.renderCalendar();
    }
    
    apply() {
        if (this.options.mode === 'single' && this.selectedDate) {
            const formatted = this.formatDate(this.selectedDate);
            this.input.querySelector('.selected-date-text').textContent = formatted;
            this.element.value = this.selectedDate.toISOString().split('T')[0];
            if (this.options.onSelect) {
                this.options.onSelect(this.selectedDate);
            }
        } else if (this.options.mode === 'range' && this.rangeStart && this.rangeEnd) {
            const formatted = `${this.formatDate(this.rangeStart)} - ${this.formatDate(this.rangeEnd)}`;
            this.input.querySelector('.selected-date-text').textContent = formatted;
            this.element.value = `${this.rangeStart.toISOString().split('T')[0]}|${this.rangeEnd.toISOString().split('T')[0]}`;
            if (this.options.onSelect) {
                this.options.onSelect(this.rangeStart, this.rangeEnd);
            }
        }
        this.close();
    }
    
    toggle() {
        this.isOpen ? this.close() : this.open();
    }
    
    open() {
        this.isOpen = true;
        this.dropdown.classList.add('active');
        this.overlay.classList.add('active');
        this.input.classList.add('active');
    }
    
    close() {
        this.isOpen = false;
        this.dropdown.classList.remove('active');
        this.overlay.classList.remove('active');
        this.input.classList.remove('active');
    }
    
    formatDate(date) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
    }
    
    isSameDay(date1, date2) {
        return date1.getFullYear() === date2.getFullYear() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getDate() === date2.getDate();
    }
    
    isDisabled(date) {
        if (this.options.minDate && date < this.options.minDate) return true;
        if (this.options.maxDate && date > this.options.maxDate) return true;
        return this.options.disabledDates.some(d => this.isSameDay(date, d));
    }
}

// ============================================
// AVAILABILITY CALENDAR FUNCTIONS (GLOBAL SCOPE)
// ============================================

let currentAvailabilityDate = new Date();
let savedAvailabilityDates = [];

function initializeAvailabilityCalendar() {
    renderAvailabilityCalendar();
}

function renderAvailabilityCalendar() {
    const year = currentAvailabilityDate.getFullYear();
    const month = currentAvailabilityDate.getMonth();
    
    // Update month/year display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('current-month-year').textContent = `${monthNames[month]} ${year}`;
    
    // Generate calendar
    const grid = document.getElementById('availability-calendar-grid');
    grid.innerHTML = '';
    
    // Add weekday headers
    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdays.forEach(day => {
        const header = document.createElement('div');
        header.className = 'calendar-grid-header';
        header.textContent = day;
        grid.appendChild(header);
    });
    
    // Get calendar data
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Previous month days
    for (let i = firstDay - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        const dayEl = createCalendarDay(day, year, month - 1, true, today);
        grid.appendChild(dayEl);
    }
    
    // Current month days
    for (let day = 1; day <= daysInMonth; day++) {
        const dayEl = createCalendarDay(day, year, month, false, today);
        grid.appendChild(dayEl);
    }
    
    // Next month days
    const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
    const nextMonthDays = totalCells - (firstDay + daysInMonth);
    for (let day = 1; day <= nextMonthDays; day++) {
        const dayEl = createCalendarDay(day, year, month + 1, true, today);
        grid.appendChild(dayEl);
    }
}

function createCalendarDay(day, year, month, isOtherMonth, today) {
    const dayEl = document.createElement('div');
    dayEl.className = 'calendar-grid-day';
    dayEl.textContent = day;
    
    const date = new Date(year, month, day);
    date.setHours(0, 0, 0, 0);
    const dateStr = date.toISOString().split('T')[0];
    
    if (isOtherMonth) {
        dayEl.classList.add('other-month');
        return dayEl;
    }
    
    // Check if past date
    if (date < today) {
        dayEl.classList.add('past');
        return dayEl;
    }
    
    // Check if today
    if (date.getTime() === today.getTime()) {
        dayEl.classList.add('today');
    }
    
    // Check if available
    if (isDateAvailable(dateStr)) {
        dayEl.classList.add('available');
    }
    
    // Add click handler
    dayEl.onclick = () => selectAvailabilityDate(dateStr, dayEl);
    
    return dayEl;
}

function isDateAvailable(dateStr) {
    return savedAvailabilityDates.some(range => {
        const start = new Date(range.start_date);
        const end = new Date(range.end_date);
        const check = new Date(dateStr);
        return check >= start && check <= end;
    });
}

function selectAvailabilityDate(dateStr, element) {
    // For future enhancement: allow selecting dates directly from calendar
    console.log('Date selected:', dateStr);
}

function changeAvailabilityMonth(delta) {
    currentAvailabilityDate.setMonth(currentAvailabilityDate.getMonth() + delta);
    renderAvailabilityCalendar();
}

function toggleAvailabilityMode() {
    const controls = document.querySelector('.availability-controls');
    const btnText = document.getElementById('availability-btn-text');
    const btnIcon = document.querySelector('.btn-add-small i');
    
    if (controls.style.display === 'none' || controls.style.display === '') {
        controls.style.display = 'block';
        btnText.textContent = 'Cancel';
        if (btnIcon) btnIcon.className = 'fa-solid fa-times';
    } else {
        controls.style.display = 'none';
        btnText.textContent = 'Add Dates';
        if (btnIcon) btnIcon.className = 'fa-solid fa-plus';
        // Reset inputs
        document.getElementById('availability-start').value = '';
        document.getElementById('availability-end').value = '';
    }
}

function cancelAvailability() {
    toggleAvailabilityMode();
}

function saveAvailability() {
    const startDate = document.getElementById('availability-start').value;
    const endDate = document.getElementById('availability-end').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('End date must be after start date');
        return;
    }
    
    // Show loading state
    const saveBtn = document.querySelector('.btn-save-availability');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    // Save to database via AJAX
    fetch('process_save_availability.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `start_date=${startDate}&end_date=${endDate}`
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        
        if (data.success) {
            alert('Availability dates saved successfully!');
            toggleAvailabilityMode();
            loadAvailabilityDates();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        console.error(err);
        alert('Error saving availability');
    });
}

function loadAvailabilityDates() {
    fetch('process_get_availability.php')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            savedAvailabilityDates = data.dates || [];
            renderAvailabilityList();
            renderAvailabilityCalendar();
        }
    })
    .catch(err => console.error('Error loading availability:', err));
}

function renderAvailabilityList() {
    const container = document.getElementById('availability-list-container');
    
    if (!container) return;
    
    if (savedAvailabilityDates.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="padding: 40px 20px;">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p>No availability dates set</p>
                <small>Add your available dates to start receiving bookings</small>
            </div>
        `;
        return;
    }
    
    container.innerHTML = savedAvailabilityDates.map(range => `
        <div class="availability-item">
            <div class="availability-dates">
                <i class="fa-solid fa-calendar-days"></i>
                <span>${formatDateDisplay(range.start_date)}</span>
                <span class="date-separator">→</span>
                <span>${formatDateDisplay(range.end_date)}</span>
            </div>
            <button class="btn-delete-availability" onclick="deleteAvailability(${range.id})" title="Delete">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `).join('');
}

function formatDateDisplay(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                   'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
}

function deleteAvailability(id) {
    if (!confirm('Delete this availability range?')) return;
    
    fetch('process_delete_availability.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Availability deleted successfully!');
            loadAvailabilityDates();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error deleting availability');
    });
}