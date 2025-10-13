# 📁 NCF Repository - Event File Management System

**Ministry of Finance, Planning and Economic Development**  
**Republic of Uganda** 🇺🇬

A professional event file repository system for the Ministry of Finance, allowing administrators to upload files and clients to access them via QR codes with modern corporate branding.

## 🏛️ Official Information

**Organization:** Ministry of Finance, Planning and Economic Development  
**Country:** Republic of Uganda 🇺🇬  
**Purpose:** Professional event file repository system  
**Version:** 1.0.0

## Features

### Admin Features

- 🔐 Simple login (admin/admin)
- 📤 Upload PDF and image files
- 📝 Add descriptions to files
- 📊 View file statistics
- 🗑️ Delete files
- 📋 Manage all uploaded files
- 📱 **QR Code Management**
  - Generate QR codes for events
  - Download QR codes as PNG files
  - Print QR codes for sharing
  - Create new events with fresh QR codes
  - Reactivate previous events
  - View events history

### Client Features

- 📱 QR code access
- 👁️ View files in browser
- ⬇️ Download files
- 📊 Repository statistics
- 📱 Mobile-friendly interface
- 🎯 Dynamic event display

## Installation

1. **Prerequisites:**
   - XAMPP installed and running
   - Apache and MySQL services started

2. **Setup:**
   - Files are already in: `c:\xampp\htdocs\NCF`
   - Open browser and go to: `http://localhost/NCF/setup.php`
   - This will create the database and required tables

3. **Access Points:**
   - **Home:** `http://localhost/NCF/`
   - **Admin:** `http://localhost/NCF/admin/` (login: admin/admin)
   - **QR Manager:** `http://localhost/NCF/admin/qr_manager.php`
   - **Client:** `http://localhost/NCF/client/`

## File Structure

```
NCF/
├── index.php              # Home page
├── setup.php             # Database setup script
├── admin/
│   ├── index.php          # Admin login
│   ├── dashboard.php      # Admin dashboard
│   └── qr_manager.php     # QR code management
├── client/
│   └── index.php          # Client interface with QR code
├── includes/
│   └── config.php         # Database configuration
└── uploads/               # File storage directory
```

## Usage

### For Administrators

1. Go to `http://localhost/NCF/admin/`
2. Login with username: `admin`, password: `admin`
3. Upload PDF or image files
4. **Manage QR Codes:**
   - Go to "QR Manager" from the dashboard
   - Download QR code as PNG file
   - Print QR code for physical distribution
   - Copy URL to clipboard
   - Create new events for different occasions
   - Reactivate previous events when needed

### For Event Management

1. **Creating New Events:**
   - Use QR Manager to create new events
   - Each event gets a unique QR code
   - Previous events are automatically deactivated

2. **QR Code Distribution:**
   - Download high-quality PNG files
   - Print QR codes for posters/handouts
   - Share URLs directly

### For Clients

1. Scan the QR code displayed on the client page
2. Browse available files for the current event
3. View files directly in browser
4. Download files to device

## Security Features

- File type validation (only PDF and images allowed)
- Secure uploads directory with .htaccess protection
- Admin authentication required for uploads
- Unique file naming to prevent conflicts
- Event-based access control

## Technical Details

- **Backend:** PHP with PDO for database operations
- **Database:** MySQL with two main tables (files, events)
- **Frontend:** Responsive HTML/CSS with JavaScript
- **QR Codes:** Generated using QR Server API (300x300 to 400x400 px)
- **File Types:** PDF, JPG, JPEG, PNG, GIF
- **Print Support:** CSS print styles for QR codes

## Database Schema

### Files Table

- id, filename, original_name, file_type, file_size, upload_date, description

### Events Table

- id, event_name, event_code, qr_url, created_date, is_active

## Configuration

### Database Settings (includes/config.php)

- Host: localhost
- Database: ncf_repository
- Username: root
- Password: (empty)

### Admin Credentials

- Username: admin
- Password: admin

## QR Code Features

### Download Options

- **PNG Format:** High-quality images for printing
- **Print Function:** Direct browser printing with optimized layout
- **URL Copying:** One-click URL copying to clipboard

### Event Management

- **Multiple Events:** Create unlimited events
- **Event History:** View all past and current events
- **Reactivation:** Easily switch between events
- **Unique Codes:** Each event gets a unique identifier

## Troubleshooting

1. **Database Connection Issues:**
   - Ensure MySQL is running in XAMPP
   - Run setup.php to create database and events table

2. **File Upload Issues:**
   - Check uploads directory permissions
   - Verify file types are allowed
   - Check PHP upload limits in php.ini

3. **QR Code Not Loading:**
   - Check internet connection (uses external QR API)
   - Verify the client URL is accessible

4. **QR Download Issues:**
   - Ensure JavaScript is enabled
   - Check browser download permissions

## Customization

- Change admin credentials in `admin/index.php`
- Modify allowed file types in `includes/config.php`
- Update QR code size in `generateQRCode()` function
- Customize styling by modifying CSS in respective files
- Add more event fields in database schema

## New in v2.0

- ✅ QR Code download functionality
- ✅ Print-optimized QR codes
- ✅ Event management system
- ✅ Multiple events support
- ✅ Event history and reactivation
- ✅ Dynamic event display
- ✅ Enhanced admin interface

## Support

For any issues or customizations, refer to the code comments or modify the configuration files as needed.

---

**NCF Repository v2.0** - Simple, secure, and user-friendly file management for events with advanced QR code management.
