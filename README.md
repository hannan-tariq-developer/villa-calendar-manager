# villa-calendar-manager
A professional WordPress plugin for villa &amp; vacation property management. Features frontend-based reservation system, AJAX-powered calendar, access code protection, minimum stay rules, custom color schemes &amp; Elementor support. No dashboard login needed for daily management. 🏖️
## 🌟 Features

- **Frontend-Based Management** - Manage reservations without WordPress dashboard access
- **Visitor View** - Clean calendar display for guests to check availability
- **Manager View** - Powerful reservation management interface with access code protection
- **AJAX-Powered** - Smooth, no-reload experience
- **Minimum Stay Rules** - Enforce minimum night requirements
- **Range Selection** - Select multiple dates with single clicks
- **Reservations Panel** - View, filter, and manage all bookings
- **Fully Responsive** - Works perfectly on desktop, tablet, and mobile
- **Customizable Colors** - Match your brand with custom color schemes
- **Elementor Compatible** - Use shortcodes in Elementor and page builders

## 📦 Installation

1. Upload the `tpr-villa-calendar-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Villa Calendar** in the admin menu to configure settings

## 🎯 Usage

### Basic Setup

1. **Configure Settings**
   - Go to **WordPress Admin → Villa Calendar**
   - Set your access code (optional but recommended)
   - Configure minimum nights (default: 3)
   - Customize calendar colors

2. **Create Pages**
   - Create a page for visitors: "Check Availability"
   - Create a page for manager: "Manage Reservations"

3. **Add Shortcodes**
   - Visitor page: `[villa_calendar id="1"]`
   - Manager page: `[villa_calendar_manager id="1"]`

### Shortcodes

#### Visitor Calendar
```
[villa_calendar id="1"]
```
Displays availability calendar for visitors (view-only)

#### Manager Calendar
```
[villa_calendar_manager id="1"]
```
Displays full management interface with:
- Date selection
- Booking controls
- Reservations list
- Optional access code protection

### For Managers (Daily Use)

1. **Access the Manager Page**
   - Navigate to your manager page
   - Enter access code if enabled
   - No WordPress login needed!

2. **Create a Reservation**
   - Click start date
   - Click end date (auto-selects range)
   - Click "Book Selected Dates"
   - Done! Instant update.

3. **View Reservations**
   - See all reservations in the side panel
   - Filter by: All, Upcoming, Past
   - Click "Highlight" to see dates on calendar
   - Click "Delete" to remove a reservation

4. **Delete a Reservation**
   - Option 1: Click on any booked date
   - Option 2: Use "Delete" button in reservations list

## ⚙️ Admin Settings

### Access Control
- **Enable Access Code**: Protect manager interface
- **Access Code**: Set or generate a secure code

### Booking Rules
- **Minimum Nights**: Set minimum stay requirement (1-30 nights)

### Calendar Colors
- **Available Dates**: Default green (#22c55e)
- **Booked Dates**: Default dark grey (#374151)
- **Selected Dates**: Default blue (#3b82f6)
- **Disabled Dates**: Default light grey (#e5e7eb)

## 🎨 Customization

### Color Scheme
Customize colors in **Villa Calendar → Color Settings**

### CSS Customization
Override styles by adding custom CSS:

```css
:root {
    --tpr-color-available: #your-color;
    --tpr-color-booked: #your-color;
    --tpr-color-selected: #your-color;
}
```

## 📱 Mobile Responsive

The plugin is fully responsive and optimized for:
- Desktop (1024px+)
- Tablet (768px - 1023px)
- Mobile (< 768px)

## 🔒 Security Features

- AJAX nonce verification
- Access code protection (optional)
- Data sanitization
- Input validation
- SQL injection protection
- XSS prevention

## 🗄️ Database Structure

### Tables Created
- `wp_tpr_reservations` - Stores reservation details
- `wp_tpr_booked_dates` - Individual booked dates for quick lookups

### Data Storage
All settings stored in: `wp_options` → `tpr_villa_settings`

## 🚀 Performance

- Lightweight code
- Optimized database queries
- AJAX for instant updates
- Minimal HTTP requests
- Fast page loads

## 🌍 Compatibility

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **MySQL**: 5.6+
- **Browsers**: All modern browsers
- **Page Builders**: Elementor, Gutenberg, Classic Editor

## 📋 Minimum Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Modern web browser with JavaScript enabled

## 🎯 Use Cases

Perfect for:
- Villa rentals
- Vacation properties
- Airbnb management
- Holiday homes
- Beach houses
- Cabin rentals
- Any property booking system

## 🛠️ Support

For issues or questions:
1. Check the settings are configured correctly
2. Clear browser cache
3. Ensure JavaScript is enabled
4. Check browser console for errors

## 📝 Changelog

### Version 1.0.0
- Initial release
- Visitor calendar view
- Manager calendar interface
- Access code protection
- Minimum nights enforcement
- Range date selection
- Reservations management
- Responsive design
- Custom color schemes
- AJAX functionality
- Shortcode support

## 👨‍💻 Development

Built with:
- PHP (WordPress standards)
- JavaScript (ES6+)
- jQuery
- CSS3
- HTML5

### File Structure
```
tpr-villa-calendar-manager/
├── tpr-villa-calendar-manager.php (Main plugin file)
├── includes/
│   ├── class-tpr-database.php
│   ├── class-tpr-settings.php
│   ├── class-tpr-calendar.php
│   ├── class-tpr-ajax.php
│   └── class-tpr-shortcodes.php
├── admin/
│   └── settings-page.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
└── README.md
```

## 📄 License

This plugin is proprietary software developed for villa management.

## 🎉 Credits

Developed by **Hannan Tariq - hannantariq.developer@gmail.com**
- Professional villa calendar management
- Clean, modern design
- User-friendly interface
- Production-ready code

---

**Enjoy managing your villa reservations! 🏖️**
