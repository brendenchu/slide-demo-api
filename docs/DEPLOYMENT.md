# Deployment Guide

**Project**: Slide Demo API + Frontend Client
**Date**: January 11, 2026
**Status**: Production Ready

---

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Laravel API Deployment](#laravel-api-deployment)
4. [Frontend Client Deployment](#frontend-client-deployment)
5. [DNS and SSL Configuration](#dns-and-ssl-configuration)
6. [Database Setup](#database-setup)
7. [Environment Configuration](#environment-configuration)
8. [Security Checklist](#security-checklist)
9. [Monitoring and Logging](#monitoring-and-logging)
10. [Post-Deployment Verification](#post-deployment-verification)
11. [Rollback Plan](#rollback-plan)
12. [Troubleshooting](#troubleshooting)

---

## Overview

This guide covers deploying the Slide Demo application, which consists of two parts:

1. **Laravel API Backend** - REST API with token-based authentication
2. **Vue Frontend Client** - Static SPA built with Vite

The deployment supports various hosting options including shared hosting, VPS, cloud platforms, and containerized environments.

---

## Prerequisites

### System Requirements

**Laravel API:**
- PHP 8.2 or higher
- Composer 2.x
- Database: SQLite, MySQL 8.0+, PostgreSQL 14+, or MariaDB 10.3+
- Web server: Nginx or Apache
- SSL certificate (required for token auth)

**Frontend Client:**
- Node.js 18+ (for building)
- Static file hosting (Nginx, Apache, CDN, or static host)

### Third-Party Services (Optional)

- Domain registrar (for custom domain)
- SSL certificate provider (Let's Encrypt recommended)
- CDN (CloudFlare, AWS CloudFront, etc.)
- Monitoring service (New Relic, DataDog, Sentry, etc.)

---

## Laravel API Deployment

### Option 1: Shared Hosting (cPanel, Plesk)

**1. Upload Files**

```bash
# On your local machine
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Upload to server (via FTP/SFTP)
# - All files to: /home/username/laravel-api/
# - Point public_html to: /home/username/laravel-api/public/
```

**2. Set Up Environment**

```bash
# On server
cp .env.example .env
php artisan key:generate

# Edit .env with production values
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

FRONTEND_URL=https://your-domain.com
```

**3. Run Migrations**

```bash
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

**4. Set Permissions**

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Option 2: VPS (DigitalOcean, Linode, Vultr)

**1. Install Dependencies**

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.4 and extensions
sudo apt install -y php8.4-fpm php8.4-cli php8.4-mbstring \
  php8.4-xml php8.4-curl php8.4-mysql php8.4-pgsql \
  php8.4-sqlite3 php8.4-zip php8.4-gd php8.4-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php -- \
  --install-dir=/usr/local/bin --filename=composer

# Install Nginx
sudo apt install -y nginx

# Install MySQL (or PostgreSQL)
sudo apt install -y mysql-server
```

**2. Clone Repository**

```bash
cd /var/www
sudo git clone https://github.com/your-repo/slide-demo-api.git
cd slide-demo-api
sudo chown -R www-data:www-data .
```

**3. Install Dependencies**

```bash
composer install --no-dev --optimize-autoloader
```

**4. Configure Environment**

```bash
cp .env.example .env
php artisan key:generate

# Edit .env with production settings
nano .env
```

**5. Configure Nginx**

Create `/etc/nginx/sites-available/api.your-domain.com`:

```nginx
server {
    listen 80;
    server_name api.your-domain.com;
    root /var/www/slide-demo-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**6. Enable Site**

```bash
sudo ln -s /etc/nginx/sites-available/api.your-domain.com \
  /etc/nginx/sites-enabled/

sudo nginx -t
sudo systemctl reload nginx
```

**7. Run Migrations**

```bash
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

**8. Set Up Process Manager (Optional)**

For queue workers:

```bash
sudo apt install supervisor

# Create /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/slide-demo-api/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/slide-demo-api/storage/logs/worker.log

# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Option 3: Laravel Forge

**1. Create Server**
- Connect your VPS provider (DigitalOcean, Linode, AWS, etc.)
- Select server size and region
- Choose PHP 8.4
- Install database (MySQL or PostgreSQL)

**2. Create Site**
- Add site: `api.your-domain.com`
- Set root directory: `/public`
- Enable "Quick Deploy"

**3. Deploy Repository**
- Connect Git repository
- Set deployment script:

```bash
cd /home/forge/api.your-domain.com

git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**4. Configure Environment**
- Add environment variables via Forge UI
- Set `APP_ENV=production`, `APP_DEBUG=false`

**5. Enable SSL**
- Use Forge's LetsEncrypt integration
- Enable "Force HTTPS"

### Option 4: Docker

**1. Create Dockerfile**

```dockerfile
FROM php:8.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

**2. Create docker-compose.yml**

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "9000:9000"
    volumes:
      - .:/var/www
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    depends_on:
      - db

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./public:/var/www/public
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: slide_demo
      MYSQL_ROOT_PASSWORD: secret
    volumes:
      - dbdata:/var/lib/mysql

volumes:
  dbdata:
```

**3. Deploy**

```bash
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

---

## Frontend Client Deployment

The frontend is a static SPA that can be deployed to any static hosting service.

### Build for Production

```bash
cd vue-spa

# Install dependencies
npm ci

# Build for production
npm run build

# Output: dist/ directory
```

### Option 1: Netlify

**1. Via Netlify UI (Drag & Drop)**

```bash
# After building
# Drag the dist/ folder to Netlify
```

**2. Via Git Integration**

```yaml
# netlify.toml
[build]
  base = "vue-spa"
  command = "npm run build"
  publish = "dist"

[[redirects]]
  from = "/*"
  to = "/index.html"
  status = 200

[build.environment]
  VITE_DATA_SOURCE = "api"
  VITE_API_URL = "https://api.your-domain.com"
```

### Option 2: Vercel

**1. Via Vercel CLI**

```bash
npm i -g vercel
cd vue-spa
vercel --prod
```

**2. Via Git Integration**

```json
// vercel.json
{
  "buildCommand": "npm run build",
  "outputDirectory": "dist",
  "framework": "vite",
  "rewrites": [
    { "source": "/(.*)", "destination": "/index.html" }
  ]
}
```

### Option 3: AWS S3 + CloudFront

**1. Upload to S3**

```bash
aws s3 sync dist/ s3://your-bucket-name/ --delete
```

**2. Configure S3 Bucket**

- Enable "Static website hosting"
- Set index document: `index.html`
- Set error document: `index.html` (for SPA routing)

**3. Create CloudFront Distribution**

- Origin: Your S3 bucket
- Default root object: `index.html`
- Custom error responses: 404 → `/index.html` (200)

### Option 4: Nginx (Self-Hosted)

**1. Upload Files**

```bash
scp -r dist/* user@server:/var/www/your-domain.com/
```

**2. Configure Nginx**

Create `/etc/nginx/sites-available/your-domain.com`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/your-domain.com;

    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
}
```

**3. Enable Site**

```bash
sudo ln -s /etc/nginx/sites-available/your-domain.com \
  /etc/nginx/sites-enabled/

sudo nginx -t
sudo systemctl reload nginx
```

---

## DNS and SSL Configuration

### DNS Records

**For API (api.your-domain.com):**

```
Type: A
Name: api
Value: <your-server-ip>
TTL: 3600
```

**For Frontend (your-domain.com):**

```
Type: A
Name: @
Value: <your-server-ip>  (or CDN IP/CNAME)
TTL: 3600

Type: CNAME
Name: www
Value: your-domain.com
TTL: 3600
```

### SSL Certificates

#### Option 1: Let's Encrypt (Free)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Generate certificate for API
sudo certbot --nginx -d api.your-domain.com

# Generate certificate for Frontend
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal (already configured)
sudo certbot renew --dry-run
```

#### Option 2: CloudFlare (Free with Proxy)

1. Add your domain to CloudFlare
2. Update nameservers at registrar
3. Set SSL/TLS mode to "Full" or "Full (strict)"
4. Enable "Always Use HTTPS"
5. CloudFlare provides free SSL automatically

#### Option 3: Commercial SSL

1. Purchase SSL from provider (Sectigo, DigiCert, etc.)
2. Generate CSR on server:

```bash
openssl req -new -newkey rsa:2048 -nodes \
  -keyout domain.key -out domain.csr
```

3. Upload CSR to provider
4. Download certificate files
5. Install on Nginx:

```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ...
}
```

---

## Database Setup

### MySQL/MariaDB

```bash
# Create database
mysql -u root -p

CREATE DATABASE slide_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'slide_demo_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON slide_demo.* TO 'slide_demo_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Update .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=slide_demo
DB_USERNAME=slide_demo_user
DB_PASSWORD=strong_password_here

# Run migrations
php artisan migrate --force
```

### PostgreSQL

```bash
# Create database
sudo -u postgres psql

CREATE DATABASE slide_demo;
CREATE USER slide_demo_user WITH PASSWORD 'strong_password_here';
GRANT ALL PRIVILEGES ON DATABASE slide_demo TO slide_demo_user;
\q

# Update .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=slide_demo
DB_USERNAME=slide_demo_user
DB_PASSWORD=strong_password_here

# Run migrations
php artisan migrate --force
```

### SQLite (Not Recommended for Production)

```bash
# Create database file
touch database/database.sqlite

# Update .env
DB_CONNECTION=sqlite

# Run migrations
php artisan migrate --force
```

---

## Environment Configuration

### Laravel API (.env)

```bash
# Application
APP_NAME="Slide Demo API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.your-domain.com
APP_TIMEZONE=UTC

# Database (see Database Setup section)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=slide_demo
DB_USERNAME=slide_demo_user
DB_PASSWORD=strong_password_here

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# CORS - Frontend URL
FRONTEND_URL=https://your-domain.com

# Sanctum Token Expiration (minutes)
SANCTUM_EXPIRATION=1440

# Mail (Optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Frontend Client (.env.production)

```bash
# Data Source Mode
VITE_DATA_SOURCE=api
VITE_API_URL=https://api.your-domain.com

# Application
VITE_APP_NAME="Slide Demo"
VITE_APP_URL=https://your-domain.com
```

---

## Security Checklist

### Before Deployment

- [ ] Set `APP_ENV=production` in Laravel
- [ ] Set `APP_DEBUG=false` in Laravel
- [ ] Generate new `APP_KEY` for production
- [ ] Use strong database passwords
- [ ] Configure CORS to only allow your frontend domain
- [ ] Set `SANCTUM_EXPIRATION` for token security
- [ ] Enable SSL/HTTPS on both API and frontend
- [ ] Configure Content Security Policy headers (already implemented)
- [ ] Remove any test/demo users from database
- [ ] Disable directory listing in web server
- [ ] Set proper file permissions (755 directories, 644 files)
- [ ] Set `storage/` and `bootstrap/cache/` to 755 with www-data owner
- [ ] Configure rate limiting (already implemented: 5/min auth, 60/min API)
- [ ] Enable firewall (UFW, fail2ban)
- [ ] Keep dependencies up to date
- [ ] Regular security audits

### Laravel-Specific

```bash
# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure optimized autoloader
composer install --optimize-autoloader --no-dev

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Monitoring and Logging

### Laravel Telescope (Development Only)

**Do NOT install Telescope in production**. It's already a dev dependency.

### Error Tracking

#### Option 1: Sentry

```bash
composer require sentry/sentry-laravel

php artisan sentry:publish --dsn=https://your-sentry-dsn
```

```php
// config/logging.php
'channels' => [
    'sentry' => [
        'driver' => 'sentry',
    ],
],
```

#### Option 2: Bugsnag

```bash
composer require bugsnag/bugsnag-laravel

php artisan vendor:publish --provider="Bugsnag\BugsnagLaravel\BugsnagServiceProvider"
```

### Application Monitoring

#### Laravel Pulse (Built-in)

```bash
composer require laravel/pulse

php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"

php artisan migrate
```

Access at: `https://api.your-domain.com/pulse`

#### New Relic

```bash
# Install New Relic PHP agent
wget -O - https://download.newrelic.com/548C16BF.gpg | sudo apt-key add -
echo "deb http://apt.newrelic.com/debian/ newrelic non-free" | \
  sudo tee /etc/apt/sources.list.d/newrelic.list

sudo apt update
sudo apt install newrelic-php5

sudo newrelic-install install
```

### Log Management

**Laravel Logs:**
- Location: `storage/logs/laravel.log`
- Rotation: Configure in `config/logging.php`

**Nginx Logs:**
- Access: `/var/log/nginx/access.log`
- Error: `/var/log/nginx/error.log`

**Log Rotation:**

```bash
# /etc/logrotate.d/laravel
/var/www/slide-demo-api/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

### Uptime Monitoring

**Free Options:**
- UptimeRobot
- Freshping
- StatusCake

**Paid Options:**
- Pingdom
- DataDog
- New Relic Synthetics

---

## Post-Deployment Verification

### 1. API Health Check

```bash
# Test API is accessible
curl https://api.your-domain.com/api/v1/health

# Expected: {"success": true, "message": "API is running"}
```

### 2. API Documentation

Visit: `https://api.your-domain.com/docs/api`

Verify Scramble documentation loads correctly.

### 3. Authentication Flow

```bash
# Register new user
curl -X POST https://api.your-domain.com/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'

# Login
curl -X POST https://api.your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

### 4. Frontend Verification

- Visit `https://your-domain.com`
- Verify site loads
- Test registration
- Test login
- Create a project
- Complete a project
- Verify API calls succeed

### 5. CORS Verification

Open browser console on frontend:

```javascript
// Should succeed (not show CORS error)
fetch('https://api.your-domain.com/api/v1/health')
  .then(r => r.json())
  .then(console.log)
```

### 6. SSL Verification

```bash
# Check SSL certificate
curl -vI https://api.your-domain.com 2>&1 | grep -i "SSL"

# Test HTTPS redirect
curl -I http://api.your-domain.com
# Should return 301/302 redirect to HTTPS
```

### 7. Security Headers

```bash
# Check security headers
curl -I https://api.your-domain.com/api/v1/health

# Should include:
# X-Content-Type-Options: nosniff
# X-Frame-Options: DENY
# X-XSS-Protection: 1; mode=block
# Referrer-Policy: strict-origin-when-cross-origin
# Content-Security-Policy: ...
```

### 8. Performance Check

```bash
# Check response time
curl -w "@curl-format.txt" -o /dev/null -s https://api.your-domain.com/api/v1/health

# curl-format.txt:
# time_total:  %{time_total}s\n
```

---

## Rollback Plan

### If Deployment Fails

**Laravel API:**

```bash
# Restore from backup
cd /var/www/slide-demo-api
git reset --hard <previous-commit>
composer install
php artisan migrate:rollback
php artisan cache:clear
sudo systemctl reload php8.4-fpm
sudo systemctl reload nginx
```

**Frontend:**

```bash
# Revert to previous build
cd /var/www/your-domain.com
rm -rf *
tar -xzf ../backups/dist-backup-YYYY-MM-DD.tar.gz
```

**Database:**

```bash
# Restore from backup
mysql -u root -p slide_demo < /backups/slide_demo-YYYY-MM-DD.sql
```

### Backup Strategy

**Daily Automated Backups:**

```bash
#!/bin/bash
# /home/deploy/backup.sh

DATE=$(date +%Y-%m-%d)
BACKUP_DIR="/backups"

# Backup database
mysqldump -u root -p'password' slide_demo > \
  $BACKUP_DIR/slide_demo-$DATE.sql

# Backup Laravel files
tar -czf $BACKUP_DIR/laravel-$DATE.tar.gz \
  /var/www/slide-demo-api

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete

# Upload to S3 (optional)
aws s3 cp $BACKUP_DIR s3://your-backup-bucket/ --recursive
```

**Cron Job:**

```bash
crontab -e

# Daily backup at 2 AM
0 2 * * * /home/deploy/backup.sh >> /var/log/backup.log 2>&1
```

---

## Troubleshooting

### API Returns 500 Error

```bash
# Check Laravel logs
tail -f /var/www/slide-demo-api/storage/logs/laravel.log

# Check PHP-FPM logs
tail -f /var/log/php8.4-fpm.log

# Check Nginx logs
tail -f /var/log/nginx/error.log

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### CORS Errors

**Check CORS configuration:**

```php
// config/cors.php
'allowed_origins' => [
    env('FRONTEND_URL', 'https://your-domain.com'),
],
```

**Verify .env:**

```bash
FRONTEND_URL=https://your-domain.com  # No trailing slash
```

### Authentication Issues

**Token not working:**

```bash
# Check Sanctum config
php artisan config:clear

# Verify SANCTUM_STATEFUL_DOMAINS
# Should NOT be set for API-only mode

# Check token in request
curl -H "Authorization: Bearer <token>" \
  https://api.your-domain.com/api/v1/user
```

### Database Connection Failed

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check credentials in .env
# Verify database exists
mysql -u root -p -e "SHOW DATABASES;"

# Check user permissions
mysql -u root -p -e "SHOW GRANTS FOR 'slide_demo_user'@'localhost';"
```

### Frontend Not Loading

**Check build:**

```bash
# Verify dist/ directory exists
ls -la dist/

# Check for index.html
cat dist/index.html
```

**Check Nginx:**

```bash
# Test config
sudo nginx -t

# Check error logs
tail -f /var/log/nginx/error.log

# Verify root directory
cat /etc/nginx/sites-available/your-domain.com | grep root
```

### Permission Issues

```bash
# Fix Laravel permissions
cd /var/www/slide-demo-api
sudo chown -R www-data:www-data .
sudo chmod -R 755 storage bootstrap/cache

# Fix SELinux (if enabled)
sudo semanage fcontext -a -t httpd_sys_rw_content_t \
  "/var/www/slide-demo-api/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t \
  "/var/www/slide-demo-api/bootstrap/cache(/.*)?"
sudo restorecon -Rv /var/www/slide-demo-api
```

---

## Support

For issues or questions:
- Check Laravel documentation: https://laravel.com/docs
- Check Vue documentation: https://vuejs.org/guide/
- Review `docs/` folder for project-specific docs
- Check application logs for error messages

---

**Last Updated**: January 11, 2026
**Version**: 1.0.0
**Status**: Production Ready
