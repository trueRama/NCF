@echo off
REM NCF Repository Manual Deployment Script for Windows
REM Ministry of Finance, Planning and Economic Development
REM Republic of Uganda

echo.
echo ============================================================
echo      NCF Repository Deployment Script for Windows
echo    Ministry of Finance, Planning and Economic Development
echo                    Republic of Uganda
echo ============================================================
echo.

REM Configuration
set DEPLOY_DIR=deploy
set BACKUP_DIR=backup-%date:~-4%-%date:~4,2%-%date:~7,2%-%time:~0,2%-%time:~3,2%-%time:~6,2%
set BACKUP_DIR=%BACKUP_DIR: =0%

echo Creating deployment directory...
if not exist %DEPLOY_DIR% mkdir %DEPLOY_DIR%
if not exist backups mkdir backups

echo.
echo Preparing files for deployment...

REM Copy files (excluding development files)
robocopy . %DEPLOY_DIR% /E /XD .git .github node_modules tests deploy backups /XF *.log .DS_Store Thumbs.db .env.local .env.development

echo.
echo Configuring production settings...

REM Navigate to deploy directory
cd %DEPLOY_DIR%

REM Create required directories
if not exist uploads mkdir uploads
if not exist assets\images mkdir assets\images

REM Create .htaccess for uploads security
echo # Prevent PHP execution in uploads folder > uploads\.htaccess
echo ^<Files *.php^> >> uploads\.htaccess
echo     Order Deny,Allow >> uploads\.htaccess
echo     Deny from all >> uploads\.htaccess
echo ^</Files^> >> uploads\.htaccess
echo. >> uploads\.htaccess
echo # Allow only specific file types >> uploads\.htaccess
echo ^<FilesMatch "\.(pdf|jpg|jpeg|png|gif)$"^> >> uploads\.htaccess
echo     Order Allow,Deny >> uploads\.htaccess
echo     Allow from all >> uploads\.htaccess
echo ^</FilesMatch^> >> uploads\.htaccess

echo.
echo Setting up security configurations...

REM Create main .htaccess if it doesn't exist
if not exist .htaccess (
    echo # NCF Repository Security Configuration > .htaccess
    echo # Ministry of Finance, Planning and Economic Development >> .htaccess
    echo. >> .htaccess
    echo # Enable rewrite engine >> .htaccess
    echo RewriteEngine On >> .htaccess
    echo. >> .htaccess
    echo # Prevent access to sensitive files >> .htaccess
    echo ^<FilesMatch "\.(env|log|ini)$"^> >> .htaccess
    echo     Order Deny,Allow >> .htaccess
    echo     Deny from all >> .htaccess
    echo ^</FilesMatch^> >> .htaccess
    echo. >> .htaccess
    echo # Protect config files >> .htaccess
    echo ^<Files "config.php"^> >> .htaccess
    echo     Order Deny,Allow >> .htaccess
    echo     Deny from all >> .htaccess
    echo ^</Files^> >> .htaccess
)

cd ..

echo.
echo Creating deployment package...
powershell Compress-Archive -Path %DEPLOY_DIR%\* -DestinationPath "backups\ncf-repository-%BACKUP_DIR%.zip" -Force

echo.
echo ============================================================
echo                 DEPLOYMENT PREPARATION COMPLETE!
echo ============================================================
echo.
echo Next Steps:
echo 1. Upload the contents of '%DEPLOY_DIR%' to your web server
echo 2. Ensure your database credentials are correct in includes/config.php
echo 3. Set proper file permissions on your server
echo 4. Test the application functionality
echo.
echo FTP Upload Steps:
echo 1. Connect to your FTP server
echo 2. Navigate to your website's public_html directory
echo 3. Create or navigate to the 'ncf' folder
echo 4. Upload all files from the '%DEPLOY_DIR%' folder to public_html/ncf/
echo.
echo Application URLs after deployment:
echo - Homepage: https://ncf.miichub.com/
echo - Admin Portal: https://ncf.miichub.com/admin/
echo - Client Interface: https://ncf.miichub.com/client/
echo.
echo Database Configuration:
echo - Production DB: u895763689_ncf
echo - Username: u895763689_ncf  
echo - Password: (Admin@2025)
echo.
echo ============================================================
echo           NCF Repository - Republic of Uganda
echo    Ministry of Finance, Planning and Economic Development
echo ============================================================

pause