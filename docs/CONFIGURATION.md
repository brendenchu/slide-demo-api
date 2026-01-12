# Configuration Guide

**Project**: Slide Demo API - Laravel REST API
**Last Updated**: January 11, 2026

## Table of Contents

- [Overview](#overview)
- [Laravel Backend Configuration](#laravel-backend-configuration)
- [Frontend Client Configuration](#frontend-client-configuration)
- [CORS Configuration](#cors-configuration)
- [Sanctum Configuration](#sanctum-configuration)
- [Database Configuration](#database-configuration)
- [Production Deployment](#production-deployment)
- [Environment Examples](#environment-examples)
- [Troubleshooting](#troubleshooting)

---

## Overview

This application consists of two parts that can be deployed separately:

1. **Laravel Backend API** - RESTful API with token-based authentication
2. **Frontend Client** - Standalone single-page application (Vue, React, Angular, etc.)

Each part has its own environment configuration that must be properly coordinated for the system to work.

---

## Laravel Backend Configuration

### Environment File Location

```
/path/to/vue-slide-demo/.env
```

### Core Configuration

#### Application Settings

```bash
# Application name (shown in emails, logs)
APP_NAME="Slide Demo API"

# Environment: local, staging, production
APP_ENV=local

# Application key (generate with: php artisan key:generate)
APP_KEY=base64:...

# Debug mode (NEVER enable in production)
APP_DEBUG=true

# Application timezone
APP_TIMEZONE=UTC

# Application URL (for asset generation, email links)
APP_URL=https://vue-slide-demo.test
```

**Key Variables**:

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `APP_NAME` | Yes | Laravel | Application name |
| `APP_ENV` | Yes | production | Environment: local, staging, production |
| `APP_KEY` | Yes | - | Encryption key (32-character random string) |
| `APP_DEBUG` | Yes | false | Debug mode (disable in production) |
| `APP_TIMEZONE` | No | UTC | Application timezone |
| `APP_URL` | Yes | http://localhost | Base application URL |

#### Localization

```bash
# Default language
APP_LOCALE=en

# Fallback language
APP_FALLBACK_LOCALE=en

# Faker locale for seeders/factories
APP_FAKER_LOCALE=en_US
```

#### Security & Hashing

```bash
# Bcrypt rounds (higher = more secure, slower)
# Recommended: 10-12 for production
BCRYPT_ROUNDS=12
```

### Database Configuration

#### SQLite (Default - Development)

```bash
# Use SQLite for local development
DB_CONNECTION=sqlite

# SQLite database file location
# Defaults to database/database.sqlite
```

#### MySQL/MariaDB (Production)

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vue_slide_demo
DB_USERNAME=root
DB_PASSWORD=secret
```

#### PostgreSQL

```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=vue_slide_demo
DB_USERNAME=postgres
DB_PASSWORD=secret
```

**Database Variables**:

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `DB_CONNECTION` | Yes | sqlite | Database driver: sqlite, mysql, pgsql |
| `DB_HOST` | MySQL/PG | 127.0.0.1 | Database host |
| `DB_PORT` | MySQL/PG | 3306/5432 | Database port |
| `DB_DATABASE` | MySQL/PG | - | Database name |
| `DB_USERNAME` | MySQL/PG | - | Database user |
| `DB_PASSWORD` | MySQL/PG | - | Database password |

### Session Configuration

```bash
# Session driver: file, cookie, database, redis
SESSION_DRIVER=database

# Session lifetime in minutes (120 = 2 hours)
SESSION_LIFETIME=120

# Encrypt session data
SESSION_ENCRYPT=false

# Session cookie path
SESSION_PATH=/

# Session cookie domain (null = current domain)
SESSION_DOMAIN=null
```

### Cache Configuration

```bash
# Cache store: file, database, redis, memcached
CACHE_STORE=database

# Cache key prefix
CACHE_PREFIX=
```

### Queue Configuration

```bash
# Queue connection: sync, database, redis, sqs
# Use 'sync' for development (runs immediately)
# Use 'database' or 'redis' for production (background processing)
QUEUE_CONNECTION=database
```

### API & CORS Configuration

```bash
# Frontend URL for CORS (required for production API mode)
# This tells Laravel which origin to allow API requests from
# Examples:
#   https://app.yourdomain.com
#   https://your-spa.netlify.app
#   https://your-spa.vercel.app
FRONTEND_URL=
```

**CORS Behavior**:
- **Local Development**: CORS allows localhost by default
- **Production**: MUST set `FRONTEND_URL` to SPA's deployed URL
- See [CORS Configuration](#cors-configuration) section

### Mail Configuration

```bash
# Mail driver: smtp, log, sendmail, mailgun, ses
MAIL_MAILER=log

# SMTP settings (if using smtp driver)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls

# From address for outgoing emails
MAIL_FROM_ADDRESS="noreply@vue-slide-demo.test"
MAIL_FROM_NAME="${APP_NAME}"
```

### Logging

```bash
# Log channel: stack, single, daily, slack, syslog
LOG_CHANNEL=stack

# Channels to use (comma-separated)
LOG_STACK=single

# Deprecation warnings channel
LOG_DEPRECATIONS_CHANNEL=null

# Log level: debug, info, notice, warning, error, critical, alert, emergency
LOG_LEVEL=debug
```

### Redis Configuration (Optional)

```bash
# Redis client: phpredis or predis
REDIS_CLIENT=phpredis

# Redis connection
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Broadcasting (Optional)

```bash
# Broadcast driver: log, pusher, redis, null
BROADCAST_CONNECTION=log
```

### Filesystem (Optional)

```bash
# Default filesystem disk: local, public, s3
FILESYSTEM_DISK=local
```

### AWS S3 (Optional)

```bash
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Sanctum Configuration (API Authentication)

**Note**: Sanctum settings are in `config/sanctum.php`, not `.env`

Key configuration in `config/sanctum.php`:
```php
// Token expiration (null = never expire)
'expiration' => null,

// Stateful domains (for SPA cookie auth - not used in this project)
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),
```

---

## Frontend Client Configuration

### Environment File Location

Frontend applications typically have their own environment configuration file(s) for client-side variables.

### Example Client Configuration Variables

```bash
# Application Settings (example using Vite)
VITE_APP_NAME="Slide Demo"
VITE_APP_URL=http://localhost:3000

# Storage Configuration (Local Mode)
VITE_STORAGE_PREFIX=vsd

# Data Source Mode
# Options: local, api, hybrid
# - local: Browser-only storage (LocalStorage + IndexedDB)
# - api: Connect to Laravel backend API
# - hybrid: Offline-first with API sync (future)
VITE_DATA_SOURCE=local

# API Configuration (Required for api/hybrid modes)
# Point to Laravel backend (WITHOUT /api suffix)
# Examples:
#   Development (Herd): https://vue-slide-demo.test
#   Development (Serve): http://localhost:8000
#   Production: https://api.yourdomain.com
VITE_API_URL=

# Debug Mode
# Enable additional console logging
VITE_DEBUG=false
```

### Variable Reference

**Note**: Environment variable names and requirements vary by frontend framework (Vite, Next.js, Create React App, etc.)

Example variables for a typical SPA client:

| Variable | Description |
|----------|-------------|
| `APP_NAME` | Application name (shown in UI) |
| `APP_URL` | Client application base URL |
| `API_URL` | Backend API base URL |
| `DEBUG` | Enable debug logging |

### Data Source Modes Explained

#### Local Mode

```bash
VITE_DATA_SOURCE=local
# No other variables needed
```

**When to use**:
- Development without backend
- Demos and testing
- Offline development
- Prototyping

**Storage**:
- LocalStorage for small data (user, tokens)
- IndexedDB for large data (projects, responses)
- All keys prefixed with `VITE_STORAGE_PREFIX`

#### API Mode

```bash
VITE_DATA_SOURCE=api
VITE_API_URL=https://vue-slide-demo.test
```

**When to use**:
- Production deployment
- Multi-device access
- Real authentication required
- Data persistence needed

**Requirements**:
- Laravel backend must be running
- `VITE_API_URL` must point to backend (without `/api` suffix)
- CORS must allow SPA origin
- Sanctum authentication configured

#### Hybrid Mode (Future)

```bash
VITE_DATA_SOURCE=hybrid
VITE_API_URL=https://vue-slide-demo.test
```

**Status**: Not yet implemented (Phase 5)

**Planned features**:
- Offline-first operation
- Background sync when online
- Conflict resolution
- Service worker integration

---

## CORS Configuration

### What is CORS?

Cross-Origin Resource Sharing (CORS) allows a frontend client (running on one domain) to make requests to the API (running on another domain).

### Laravel CORS Setup

Configuration file: `config/cors.php`

```php
return [
    // API routes that support CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Allowed HTTP methods
    'allowed_methods' => ['*'],

    // Allowed origins (where requests can come from)
    'allowed_origins' => [
        env('FRONTEND_URL'),  // Production SPA URL
    ],

    // Allowed request headers
    'allowed_headers' => ['*'],

    // Headers exposed to browser
    'exposed_headers' => [],

    // Max age for preflight requests (in seconds)
    'max_age' => 0,

    // Allow credentials (cookies, auth headers)
    'supports_credentials' => true,
];
```

### Development vs Production

#### Development (Same Domain)

When serving both API and frontend from the same origin:

```bash
# Backend API
APP_URL=https://api.local.test

# Frontend Client
API_URL=https://api.local.test

# No CORS issues - same origin!
```

#### Development (Different Ports)

If running separately:

```bash
# Backend API
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

# Frontend Client
API_URL=http://localhost:8000

# CORS enabled via FRONTEND_URL
```

#### Production (Different Domains)

```bash
# Backend API (.env)
APP_URL=https://api.yourdomain.com
FRONTEND_URL=https://app.yourdomain.com

# Frontend Client (.env.production)
API_URL=https://api.yourdomain.com

# CORS configured via FRONTEND_URL
```

### Testing CORS

```bash
# From frontend client domain, test API
curl -H "Origin: https://app.yourdomain.com" \
     -H "Access-Control-Request-Method: GET" \
     -H "Access-Control-Request-Headers: Authorization" \
     -X OPTIONS \
     https://api.yourdomain.com/api/v1/projects

# Should return CORS headers:
# Access-Control-Allow-Origin: https://app.yourdomain.com
# Access-Control-Allow-Credentials: true
```

---

## Sanctum Configuration

### Token Authentication

Configuration file: `config/sanctum.php`

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Token Expiration
    |--------------------------------------------------------------------------
    |
    | Minutes until API tokens expire. Set to null for no expiration.
    |
    */
    'expiration' => null,  // Never expire (suitable for SPA)

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix tokens. Default is an empty string.
    |
    */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
];
```

### Token Expiration Strategies

#### Never Expire (Current)

```php
'expiration' => null,
```

**Pros**:
- Simple to implement
- No refresh token flow needed
- User stays logged in

**Cons**:
- Token valid forever (until manually revoked)
- Security concern if token leaked

**Best for**: Internal applications, trusted environments

#### Short Expiration (Alternative)

```php
'expiration' => 60,  // 1 hour
```

**Pros**:
- More secure (limited window if leaked)
- Forces periodic re-authentication

**Cons**:
- Requires refresh token implementation
- More complex frontend logic
- User logs out frequently

**Best for**: Public applications, sensitive data

### Token Management

**Create Token**:
```php
$token = $user->createToken('api-client')->plainTextToken;
```

**Revoke Token** (Logout):
```php
$request->user()->currentAccessToken()->delete();
```

**Revoke All Tokens**:
```php
$user->tokens()->delete();
```

---

## Database Configuration

### SQLite (Development)

**Advantages**:
- Zero configuration
- File-based (portable)
- Perfect for local development
- Included with PHP

**Setup**:
```bash
# .env
DB_CONNECTION=sqlite

# Create database file
touch database/database.sqlite

# Run migrations
php artisan migrate
```

**Limitations**:
- Not recommended for production
- Limited concurrency
- No network access

### MySQL (Production)

**Advantages**:
- Industry standard
- Excellent performance
- Great tooling
- Wide hosting support

**Setup**:
```bash
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vue_slide_demo
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Create database
mysql -u root -p -e "CREATE DATABASE vue_slide_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate
```

**Production Considerations**:
- Use strong password
- Enable SSL connections
- Regular backups
- Monitor performance

### PostgreSQL (Alternative)

**Advantages**:
- Advanced features (JSON, full-text search)
- Strong data integrity
- Excellent for complex queries

**Setup**:
```bash
# .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=vue_slide_demo
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Create database
psql -U postgres -c "CREATE DATABASE vue_slide_demo;"

# Run migrations
php artisan migrate
```

---

## Production Deployment

### Laravel Backend Deployment

#### Environment Configuration

```bash
# Production .env
APP_NAME="Slide Demo API"
APP_ENV=production
APP_KEY=base64:...  # Generate with: php artisan key:generate
APP_DEBUG=false      # CRITICAL: Must be false
APP_URL=https://api.yourdomain.com

# Database (MySQL recommended)
DB_CONNECTION=mysql
DB_HOST=your-db-host.com
DB_PORT=3306
DB_DATABASE=production_db
DB_USERNAME=db_user
DB_PASSWORD=strong_password

# Session & Cache (Redis recommended)
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=your-redis-host.com
REDIS_PASSWORD=redis_password
REDIS_PORT=6379

# Mail (Use real service)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"

# CORS (CRITICAL for API mode)
FRONTEND_URL=https://app.yourdomain.com

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

#### Deployment Checklist

**Before Deployment**:
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure production database
- [ ] Set `FRONTEND_URL` for CORS
- [ ] Configure mail service
- [ ] Set up Redis (recommended)
- [ ] Enable HTTPS/SSL

**Deployment Steps**:
```bash
# 1. Install dependencies (production only)
composer install --optimize-autoloader --no-dev

# 2. Generate application key
php artisan key:generate

# 3. Run migrations
php artisan migrate --force

# 4. Seed database (if needed)
php artisan db:seed --force

# 5. Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Set file permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 7. Start queue worker (if using queues)
php artisan queue:work --daemon

# 8. Setup supervisor for queue workers
# See: https://laravel.com/docs/queues#supervisor-configuration
```

**Server Requirements**:
- PHP 8.2+
- Composer 2+
- Database (MySQL 8+)
- Redis (optional but recommended)
- SSL certificate (required for Sanctum)

### Frontend Client Deployment

#### Environment Configuration

Create `.env.production`:

```bash
# Production SPA environment
VITE_APP_NAME="Slide Demo"
VITE_APP_URL=https://app.yourdomain.com

# API Mode (Production)
VITE_DATA_SOURCE=api
VITE_API_URL=https://api.yourdomain.com

# Debug (disable in production)
VITE_DEBUG=false
```

#### Build & Deploy

```bash
# 1. Install dependencies
npm install

# 2. Build for production
npm run build

# 3. Output in dist/ directory
# Deploy dist/ folder to static hosting
```

#### Deployment Options

**Option 1: Netlify**
```bash
# netlify.toml
[build]
  command = "npm run build"
  publish = "dist"

[[redirects]]
  from = "/*"
  to = "/index.html"
  status = 200
```

**Option 2: Vercel**
```json
// vercel.json
{
  "rewrites": [
    { "source": "/(.*)", "destination": "/index.html" }
  ]
}
```

**Option 3: Nginx**
```nginx
server {
    listen 80;
    server_name app.yourdomain.com;

    root /var/www/vue-spa/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Enable gzip compression
    gzip on;
    gzip_types text/css application/javascript application/json;
}
```

**Option 4: AWS S3 + CloudFront**
```bash
# Upload to S3
aws s3 sync dist/ s3://your-bucket-name/ --delete

# Invalidate CloudFront cache
aws cloudfront create-invalidation --distribution-id YOUR_DIST_ID --paths "/*"
```

#### Post-Deployment Checklist

- [ ] Verify `VITE_API_URL` points to production API
- [ ] Ensure HTTPS is enabled (required for Sanctum)
- [ ] Test API connectivity from deployed SPA
- [ ] Verify CORS allows SPA origin
- [ ] Check all routes work (SPA routing)
- [ ] Test authentication flow
- [ ] Monitor browser console for errors
- [ ] Setup CDN for assets (optional)
- [ ] Configure caching headers
- [ ] Test on multiple browsers

---

## Environment Examples

### Development (Separate Servers)

**Laravel** (`.env`):
```bash
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
DB_CONNECTION=sqlite
```

**Frontend Client** (`.env`):
```bash
VITE_DATA_SOURCE=api
VITE_API_URL=http://localhost:8000
```

### Development (Laravel Herd)

**Backend API** (`.env`):
```bash
APP_URL=https://api.local.test
FRONTEND_URL=
DB_CONNECTION=sqlite
```

**Frontend Client** (`.env`):
```bash
VITE_DATA_SOURCE=api
VITE_API_URL=https://vue-slide-demo.test
```

### Staging

**Backend API** (`.env`):
```bash
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging-api.yourdomain.com
FRONTEND_URL=https://staging.yourdomain.com
DB_CONNECTION=mysql
DB_HOST=staging-db.yourdomain.com
```

**Frontend Client** (`.env.staging`):
```bash
VITE_DATA_SOURCE=api
VITE_API_URL=https://staging-api.yourdomain.com
VITE_DEBUG=false
```

### Production

**Backend API** (`.env`):
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com
FRONTEND_URL=https://app.yourdomain.com
DB_CONNECTION=mysql
DB_HOST=production-db.yourdomain.com
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
LOG_LEVEL=warning
```

**Frontend Client** (`.env.production`):
```bash
VITE_DATA_SOURCE=api
VITE_API_URL=https://api.yourdomain.com
VITE_DEBUG=false
```

---

## Troubleshooting

### CORS Errors

**Symptom**: "Access to XMLHttpRequest blocked by CORS policy"

**Solutions**:
1. Set `FRONTEND_URL` in Laravel `.env`
2. Verify `FRONTEND_URL` matches SPA origin exactly
3. Check CORS config in `config/cors.php`
4. Clear Laravel cache: `php artisan config:clear`
5. Verify API response includes `Access-Control-Allow-Origin` header

### Authentication Issues

**Symptom**: 401 Unauthorized errors

**Solutions**:
1. Check token is stored: localStorage `vsd:auth:token`
2. Verify `Authorization: Bearer {token}` header sent
3. Check token hasn't been deleted in database
4. Try logging in again
5. Check Sanctum configuration

### API Connection Errors

**Symptom**: "Network Error" or "ERR_CONNECTION_REFUSED"

**Solutions**:
1. Verify `VITE_API_URL` is correct (no trailing slash, no `/api`)
2. Ensure Laravel backend is running
3. Test API manually: `curl $VITE_API_URL/api/v1/auth/user`
4. Check firewall/security groups allow traffic
5. Verify SSL certificate (if HTTPS)

### Database Connection Errors

**Symptom**: "SQLSTATE[HY000] [2002] Connection refused"

**Solutions**:
1. Verify database credentials in `.env`
2. Ensure database server is running
3. Check database exists: `SHOW DATABASES;`
4. Verify host/port are correct
5. Check firewall allows database port

### Environment Variables Not Loading

**Symptom**: Default values used instead of `.env` values

**Solutions**:

**Laravel**:
```bash
# Clear config cache
php artisan config:clear

# Recreate cache
php artisan config:cache
```

**Frontend Client**:
```bash
# Restart dev server (build tool reads .env on startup)
npm run dev
```

---

## Security Best Practices

### Laravel Backend

1. **Never commit `.env`** - add to `.gitignore`
2. **Set `APP_DEBUG=false`** in production
3. **Use strong `APP_KEY`** - 32+ character random string
4. **Enable HTTPS** for production (required for Sanctum)
5. **Use environment-specific configs** - separate dev/staging/prod
6. **Rotate tokens** on logout
7. **Set restrictive CORS** - only allow specific origins
8. **Use strong database passwords**
9. **Keep dependencies updated** - `composer update`
10. **Enable rate limiting** - protects against abuse

### Frontend Client

1. **Never commit `.env`** files with secrets
2. **Don't store sensitive data** in localStorage
3. **Use HTTPS** in production
4. **Validate on backend** - don't trust frontend validation
5. **Sanitize user input** - prevent XSS
6. **Keep dependencies updated** - `npm update`
7. **Use CSP headers** - content security policy
8. **Clear tokens on logout**
9. **Set `VITE_DEBUG=false`** in production

---

## Related Documentation

- [API Integration Guide](API_INTEGRATION.md) - API setup and architecture
- [API Documentation](api/API_DOCUMENTATION.md) - API endpoint reference
- [Architecture Documentation](ARCHITECTURE.md) - System architecture

---

**Last Updated**: January 11, 2026
