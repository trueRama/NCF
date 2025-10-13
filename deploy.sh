#!/bin/bash

# NCF Repository Manual Deployment Script
# Ministry of Finance, Planning and Economic Development
# Republic of Uganda

echo "🏛️ NCF Repository Deployment Script"
echo "Ministry of Finance, Planning and Economic Development"
echo "Republic of Uganda 🇺🇬"
echo "======================================================"

# Configuration
DEPLOY_DIR="deploy"
BACKUP_DIR="backup-$(date +%Y%m%d-%H%M%S)"

echo "📁 Creating deployment directory..."
mkdir -p $DEPLOY_DIR
mkdir -p backups

echo "🔄 Preparing files for deployment..."

# Copy all files except development files
rsync -av --progress ./ $DEPLOY_DIR/ \
  --exclude '.git' \
  --exclude '.github' \
  --exclude 'node_modules' \
  --exclude '.env.local' \
  --exclude '.env.development' \
  --exclude 'tests' \
  --exclude '*.log' \
  --exclude '.DS_Store' \
  --exclude 'Thumbs.db' \
  --exclude 'deploy' \
  --exclude 'backups'

echo "🔧 Configuring production settings..."

# Ensure production database settings are active
cd $DEPLOY_DIR

# Update config.php for production
sed -i.bak 's|//production|// Production Environment|g' includes/config.php
sed -i 's|//development|// Development Environment (Disabled)|g' includes/config.php

echo "📁 Creating required directories..."
mkdir -p uploads
mkdir -p assets/images

# Create .htaccess for uploads security
cat > uploads/.htaccess << 'EOF'
# Prevent PHP execution in uploads folder
<Files *.php>
    Order Deny,Allow
    Deny from all
</Files>

# Allow only specific file types
<FilesMatch "\.(pdf|jpg|jpeg|png|gif)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOF

echo "🔐 Setting up security configurations..."

# Create main .htaccess if it doesn't exist
if [ ! -f .htaccess ]; then
cat > .htaccess << 'EOF'
# NCF Repository Security Configuration
# Ministry of Finance, Planning and Economic Development

# Enable rewrite engine
RewriteEngine On

# Prevent access to sensitive files
<FilesMatch "\.(env|log|ini)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Protect config files
<Files "config.php">
    Order Deny,Allow
    Deny from all
</Files>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>

# Compress files for better performance
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>
EOF
fi

cd ..

echo "📦 Creating deployment package..."
tar -czf "backups/ncf-repository-$BACKUP_DIR.tar.gz" $DEPLOY_DIR/

echo "✅ Deployment preparation complete!"
echo ""
echo "📋 Next Steps:"
echo "1. Upload the contents of '$DEPLOY_DIR' to your web server"
echo "2. Ensure your database credentials are correct in includes/config.php"
echo "3. Set proper file permissions (755 for directories, 644 for files)"
echo "4. Test the application functionality"
echo ""
echo "🔗 FTP Upload Commands (example):"
echo "ftp your-server.com"
echo "put -r $DEPLOY_DIR/* /"
echo ""
echo "🌐 Application URLs after deployment:"
echo "- Homepage: https://your-domain.com/"
echo "- Admin: https://your-domain.com/admin/"
echo "- Client: https://your-domain.com/client/"
echo ""
echo "🎉 NCF Repository ready for deployment!"
echo "Ministry of Finance, Planning and Economic Development"
echo "Republic of Uganda 🇺🇬"