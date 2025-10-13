# 🔧 NCF Repository Deployment Troubleshooting Guide

**Ministry of Finance, Planning and Economic Development**  
**Republic of Uganda** 🇺🇬

## 🚨 Common Issues and Solutions

### Issue: GitHub Actions Runs Successfully But No Files Uploaded

#### ✅ **Step 1: Verify GitHub Secrets**

Go to your GitHub repository: `https://github.com/trueRama/NCF/settings/secrets/actions`

Ensure these secrets are configured:

| Secret Name | Required | Example Value | Notes |
|-------------|----------|---------------|-------|
| `FTP_SERVER` | ✅ Yes | `miichub.com` | Without protocol (no ftp:// or https://) |
| `FTP_USERNAME` | ✅ Yes | `your_ftp_username` | Your cPanel/FTP username |
| `FTP_PASSWORD` | ✅ Yes | `your_ftp_password` | Your cPanel/FTP password |
| `FTP_PROTOCOL` | ❌ Optional | `ftp` | Leave empty for default FTP |
| `FTP_PORT` | ❌ Optional | `21` | Leave empty for default port 21 |

#### ✅ **Step 2: Check FTP Server Settings**

**Common FTP Server Configurations:**

1. **cPanel Hosting (Most Common)**
   - Server: Your domain name (e.g., `miichub.com`)
   - Username: Your cPanel username
   - Password: Your cPanel password
   - Directory: Files go to `public_html/ncf/`

2. **DirectAdmin/Plesk**
   - Server: Your domain or server IP
   - Username: Your hosting username
   - Password: Your hosting password

3. **VPS/Dedicated Server**
   - Server: Your server IP
   - Username: Usually `root` or custom user
   - Password: Server password

#### ✅ **Step 3: Manual FTP Test**

Test your FTP connection manually:

```bash
# Using command line FTP
ftp miichub.com

# Login with your credentials
# Username: your_username
# Password: your_password

# Navigate to public_html
cd public_html

# Create ncf directory if it doesn't exist
mkdir ncf
cd ncf

# List files to verify you're in the right place
ls -la
```

#### ✅ **Step 4: Check Server Directory Structure**

Your hosting should have this structure:
```
/
├── public_html/          <- Web root directory
│   └── ncf/             <- Your NCF Repository files go here
│       ├── index.php
│       ├── admin/
│       ├── client/
│       └── assets/
└── other_folders/
```

#### ✅ **Step 5: Alternative Deployment Methods**

If GitHub Actions continues to fail, try manual deployment:

**Method 1: Using FileZilla (Windows/Mac/Linux)**
1. Download FileZilla from https://filezilla-project.org/
2. Connect to `miichub.com` with your FTP credentials
3. Navigate to `public_html/ncf/` on the server
4. Run `deploy.bat` (Windows) or `deploy.sh` (Mac/Linux) locally
5. Upload contents of the `deploy` folder

**Method 2: Using cPanel File Manager**
1. Login to your cPanel
2. Open File Manager
3. Navigate to `public_html/`
4. Create `ncf` folder if it doesn't exist
5. Upload a ZIP file of your project and extract

**Method 3: Using WinSCP (Windows)**
1. Download WinSCP from https://winscp.net/
2. Connect using your FTP credentials
3. Navigate to `/public_html/ncf/`
4. Upload your files

## 🔍 **Debug Workflow Issues**

### Check GitHub Actions Logs

1. Go to your repository: `https://github.com/trueRama/NCF`
2. Click on **Actions** tab
3. Click on the latest workflow run
4. Check each step for error messages

### Common Error Messages and Solutions

**"Connection refused" or "Could not connect"**
- ✅ Verify FTP server allows connections
- ✅ Check if firewall is blocking FTP (port 21)
- ✅ Try SFTP instead (port 22)

**"Authentication failed" or "Login incorrect"**
- ✅ Double-check FTP username and password
- ✅ Ensure no extra spaces in GitHub Secrets
- ✅ Try logging in manually with same credentials

**"Directory not found" or "Access denied"**
- ✅ Check if `public_html` directory exists
- ✅ Verify FTP user has write permissions
- ✅ Create `ncf` directory manually if needed

**"Files uploaded but not visible"**
- ✅ Check if files went to correct directory
- ✅ Verify file permissions (should be 644 for files, 755 for directories)
- ✅ Check for .htaccess restrictions

## 📞 **Getting Help**

### Information to Collect for Support

When asking for help, provide:

1. **Hosting Provider**: (e.g., cPanel, Hostinger, etc.)
2. **Error Messages**: From GitHub Actions logs
3. **FTP Settings**: Server, username (no password!)
4. **Directory Structure**: What you see in File Manager
5. **Manual FTP Test Results**: Can you connect manually?

### Contact Points

1. **Hosting Provider Support**: For FTP access issues
2. **GitHub Support**: For GitHub Actions issues
3. **Repository Owner**: For application-specific issues

## 🛠️ **Quick Fix Checklist**

- [ ] GitHub Secrets are configured correctly
- [ ] FTP credentials work in manual FTP client
- [ ] `public_html/ncf/` directory exists and is writable
- [ ] No firewall blocking FTP connections
- [ ] Server accepts FTP protocol (not just SFTP)
- [ ] Sufficient disk space on server
- [ ] No special characters in passwords causing issues

## 🎯 **Success Verification**

After successful deployment, verify:

1. **Visit**: https://ncf.miichub.com/
2. **Admin Portal**: https://ncf.miichub.com/admin/
3. **Client Interface**: https://ncf.miichub.com/client/
4. **Database Setup**: https://ncf.miichub.com/setup.php

---

**Ministry of Finance, Planning and Economic Development**  
**Republic of Uganda** 🇺🇬