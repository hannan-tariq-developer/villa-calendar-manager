/**
 * TPR Villa Calendar Manager - Frontend Scripts
 */

(function($) {
    'use strict';
    
    /**
     * Base Calendar Class
     */
    class TPRCalendar {
        constructor(options) {
            this.container = $(options.container);
            this.villaId = options.villaId || 1;
            this.mode = options.mode || 'visitor';
            this.minimumNights = options.minimumNights || 3;
            this.currentMonth = options.currentMonth || new Date().getMonth() + 1;
            this.currentYear = options.currentYear || new Date().getFullYear();
            this.bookedDates = [];
            
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.loadCalendar();
        }
        
        bindEvents() {
            const self = this;
            
            // Month navigation
            this.container.on('click', '.tpr-prev-month', function() {
                self.changeMonth(-1);
            });
            
            this.container.on('click', '.tpr-next-month', function() {
                self.changeMonth(1);
            });
        }
        
        changeMonth(direction) {
            this.currentMonth += direction;
            
            if (this.currentMonth < 1) {
                this.currentMonth = 12;
                this.currentYear--;
            } else if (this.currentMonth > 12) {
                this.currentMonth = 1;
                this.currentYear++;
            }
            
            this.loadCalendar();
        }
        
        loadCalendar() {
            this.showLoading();
            
            // Update header
            this.updateHeader();
            
            // Load booked dates
            this.loadBookedDates().then(() => {
                this.renderCalendar();
                this.hideLoading();
            });
        }
        
        loadBookedDates() {
            const self = this;
            
            return $.ajax({
                url: tprVilla.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpr_get_booked_dates',
                    nonce: tprVilla.nonce,
                    villa_id: this.villaId,
                    year: this.currentYear,
                    month: this.currentMonth
                },
                success: function(response) {
                    if (response.success) {
                        self.bookedDates = response.data.booked_dates || [];
                    }
                }
            });
        }
        
        updateHeader() {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
            
            this.container.find('.tpr-month-name').text(monthNames[this.currentMonth - 1]);
            this.container.find('.tpr-year-name').text(this.currentYear);
        }
        
        renderCalendar() {
            const firstDay = new Date(this.currentYear, this.currentMonth - 1, 1);
            const lastDay = new Date(this.currentYear, this.currentMonth, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();
            
            const $container = this.container.find('.tpr-dates-container');
            $container.empty();
            
            // Previous month's trailing dates
            const prevMonth = this.currentMonth === 1 ? 12 : this.currentMonth - 1;
            const prevYear = this.currentMonth === 1 ? this.currentYear - 1 : this.currentYear;
            const daysInPrevMonth = new Date(prevYear, prevMonth, 0).getDate();
            
            for (let i = startingDayOfWeek - 1; i >= 0; i--) {
                const day = daysInPrevMonth - i;
                const $date = this.createDateElement(day, prevMonth, prevYear, true);
                $container.append($date);
            }
            
            // Current month dates
            for (let day = 1; day <= daysInMonth; day++) {
                const $date = this.createDateElement(day, this.currentMonth, this.currentYear, false);
                $container.append($date);
            }
            
            // Next month's leading dates
            const totalCells = $container.children().length;
            const remainingCells = 42 - totalCells; // 6 rows × 7 days
            const nextMonth = this.currentMonth === 12 ? 1 : this.currentMonth + 1;
            const nextYear = this.currentMonth === 12 ? this.currentYear + 1 : this.currentYear;
            
            for (let day = 1; day <= remainingCells; day++) {
                const $date = this.createDateElement(day, nextMonth, nextYear, true);
                $container.append($date);
            }
        }
        
        createDateElement(day, month, year, isOtherMonth) {
            const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isBooked = this.bookedDates.includes(dateStr);
            const isToday = this.isToday(day, month, year);
            const isPast = this.isPastDate(day, month, year);
            
            const $date = $('<div>')
                .addClass('tpr-date')
                .text(day)
                .attr('data-date', dateStr);
            
            if (isOtherMonth) {
                $date.addClass('tpr-other-month');
            } else {
                if (isPast) {
                    // Past dates are disabled
                    $date.addClass('tpr-disabled tpr-past');
                } else if (isBooked) {
                    $date.addClass('tpr-booked');
                } else {
                    $date.addClass('tpr-available');
                }
                
                if (isToday) {
                    $date.addClass('tpr-today');
                }
            }
            
            return $date;
        }
        
        isPastDate(day, month, year) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const checkDate = new Date(year, month - 1, day);
            checkDate.setHours(0, 0, 0, 0);
            
            return checkDate < today;
        }
        
        isToday(day, month, year) {
            const today = new Date();
            return day === today.getDate() && 
                   month === (today.getMonth() + 1) && 
                   year === today.getFullYear();
        }
        
        isPastDate(day, month, year) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const checkDate = new Date(year, month - 1, day);
            checkDate.setHours(0, 0, 0, 0);
            
            return checkDate < today;
        }
        
        showLoading() {
            this.container.find('.tpr-loading-overlay').fadeIn(200);
        }
        
        hideLoading() {
            this.container.find('.tpr-loading-overlay').fadeOut(200);
        }
    }
    
    /**
     * Manager Calendar Class (extends TPRCalendar)
     */
    class TPRManagerCalendar extends TPRCalendar {
        constructor(options) {
            super(options);
            this.accessCodeEnabled = options.accessCodeEnabled || false;
            this.selectedDates = [];
            this.startDate = null;
            this.endDate = null;
            this.reservations = [];
            this.eventsBound = false;
            
            this.initManager();
        }
        
        initManager() {
            console.log('TPR: initManager called');
            console.log('TPR: Access code enabled:', this.accessCodeEnabled);
            console.log('TPR: Is authenticated:', this.isAuthenticated());
            
            // Check if already authenticated in this session
            if (this.isAuthenticated()) {
                console.log('TPR: Already authenticated, showing content');
                // Already authenticated, skip access gate
                const managerContent = document.querySelector('.tpr-manager-content');
                const accessGate = document.getElementById('tprAccessGate');
                
                if (managerContent) managerContent.style.display = 'block';
                if (accessGate) accessGate.style.display = 'none';
                
                this.showManagerContent();
            } else if (this.accessCodeEnabled) {
                console.log('TPR: Not authenticated, showing access gate');
                // Not authenticated, show access gate
                const accessGate = document.getElementById('tprAccessGate');
                const managerContent = document.querySelector('.tpr-manager-content');
                
                if (accessGate) accessGate.style.display = 'flex';
                if (managerContent) managerContent.style.display = 'none';
                
                this.initAccessGate();
            } else {
                console.log('TPR: Access code disabled, showing content');
                // No access code required
                const managerContent = document.querySelector('.tpr-manager-content');
                if (managerContent) managerContent.style.display = 'block';
                
                this.showManagerContent();
            }
        }
        
        isAuthenticated() {
            // Check sessionStorage for authentication
            const sessionKey = 'tpr_manager_authenticated_' + this.villaId;
            const value = sessionStorage.getItem(sessionKey);
            console.log('TPR: isAuthenticated check - Key:', sessionKey, 'Value:', value);
            return value === 'true';
        }
        
        setAuthenticated() {
            const sessionKey = 'tpr_manager_authenticated_' + this.villaId;
            console.log('TPR: Setting authenticated - Key:', sessionKey);
            sessionStorage.setItem(sessionKey, 'true');
            console.log('TPR: Authenticated set. Value:', sessionStorage.getItem(sessionKey));
        }
        
        clearAuthentication() {
            const sessionKey = 'tpr_manager_authenticated_' + this.villaId;
            console.log('TPR: Clearing authentication - Key:', sessionKey);
            sessionStorage.removeItem(sessionKey);
        }
        
        initAccessGate() {
            const self = this;
            const $wrapper = $('.tpr-manager-wrapper');
            
            $wrapper.on('click', '#tprAccessSubmit', function() {
                const code = $('#tprAccessCode').val().trim();
                
                if (!code) {
                    self.showAccessError('Please enter an access code');
                    return;
                }
                
                $.ajax({
                    url: tprVilla.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'tpr_verify_access',
                        nonce: tprVilla.nonce,
                        code: code
                    },
                    success: function(response) {
                        console.log('TPR: Access code verification response:', response);
                        if (response.success) {
                            console.log('TPR: Access code correct, setting authenticated');
                            // Save authentication in session
                            self.setAuthenticated();
                            console.log('TPR: Authentication saved, hiding access gate');
                            
                            $('#tprAccessGate').fadeOut(300, function() {
                                console.log('TPR: Access gate hidden, showing manager content');
                                self.showManagerContent();
                            });
                        } else {
                            console.log('TPR: Access code incorrect');
                            self.showAccessError(response.data.message || tprVilla.strings.invalidCode);
                        }
                    },
                    error: function() {
                        self.showAccessError(tprVilla.strings.errorOccurred);
                    }
                });
            });
            
            // Enter key to submit
            $wrapper.on('keypress', '#tprAccessCode', function(e) {
                if (e.which === 13) {
                    $('#tprAccessSubmit').click();
                }
            });
        }
        
        showAccessError(message) {
            $('.tpr-access-error').text(message).slideDown(200);
        }
        
        showManagerContent() {
            $('.tpr-manager-content').show();
            $('#tprAccessGate').hide();
            
            // Only bind events once
            if (!this.eventsBound) {
                this.bindManagerEvents();
                this.loadReservations();
                this.eventsBound = true;
            }
        }
        
        bindManagerEvents() {
            const self = this;
            
            // Logout button
            $(document).on('click', '#tprLogout', function() {
                if (confirm('Are you sure you want to logout?')) {
                    self.clearAuthentication();
                    location.reload();
                }
            });
            
            // Date selection
            this.container.on('click', '.tpr-date:not(.tpr-other-month):not(.tpr-disabled):not(.tpr-past)', function() {
                const $date = $(this);
                const dateStr = $date.attr('data-date');
                
                if ($date.hasClass('tpr-booked')) {
                    // Toggle booked dates (delete reservation)
                    self.handleBookedDateClick(dateStr);
                } else if ($date.hasClass('tpr-available')) {
                    self.handleAvailableDateClick(dateStr, $date);
                }
            });
            
            // Book selected dates
            $(document).on('click', '#tprBookDates', function() {
                self.bookSelectedDates();
            });
            
            // Clear selection
            $(document).on('click', '#tprClearSelection', function() {
                self.clearSelection();
            });
            
            // Reservation filter
            $(document).on('change', '#tprReservationFilter', function() {
                self.loadReservations();
            });
            
            // Reservation actions
            $(document).on('click', '.tpr-highlight-btn', function() {
                const reservationId = $(this).closest('.tpr-reservation-item').data('id');
                self.highlightReservation(reservationId);
            });
            
            $(document).on('click', '.tpr-delete-btn', function() {
                const reservationId = $(this).closest('.tpr-reservation-item').data('id');
                self.deleteReservation(reservationId);
            });
        }
        
        showTooltip($element, text) {
            // Remove existing tooltip
            $('.tpr-tooltip').remove();
            
            const offset = $element.offset();
            const $tooltip = $('<div class="tpr-tooltip tpr-show">')
                .text(text)
                .css({
                    top: offset.top - 35,
                    left: offset.left + ($element.width() / 2)
                });
            
            $('body').append($tooltip);
            
            // Center tooltip
            $tooltip.css('left', offset.left + ($element.width() / 2) - ($tooltip.width() / 2));
        }
        
        hideTooltip() {
            $('.tpr-tooltip').remove();
        }
        
        handleAvailableDateClick(dateStr, $date) {
            console.log('TPR: Date clicked:', dateStr);
            console.log('TPR: Before - startDate:', this.startDate, 'endDate:', this.endDate);
            
            if (!this.startDate) {
                // First click - set start date
                console.log('TPR: First click - setting start date');
                this.startDate = dateStr;
                this.selectedDates = [dateStr];
                this.updateSelection();
                this.updateSelectionText();
            } else if (!this.endDate) {
                // Second click - check if same or different date
                const startTime = new Date(this.startDate).getTime();
                const clickTime = new Date(dateStr).getTime();
                
                if (clickTime === startTime) {
                    // Same date clicked - keep it selected, user can book just this 1 date
                    console.log('TPR: Same date clicked - keeping selection, ready to book 1 night');
                    // Don't change anything, just keep the selection
                    return;
                } else if (clickTime > startTime) {
                    // Different date clicked after start - set as end date for range
                    console.log('TPR: Second click - after start date, setting end date for range');
                    this.endDate = dateStr;
                    this.selectDateRange(this.startDate, this.endDate);
                } else {
                    // Clicked before start date - reset and use new date as start
                    console.log('TPR: Second click - before start date, resetting');
                    this.startDate = dateStr;
                    this.endDate = null;
                    this.selectedDates = [dateStr];
                }
                
                this.updateSelection();
                this.updateSelectionText();
            } else {
                // Already have start and end - reset and start new selection
                console.log('TPR: Third click - resetting and starting new');
                this.clearSelection();
                this.startDate = dateStr;
                this.selectedDates = [dateStr];
                this.updateSelection();
                this.updateSelectionText();
            }
            
            console.log('TPR: After - startDate:', this.startDate, 'endDate:', this.endDate, 'selectedDates:', this.selectedDates);
        }
        
        selectDateRange(startStr, endStr) {
            this.selectedDates = [];
            const start = new Date(startStr);
            const end = new Date(endStr);
            const current = new Date(start);
            
            // Select dates from start to end (inclusive for display)
            while (current <= end) {
                const dateStr = current.toISOString().split('T')[0];
                this.selectedDates.push(dateStr);
                current.setDate(current.getDate() + 1);
            }
        }
        
        updateSelection() {
            // Remove all selected classes
            this.container.find('.tpr-date').removeClass('tpr-selected');
            
            // Add selected class to selected dates
            this.selectedDates.forEach(dateStr => {
                this.container.find(`.tpr-date[data-date="${dateStr}"]`).addClass('tpr-selected');
            });
            
            // Show/hide action buttons - show even with single date
            if (this.selectedDates.length > 0 || this.startDate) {
                $('#tprBookDates, #tprClearSelection').show();
            } else {
                $('#tprBookDates, #tprClearSelection').hide();
            }
        }
        
        updateSelectionText() {
            let text = 'Click a date to start booking';
            
            if (this.startDate && !this.endDate) {
                // Single date selected - show BOTH options clearly
                text = '✓ ' + this.formatDate(this.startDate) + ' selected (1 night) — ' +
                       'Book now OR click another date for more nights';
            } else if (this.startDate && this.endDate) {
                // Calculate nights correctly: end_date - start_date
                const start = new Date(this.startDate);
                const end = new Date(this.endDate);
                const nights = Math.floor((end - start) / (1000 * 60 * 60 * 24));
                
                text = `✓ ${this.formatDate(this.startDate)} to ${this.formatDate(this.endDate)} (${nights} ${nights === 1 ? 'night' : 'nights'})`;
            }
            
            $('#tprSelectionText').text(text);
        }
        
        clearSelection() {
            this.startDate = null;
            this.endDate = null;
            this.selectedDates = [];
            this.updateSelection();
            this.updateSelectionText();
        }
        
        bookSelectedDates() {
            console.log('TPR: bookSelectedDates called');
            console.log('TPR: startDate =', this.startDate);
            console.log('TPR: endDate =', this.endDate);
            
            if (!this.startDate) {
                console.log('TPR: ERROR - No start date selected!');
                alert('Please select at least one date');
                return;
            }
            
            // If only start date selected, book it as single night
            let endDateToBook = this.endDate;
            if (!endDateToBook) {
                console.log('TPR: No end date, auto-calculating for 1 night...');
                // Single date selected - book it for 1 night
                const start = new Date(this.startDate);
                start.setDate(start.getDate() + 1);
                endDateToBook = start.toISOString().split('T')[0];
                console.log('TPR: Auto end date =', endDateToBook);
            }
            
            console.log('TPR: Booking dates:', this.startDate, 'to', endDateToBook);
            
            const self = this;
            
            $.ajax({
                url: tprVilla.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpr_create_reservation',
                    nonce: tprVilla.nonce,
                    villa_id: this.villaId,
                    start_date: this.startDate,
                    end_date: endDateToBook
                },
                beforeSend: function() {
                    console.log('TPR: Sending booking request...');
                    self.showLoading();
                },
                success: function(response) {
                    console.log('TPR: Booking response:', response);
                    if (response.success) {
                        console.log('TPR: Booking successful!');
                        self.clearSelection();
                        self.loadCalendar();
                        self.loadReservations();
                    } else {
                        console.log('TPR: Booking failed:', response.data.message);
                        alert(response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('TPR: AJAX error:', status, error);
                    console.error('TPR: Response:', xhr.responseText);
                    alert(tprVilla.strings.errorOccurred);
                },
                complete: function() {
                    console.log('TPR: Request complete');
                    self.hideLoading();
                }
            });
        }
        
        handleBookedDateClick(dateStr) {
            // Find reservation for this date and ask to delete
            const reservation = this.reservations.find(r => {
                const start = new Date(r.start_date);
                const end = new Date(r.end_date);
                const current = new Date(dateStr);
                // Include end date in the check
                return current >= start && current <= end;
            });
            
            if (reservation) {
                this.deleteReservation(reservation.id);
            }
        }
        
        loadReservations() {
            const self = this;
            const filter = $('#tprReservationFilter').val() || 'upcoming';
            
            $.ajax({
                url: tprVilla.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpr_get_reservations',
                    nonce: tprVilla.nonce,
                    villa_id: this.villaId,
                    filter: filter
                },
                success: function(response) {
                    if (response.success) {
                        self.reservations = response.data.reservations || [];
                        self.renderReservations();
                    }
                }
            });
        }
        
        renderReservations() {
            const $list = $('#tprReservationsList');
            $list.empty();
            
            if (this.reservations.length === 0) {
                $list.append('<div class="tpr-no-reservations">No reservations found</div>');
                return;
            }
            
            this.reservations.forEach(reservation => {
                const $item = $(`
                    <div class="tpr-reservation-item" data-id="${reservation.id}">
                        <div class="tpr-reservation-dates">
                            ${this.formatDate(reservation.start_date)} → ${this.formatDate(reservation.end_date)}
                        </div>
                        <div class="tpr-reservation-nights">
                            ${reservation.total_nights} ${tprVilla.strings.nights}
                        </div>
                        <div class="tpr-reservation-actions">
                            <button class="tpr-reservation-btn tpr-highlight-btn">Highlight</button>
                            <button class="tpr-reservation-btn tpr-delete-btn">Delete</button>
                        </div>
                    </div>
                `);
                
                $list.append($item);
            });
        }
        
        highlightReservation(reservationId) {
            const reservation = this.reservations.find(r => r.id === reservationId);
            if (!reservation) return;
            
            // Remove previous highlights
            $('.tpr-reservation-item').removeClass('tpr-highlighted');
            this.container.find('.tpr-date').removeClass('tpr-selected');
            
            // Highlight reservation item
            $(`.tpr-reservation-item[data-id="${reservationId}"]`).addClass('tpr-highlighted');
            
            // Highlight dates on calendar (including end date)
            const start = new Date(reservation.start_date);
            const end = new Date(reservation.end_date);
            const current = new Date(start);
            
            while (current <= end) {
                const dateStr = current.toISOString().split('T')[0];
                this.container.find(`.tpr-date[data-date="${dateStr}"]`).addClass('tpr-selected');
                current.setDate(current.getDate() + 1);
            }
            
            // Navigate to reservation month if not visible
            const resMonth = start.getMonth() + 1;
            const resYear = start.getFullYear();
            
            if (this.currentMonth !== resMonth || this.currentYear !== resYear) {
                this.currentMonth = resMonth;
                this.currentYear = resYear;
                this.loadCalendar();
            }
        }
        
        deleteReservation(reservationId) {
            if (!confirm(tprVilla.strings.confirmDelete)) {
                return;
            }
            
            const self = this;
            
            $.ajax({
                url: tprVilla.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tpr_delete_reservation',
                    nonce: tprVilla.nonce,
                    reservation_id: reservationId
                },
                beforeSend: function() {
                    self.showLoading();
                },
                success: function(response) {
                    if (response.success) {
                        self.loadCalendar();
                        self.loadReservations();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(tprVilla.strings.errorOccurred);
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        }
        
        formatDate(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${monthNames[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
        }
    }
    
    // Expose to global scope
    window.TPRCalendar = TPRCalendar;
    window.TPRManagerCalendar = TPRManagerCalendar;
    
})(jQuery);
