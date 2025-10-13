# 🚀 NCF Repository Deployment Guide

**Ministry of Finance, Planning and Economic Development**  
**Republic of Uganda** 🇺🇬

## 📋 Overview

This guide covers how to deploy the NCF Repository to your FTP server using automated GitHub Actions or manual deployment methods.

## 🤖 Automated Deployment with GitHub Actions

### Prerequisites

- GitHub repository with admin access
- FTP server credentials
- Production database setup

### Step 1: Configure GitHub Secrets

1. Go to your GitHub repository
2. Navigate to **Settings** > **Secrets and Variables** > **Actions**
3. Add the following secrets:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `FTP_SERVER` | Your FTP server hostname | `ftp.yourdomain.com` |
| `FTP_USERNAME` | FTP username | `your_ftp_user` |
| `FTP_PASSWORD` | FTP password | `your_ftp_pass` |
| `FTP_SERVER_DIR` | Target directory (optional) | `public_html/` |
| `FTP_PROTOCOL` | Protocol (optional) | `ftp` or `sftp` |
| `FTP_PORT` | Port number (optional) | `21` or `22` |

### Step 2: Trigger Deployment

**Automatic Deployment:**

- Push to `main` or `master` branch
- Create a pull request

**Manual Deployment:**

1. Go to **Actions** tab in your repository
2. Select "Deploy NCF Repository to FTP Server"
3. Click "Run workflow"
4. Choose environment (production/staging)

## 🔧 Manual Deployment

### Windows Users

1. **Run the deployment script:**

   ```cmd
   deploy.bat
   ```

2. **Upload files:**
   - Use FTP client (FileZilla, WinSCP, etc.)
   - Upload contents of `deploy` folder to your server
   - Ensure proper file permissions

### Linux/Mac Users

1. **Make script executable:**

   ```bash
   chmod +x deploy.sh
   ```

2. **Run deployment script:**

   ```bash
   ./deploy.sh
   ```

3. **Upload via FTP:**

   ```bash
   # Using lftp (recommended)
   lftp -u username,password ftp.yourdomain.com
   mirror -R deploy/ /public_html/
   ```

## 🗄️ Database Configuration

### Production Database Settings

- **Host:** `localhost`
- **Database:** `u895763689_ncf`
- **Username:** `u895763689_ncf`
- **Password:** `(Admin@2025)`

### Database Setup Steps

1. Create database on your hosting panel
2. Import database structure (tables will be auto-created)
3. Verify database connection in `includes/config.php`

## 📁 File Structure After Deployment

```
public_html/
├── admin/
│   ├── index.php (login)
│   ├── dashboard.php
│   └── qr_manager.php
├── assets/
│   ├── css/
│   │   └── corporate-style.css
│   └── images/
│       └── logo.png
├── client/
│   └── index.php
├── includes/
│   └── config.php
├── uploads/ (writable)
├── index.php
├── setup.php
└── .htaccess
```

## 🔐 Security Configuration

### File Permissions

```bash
# Directories
find . -type d -exec chmod 755 {} \;

# PHP files
find . -name "*.php" -exec chmod 644 {} \;

# Upload directory (writable)
chmod 755 uploads/

# Config files (protected)
chmod 600 includes/config.php
```

### .htaccess Security Features

- PHP execution prevention in uploads
- Sensitive file protection
- Security headers
- File compression
- Cache optimization

## ✅ Post-Deployment Checklist

### Immediate Verification

- [ ] Website loads correctly
- [ ] Database connection working
- [ ] Admin portal accessible (`/admin/`)
- [ ] File upload functionality working
- [ ] QR code generation working
- [ ] Client interface responsive

### Security Verification

- [ ] Upload directory protected
- [ ] Config files not accessible
- [ ] SSL certificate active
- [ ] Security headers present
- [ ] File permissions correct

### Functionality Testing

- [ ] Admin login (admin/admin)
- [ ] File upload (PDF, images)
- [ ] QR code generation
- [ ] QR code download
- [ ] Client file access
- [ ] Mobile responsiveness

## 🌐 Application URLs

After successful deployment:

- **Homepage:** `https://ncf.miichub.com/`
- **Admin Portal:** `https://ncf.miichub.com/admin/`
- **Client Interface:** `https://ncf.miichub.com/client/`
- **Setup Page:** `https://ncf.miichub.com/setup.php`

## 🎯 Default Admin Credentials

- **Username:** `admin`
- **Password:** `admin`

⚠️ **Important:** Change default credentials after first login!

## 🆘 Troubleshooting

### Common Issues

**Database Connection Error:**

- Verify database credentials in `includes/config.php`
- Ensure database exists on hosting server
- Check database user permissions

**File Upload Issues:**

- Verify `uploads/` directory exists and is writable
- Check PHP upload limits on server
- Ensure `.htaccess` security rules are correct

**QR Code Not Generating:**

- Check internet connectivity on server
- Verify QR API service availability
- Check for firewall blocking external requests

**404 Errors:**

- Verify files uploaded to correct directory
- Check `.htaccess` rewrite rules
- Ensure web server supports URL rewriting

### Server Requirements

- **PHP:** 7.4+ (8.1+ recommended)
- **MySQL:** 5.7+ or MariaDB 10.2+
- **Extensions:** PDO, PDO_MySQL, GD, mbstring
- **Permissions:** File upload and write permissions

## 📞 Support

For deployment issues related to:

- **GitHub Actions:** Check workflow logs
- **FTP Upload:** Verify server credentials
- **Database:** Contact hosting provider
- **Application:** Review error logs

## 🏛️ Official Information

**Organization:** Ministry of Finance, Planning and Economic Development  
**Country:** Republic of Uganda 🇺🇬  
**System:** NCF Repository - Event File Management  
**Version:** 1.0.0  

---

*This deployment guide ensures secure and reliable hosting of the NCF Repository for the Ministry of Finance, Planning and Economic Development, Republic of Uganda.*
