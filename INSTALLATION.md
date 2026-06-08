# TPR Villa Calendar Manager - Installation & Setup Guide

## 📦 Quick Start (5 Minutes)

### Step 1: Install the Plugin
1. Upload the `tpr-villa-calendar-manager` folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Find "Villa Calendar Manager"
4. Click **Activate**

### Step 2: Configure Settings
1. Go to **WordPress Admin → Villa Calendar**
2. **Enable Access Code**: Toggle ON (recommended)
3. **Access Code**: Click "Generate New Code" or create your own
4. **Minimum Nights**: Set to 3 (or your preference)
5. Click **Save Settings**

### Step 3: Create Pages
1. Go to **Pages → Add New**
2. Create two pages:
   - **"Check Availability"** (for visitors)
   - **"Manage Reservations"** (for you)

### Step 4: Add Shortcodes

**Visitor Page ("Check Availability"):**
```
[villa_calendar id="1"]
```

**Manager Page ("Manage Reservations"):**
```
[villa_calendar_manager id="1"]
```

### Step 5: Test It!
1. Visit your **"Check Availability"** page
   - ✓ You should see a green calendar
   - ✓ All dates available by default

2. Visit your **"Manage Reservations"** page
   - ✓ Enter your access code
   - ✓ You should see the management interface

**Done! 🎉**

---

## 🎓 Detailed Setup Guide

### Plugin Configuration

#### Access Control Settings

**What is Access Code?**
- Protects your manager calendar from unauthorized access
- Similar to a password, but simpler
- No need to create WordPress accounts

**When to Enable:**
- ✅ Always recommended for security
- ✅ When sharing the manager page URL
- ✅ For multi-user environments

**When to Disable:**
- ⚠️ Only if page is password-protected elsewhere
- ⚠️ For completely private/internal sites

**Setting Access Code:**
1. Go to **Villa Calendar → Access Control**
2. Toggle **Enable Access Code** to ON
3. Choose one:
   - Click **Generate New Code** for random code
   - Type your own code (letters/numbers only)
4. **Important**: Save this code somewhere safe!

#### Booking Rules

**Minimum Nights:**
- Default: 3 nights
- Range: 1-30 nights
- This enforces how many nights guests must book minimum

**Example:**
- If set to 3 nights
- Selecting Feb 1 as start date
- Feb 2 and Feb 3 become unselectable
- Feb 4 becomes the earliest possible end date
- Result: 3-night minimum reservation

#### Color Customization

