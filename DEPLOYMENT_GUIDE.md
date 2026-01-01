# 🚀 Tanzania Scam Detector MVP - Deployment Guide

> Complete deployment and setup guide for the advanced scam detection system with continuous learning capabilities

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Environment Configuration](#environment-configuration)
- [Running Migrations](#running-migrations)
- [Seeding Initial Data](#seeding-initial-data)
- [Model Training](#model-training)
- [Scheduling Configuration](#scheduling-configuration)
- [Testing the Deployment](#testing-the-deployment)
- [Monitoring and Maintenance](#monitoring-and-maintenance)
- [Troubleshooting](#troubleshooting)

## 🔧 Prerequisites

### System Requirements
- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **MySQL**: 8.0 or higher (or PostgreSQL 13+)
- **Web Server**: Apache/Nginx
- **SSL Certificate**: For production deployment

### Required PHP Extensions
```bash
php -m | grep -E "(curl|fileinfo|json|mbstring|mysql|openssl|tokenizer|xml|zip)"
```

### Required Software
- Git
- Node.js 16+ and npm (for asset compilation)
- Cron daemon (for scheduled tasks)

## 📦 Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd scam-detector-mvp
```

### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

### 3. Set Permissions
```bash
# Set proper ownership
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Set Laravel cache permissions
sudo chmod -R 775 bootstrap/cache
```

## 🗄️ Database Setup

### 1. Create Database
```sql
CREATE DATABASE scam_detector CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'scam_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON scam_detector.* TO 'scam_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Configure Database Connection
Update your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scam_detector
DB_USERNAME=scam_user
DB_PASSWORD=strong_password
```

## ⚙️ Environment Configuration

### 1. Copy Environment File
```bash
cp .env.example .env
```

### 2. Generate Application Key
```bash
php artisan key:generate
```

### 3. Configure Environment Variables
```env
# Application
APP_NAME="Tanzania Scam Detector"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scam_detector
DB_USERNAME=scam_user
DB_PASSWORD=strong_password

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=single
LOG_LEVEL=error

# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database
```

## 🔄 Running Migrations

### 1. Run Database Migrations
```bash
php artisan migrate --force
```

### 2. Verify Tables Created
```sql
SHOW TABLES;
```
You should see these tables:
- `training_data`
- `user_feedback`
- `scam_senders`
- `users`
- `password_reset_tokens`
- `personal_access_tokens`
- `sessions`
- `cache`
- `cache_locks`
- `job_batches`
- `failed_jobs`
- `jobs`

## 🌱 Seeding Initial Data

### 1. Seed Training Data
```bash
php artisan db:seed --class=TrainingDataSeeder
```

This will:
- Import existing CSV training data
- Create verified training samples
- Set up initial spam/ham distribution

### 2. Verify Training Data
```sql
SELECT COUNT(*) as total_samples FROM training_data;
SELECT label, COUNT(*) as count FROM training_data GROUP BY label;
```

Expected output:
- Total samples: ~1500
- Spam samples: ~750
- Ham samples: ~750

## 🤖 Model Training

### 1. Train Initial Model
```bash
php artisan train:spam-model
```

### 2. Verify Model Training
```bash
php artisan training:status
```

Expected output:
```json
{
  "status": "success",
  "message": "Training status retrieved successfully",
  "data": {
    "modelStatus": {
      "is_trained": true,
      "model_size": 24576,
      "last_trained": "2025-12-30 10:30:00",
      "path": "/storage/app/spam_model.phpml"
    }
  }
}
```

### 3. Test Model Functionality
```bash
php artisan test:scam-detection
```

## ⏰ Scheduling Configuration

### 1. Add Cron Entry
```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /path/to/your/app && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Verify Cron Schedule
```bash
# Check if cron is running
ps aux | grep cron

# Test scheduled tasks
php artisan schedule:list
```

Expected scheduled tasks:
- Every 6 hours: Process feedback
- Daily at 2 AM: Process feedback and retrain model
- Weekly: Database backup and cleanup

### 3. Test Manual Scheduling
```bash
# Process feedback manually
php artisan scam:process-feedback

# Run backup and maintenance
php artisan scam:backup-maintenance --all
```

## 🧪 Testing the Deployment

### 1. Test Basic Functionality
```bash
# Test API health
curl -X GET http://localhost:8000/api/v1/training/status

# Test scam detection
curl -X POST http://localhost:8000/api/v1/scam/check \
  -H "Content-Type: application/json" \
  -d '{"text": "M-Pesa reversal Ksh 2500 pending. Confirm PIN now!", "sender": "MPESA"}'

# Test feedback system
curl -X POST http://localhost:8000/api/v1/feedback/scam-message \
  -H "Content-Type: application/json" \
  -d '{"message_text": "Test scam message", "sender_identifier": "+254712345678", "sender_type": "phone"}'
```

### 2. Run Test Suite
```bash
php artisan test
```

### 3. Test Web Interface
- Navigate to `http://localhost:8000`
- Test scam detection form
- Test training interface

### 4. Performance Testing
```bash
# Load testing with Apache Bench
ab -n 1000 -c 10 http://localhost:8000/api/v1/scam/check

# Expected results:
# - Response time < 500ms for 95% of requests
# - Success rate > 99%
```

## 📊 Monitoring and Maintenance

### 1. Log Monitoring
```bash
# Application logs
tail -f storage/logs/laravel.log

# Scheduler logs
tail -f storage/logs/scheduler.log

# Error logs
tail -f storage/logs/laravel-error.log
```

### 2. Database Monitoring
```bash
# Check database size
SELECT 
  table_schema AS 'Database',
  ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB'
FROM information_schema.tables 
WHERE table_schema = 'scam_detector'
GROUP BY table_schema;

# Monitor feedback processing
SELECT 
  feedback_type,
  COUNT(*) as count,
  SUM(CASE WHEN is_processed = 1 THEN 1 ELSE 0 END) as processed
FROM user_feedback 
GROUP BY feedback_type;
```

### 3. Performance Monitoring
```bash
# Monitor API response times
grep "Response time" storage/logs/laravel.log | tail -100

# Monitor model accuracy
SELECT 
  DATE(created_at) as date,
  COUNT(*) as total_checks,
  AVG(CASE WHEN confidence_score > 0.8 THEN 1 ELSE 0 END) as accuracy
FROM user_feedback 
WHERE feedback_type = 'scam_message'
GROUP BY DATE(created_at);
```

### 4. Regular Maintenance Tasks

#### Weekly Database Backup
```bash
php artisan scam:backup-maintenance --backup
```

#### Monthly Model Retraining
```bash
php artisan scam:process-feedback --auto-retrain
```

#### Quarterly Performance Review
- Review feedback statistics
- Analyze false positive rates
- Update training data if needed
- Optimize database indexes

## 🚨 Troubleshooting

### Common Issues

#### 1. Database Connection Errors
```bash
# Check database connectivity
php artisan migrate:status

# Test database connection
php artisan tinker
> DB::connection()->getPdo();
```

#### 2. Model Training Failures
```bash
# Check training data
php artisan training:data

# Verify CSV file exists and is readable
ls -la storage/app/spam.csv

# Check file permissions
chmod 644 storage/app/spam.csv
```

#### 3. API Response Errors
```bash
# Check application logs
tail -f storage/logs/laravel.log

# Test API endpoint manually
php artisan serve
curl -v http://localhost:8000/api/v1/scam/check
```

#### 4. Scheduler Not Running
```bash
# Check cron configuration
crontab -l

# Test scheduler manually
php artisan schedule:work

# Check Laravel log for schedule errors
grep "Running scheduled command" storage/logs/laravel.log
```

#### 5. High Memory Usage
```bash
# Monitor memory usage
php artisan optimize:clear
php artisan config:cache

# Check for memory leaks
php artisan tinker
> memory_get_usage(true);
> gc_collect_cycles();
```

### Performance Optimization

#### 1. Enable Caching
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 2. Optimize Database
```bash
# Add database indexes
php artisan migrate --force

# Analyze query performance
EXPLAIN SELECT * FROM training_data WHERE label = 'spam' LIMIT 10;
```

#### 3. Configure Web Server

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.*)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/app/public;

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
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🔒 Security Considerations

### 1. Environment Security
- Use strong database passwords
- Enable SSL/TLS in production
- Set proper file permissions (644 for files, 755 for directories)
- Disable debug mode in production

### 2. API Security
- Implement rate limiting
- Add API authentication for production
- Use HTTPS for all API endpoints
- Validate and sanitize all inputs

### 3. Database Security
- Regular security updates
- Limited database user privileges
- Encrypted connections
- Regular backups

## 📈 Scaling Considerations

### 1. Database Scaling
- Use read replicas for heavy read operations
- Implement connection pooling
- Consider database partitioning for large datasets
- Regular database maintenance and optimization

### 2. Application Scaling
- Use load balancers for multiple application servers
- Implement caching strategies (Redis/Memcached)
- Use queues for background processing
- Consider horizontal scaling with containerization

### 3. Monitoring and Alerting
- Set up application performance monitoring
- Configure alerts for high error rates
- Monitor database performance metrics
- Set up log aggregation and analysis

---

## 🎯 Post-Deployment Checklist

- [ ] Application is accessible via web interface
- [ ] All API endpoints are responding correctly
- [ ] Database migrations completed successfully
- [ ] Initial training data seeded
- [ ] Model training completed
- [ ] Scheduler is running cron jobs
- [ ] Backup system is configured
- [ ] SSL certificate installed and working
- [ ] Logging is configured and working
- [ ] Error monitoring is set up
- [ ] Performance monitoring is active
- [ ] Security headers are configured
- [ ] Rate limiting is implemented
- [ ] Documentation is up to date

**Deployment completed successfully! 🎉**

For support and troubleshooting, refer to the logs and monitoring systems configured during deployment.