**Default Colors:**
- Available: Green (#22c55e)
- Booked: Dark Grey (#374151)
- Selected: Blue (#3b82f6)
- Disabled: Light Grey (#e5e7eb)

**How to Change:**
1. Go to **Villa Calendar → Calendar Colors**
2. Click any color box
3. Choose your color
4. Preview shows instantly
5. Click **Save Settings**

**Color Tips:**
- Use high contrast for visibility
- Available = Bright, inviting color
- Booked = Neutral, unavailable color
- Selected = Action color (blue works well)

---

## 📄 Page Setup Options

### Option 1: Simple Pages (Recommended)

**Create in WordPress Editor:**
1. Add New Page
2. Title: "Check Availability"
3. Add shortcode: `[villa_calendar id="1"]`
4. Publish

### Option 2: With Elementor

**Using Elementor:**
1. Create New Page with Elementor
2. Add **Shortcode Widget**
3. Paste: `[villa_calendar id="1"]`
4. Publish

### Option 3: In Widget Areas

**Add to Sidebar:**
1. Go to **Appearance → Widgets**
2. Add **Shortcode Widget**
3. Paste shortcode
4. Save

### Option 4: In Theme Template

**Add to PHP template:**
```php
<?php echo do_shortcode('[villa_calendar id="1"]'); ?>
```

---

## 👤 Manager Daily Workflow

### Accessing Manager Interface

1. Navigate to your manager page
2. Enter access code (if enabled)
3. Click "Access Calendar"

**Bookmark this page for quick access!**

### Creating a Reservation

**Method 1: Range Selection (Fastest)**
1. Click start date (e.g., March 5)
2. Click end date (e.g., March 15)
3. System auto-selects March 5-15
4. Click "Book Selected Dates"
5. Done! ✓

**Method 2: Manual Selection**
1. Click each date individually
2. Click "Book Selected Dates"

**Important Notes:**
- Must meet minimum nights requirement
- Can't book already-booked dates
- Dates update instantly (no page reload)

### Viewing Reservations

**Reservations Panel (Right Side):**
- Shows all your bookings
- Start date → End date
- Total nights

**Filter Options:**
1. **All Reservations**: Shows everything
2. **Upcoming**: Shows future bookings only
3. **Past**: Shows historical bookings

### Managing Reservations

**Highlight a Reservation:**
1. Click **"Highlight"** button
2. Dates light up on calendar
3. Calendar navigates to that month
4. Great for finding specific bookings!

**Delete a Reservation:**

*Option 1 - From Calendar:*
1. Click any grey (booked) date
2. Confirm deletion
3. Dates turn green (available)

*Option 2 - From List:*
1. Find reservation in panel
2. Click **"Delete"** button
3. Confirm deletion
4. Removed instantly

---

## 🎨 Customization Guide

### Custom CSS Styling

Add to **Appearance → Customize → Additional CSS:**

```css
/* Change calendar background */
.tpr-calendar-grid {
    background: #f0f0f0;
}

/* Change header gradient */
.tpr-calendar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Larger date numbers */
.tpr-date {
    font-size: 18px;
}

/* Rounded corners */
.tpr-calendar-wrapper {
    border-radius: 20px;
}
```

### Custom Color Variables

```css
:root {
    --tpr-color-available: #00d4ff;
    --tpr-color-booked: #1a1a1a;
    --tpr-color-selected: #ff6b6b;
    --tpr-primary: #667eea;
}
```

---

## 🔧 Troubleshooting

### Calendar Not Showing

**Check:**
1. ✓ Plugin is activated
2. ✓ Shortcode is correct
3. ✓ Page is published
4. ✓ Clear browser cache

**Solution:**
- View page source
- Look for `tpr-calendar-wrapper`
- If missing, shortcode isn't rendering

### Access Code Not Working

**Check:**
1. ✓ Code is enabled in settings
2. ✓ Code matches exactly (case-sensitive)
3. ✓ No extra spaces

**Solution:**
- Regenerate code in admin
- Try disabling and re-enabling
- Check browser console for errors

### Dates Not Updating

**Check:**
1. ✓ JavaScript is enabled
2. ✓ No JavaScript errors in console
3. ✓ Page isn't cached

**Solution:**
- Hard refresh (Ctrl+F5)
- Disable caching plugins temporarily
- Check browser console

### Minimum Nights Not Working

**Check:**
1. ✓ Setting is saved correctly
2. ✓ Value is between 1-30
3. ✓ Calendar reloaded after change

**Solution:**
- Clear selection
- Refresh page
- Try new dates

### Colors Not Changing

**Check:**
1. ✓ Settings saved
2. ✓ Valid hex colors
3. ✓ Page cache cleared

**Solution:**
- Hard refresh browser
- Clear site cache
- Check CSS isn't overriding

---

## 🔒 Security Best Practices

### Recommendations

1. **Enable Access Code**
   - Always use for manager interface
   - Change periodically
   - Don't share publicly

2. **Page Protection**
   - Consider password-protecting manager page
   - Use HTTPS (SSL certificate)
   - Limit who knows the URL

3. **Regular Updates**
   - Keep WordPress updated
   - Keep PHP updated
   - Backup regularly

### Access Code Tips

**Good Codes:**
- `TPR-VILLA-2024`
- `MANAGE-CALENDAR`
- `SECURE-ACCESS-42`

**Avoid:**
- Simple codes: `1234`, `password`
- Personal info: birthdays, names
- Dictionary words

---

## 📊 Database Information

### Tables Created

**wp_tpr_reservations**
- Stores reservation records
- Columns: id, villa_id, start_date, end_date, total_nights, status, created_at, updated_at

**wp_tpr_booked_dates**
- Individual date records
- Enables fast date lookups
- Columns: id, villa_id, reservation_id, booked_date

### Data Cleanup

**To Remove All Data:**
1. Deactivate plugin
2. Delete plugin files
3. Run in phpMyAdmin:
```sql
DROP TABLE IF EXISTS wp_tpr_reservations;
DROP TABLE IF EXISTS wp_tpr_booked_dates;
DELETE FROM wp_options WHERE option_name = 'tpr_villa_settings';
```

---

## 💡 Pro Tips

### For Best Performance

1. **Use Caching Wisely**
   - Exclude calendar pages from caching
   - Or set very short cache lifetime

2. **Optimize Images**
   - Keep page lightweight
   - Fast loading = better UX

3. **Mobile First**
   - Test on mobile devices
   - Ensure touch works smoothly

### For Better UX

1. **Clear Instructions**
   - Add text above calendar explaining how to use
   - Example: "Green = Available, Grey = Booked"

2. **Contact Info**
   - Add contact details near calendar
   - Help users with questions

3. **Booking Policy**
   - Display cancellation policy
   - Show check-in/out times

---

## 📞 Support Checklist

Before requesting support, verify:

- [ ] WordPress version 5.0+
- [ ] PHP version 7.4+
- [ ] Plugin is activated
- [ ] Settings are saved
- [ ] Shortcode is correct
- [ ] Browser cache cleared
- [ ] JavaScript enabled
- [ ] No console errors
- [ ] Tested in different browser

---

## 🎯 Quick Reference

### Shortcodes
```
[villa_calendar id="1"]           - Visitor view
[villa_calendar_manager id="1"]   - Manager view
```

### File Locations
```
/wp-content/plugins/tpr-villa-calendar-manager/
```

### Settings Location
```
WordPress Admin → Villa Calendar
```

### Database Tables
```
wp_tpr_reservations
wp_tpr_booked_dates
```

---

**Need help? Double-check this guide first! 📖**
