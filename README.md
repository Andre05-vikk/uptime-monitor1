# 🔍 Uptime Monitor - Web Monitoring Application

> **Purpose**: Minimal web application to monitor website availability, send email notifications when sites are down, and support user authentication. Designed to run on a server with automated cron checks.

---

## 🚀 Quick Start

### One-Command Launch

```fish
# Fish Shell (recommended)
./start.fish

# Bash Shell
./start.sh

# NPM Scripts
npm start
npm run dev
```

### Manual Launch

```fish
php -S localhost:8000
open http://localhost:8000
```

### Login Credentials

- **Username**: `admin`
- **Password**: `admin123`

---

## 📋 Features

✅ **User Authentication System**
- Secure login/logout with PHP sessions
- User registration with validation
- Session management and protection

✅ **Website Monitoring**
- Add/manage monitored URLs
- Real-time status checking
- HTTP response time tracking
- Visual status indicators (🟢 UP / 🔴 DOWN)

✅ **Email Notifications**
- Automatic alerts when sites go down
- Mailgun integration for reliable delivery
- One alert per incident (no spam)
- Detailed error messages with timestamps

✅ **Automated Monitoring**
- Cron-compatible PHP script (`monitor.php`)
- Configurable check intervals
- Comprehensive logging
- Alert tracking and history

✅ **Dashboard Interface**
- Real-time monitoring status
- Statistics overview (Total/Online/Offline)
- Response time metrics
- Recent alerts history
- Manual refresh capability

---

## 🛠️ Installation & Setup

### Prerequisites

- PHP 8.0+ with cURL extension
- Composer (for dependencies)
- Web server or PHP built-in server

### Quick Setup

1. **Clone/Download** this repository
2. **Configure environment**:
   ```fish
   cp .env.example .env
   # Edit .env file with your Mailgun credentials
   ```
3. **Install dependencies**:
   ```fish
   composer install
   npm install  # for testing (optional)
   ```
4. **Start application**:
   ```fish
   ./start.fish
   ```

---

## 🎯 Usage Guide

### 1. Starting the Application

**Automated start (recommended)**:
```fish
./start.fish
```
This will:
- Check and kill any existing processes on port 8000
- Start PHP development server
- Open browser automatically
- Show real-time server logs
- Display login credentials

**Manual start**:
```fish
php -S localhost:8000
```

### 2. Logging In

Navigate to `http://localhost:8000` and use:
- Username: `admin`
- Password: `admin123`

### 3. Adding Monitors

1. Log into the dashboard
2. Enter website URL (e.g., `https://google.com`)
3. Enter notification email
4. Click "Add Monitor"

### 4. Viewing Status

The dashboard shows:
- **Statistics**: Total monitors, online/offline counts
- **Live Status**: Real-time UP/DOWN indicators
- **Response Times**: Performance metrics in milliseconds
- **HTTP Codes**: Status codes (200, 404, etc.)
- **Recent Alerts**: Last 3 alerts per monitor

### 5. Manual Monitoring Check

```fish
# Run monitoring script manually
php monitor.php

# Or via npm
npm run monitor
npm run check
```

### 6. Stopping the Server

```fish
./stop.fish
# or
npm stop
```

---

## ⚙️ Configuration

### Environment Setup (.env)

1. **Create environment file**:
   ```fish
   cp .env.example .env
   ```

2. **Edit .env file** with your credentials:
   ```bash
   # Required for email notifications
   MAILGUN_API_KEY=your-actual-mailgun-api-key
   MAILGUN_DOMAIN=your-actual-domain.com
   
   # Optional customizations
   FROM_NAME="Your Monitor Name"
   ADMIN_EMAIL=your-admin@email.com
   MONITOR_TIMEOUT=10
   ```

### Email Setup (Mailgun)

1. **Get Mailgun credentials**:
   - API Key: https://app.mailgun.com/app/account/security/api_keys
   - Domain: https://app.mailgun.com/app/domains

2. **Add to .env file**:
   ```bash
   MAILGUN_API_KEY=key-1234567890abcdef1234567890abcdef
   MAILGUN_DOMAIN=mg.yourdomain.com
   ```

### Alternative: Environment Variables

You can also set environment variables directly:
```fish
export MAILGUN_API_KEY="your-api-key"
export MAILGUN_DOMAIN="your-domain.com"
```

### Automated Monitoring (Cron)

Add to crontab for automatic checks:

```cron
# Check every 5 minutes
*/5 * * * * cd /path/to/uptime-monitor && php monitor.php

# Check every minute
* * * * * cd /path/to/uptime-monitor && php monitor.php
```

Setup cron:
```fish
crontab -e
# Add the line above, save and exit
```

---

## 📁 File Structure

### Core Application Files
- `index.php` - Login page
- `dashboard.php` - Main monitoring dashboard
- `signup.php` - User registration
- `logout.php` - Logout handler
- `config.php` - Configuration and functions
- `monitor.php` - Cron monitoring script

### Configuration Files
- `.env` - Environment variables (API keys, settings)
- `.env.example` - Environment template
- `composer.json` - PHP dependencies
- `package.json` - NPM scripts and test dependencies

### Data Files
- `users.json` - User credentials (JSON)
- `monitors.json` - Monitor configurations
- `alerts.json` - Alert history and tracking

### Scripts
- `start.fish` / `start.sh` - Application startup scripts
- `stop.fish` / `stop.sh` - Server stop scripts
- `package.json` - NPM scripts configuration

### Documentation
- `README.md` - This file (usage guide)
- `TEST.md` - Complete testing documentation

---

## 🔧 Development

### Available NPM Scripts

```fish
npm start          # Start application with browser
npm stop           # Stop the server
npm run dev        # Development mode (same as start)
npm run server     # PHP server only (no browser)
npm run monitor    # Run monitoring check
npm run test       # Run all tests (see TEST.md for details)
npm run test:headed # Run tests with visible browser
npm run test:ui    # Interactive test UI
```

### Project Structure

```
/uptime-monitor/
├── Core PHP Files
│   ├── index.php (Login)
│   ├── dashboard.php (Main UI)
│   ├── config.php (Functions)
│   └── monitor.php (Cron script)
├── Data Storage
│   ├── users.json
│   ├── monitors.json
│   └── alerts.json
├── Scripts
│   ├── start.fish/sh
│   └── stop.fish/sh
└── Tests
    └── tests/*.spec.js
```

---

## 🛡️ Security Features

- **Session Management**: Secure PHP sessions with proper cleanup
- **Password Hashing**: PHP `password_hash()` for secure storage
- **Input Validation**: URL and email format validation
- **CSRF Protection**: Form validation and authentication checks
- **Session Timeout**: Automatic logout handling

---

## 📊 Monitoring Features

### Real-time Status Checking
- HTTP response codes
- Connection timeouts (10-second limit)
- SSL certificate validation
- Response time measurement
- Error message capture

### Alert System
- **Smart Notifications**: One email per incident
- **Alert Tracking**: Prevent duplicate notifications
- **Detailed Messages**: URL, timestamp, error details
- **Alert History**: Dashboard shows recent alerts

### Performance Metrics
- Response time in milliseconds
- HTTP status codes
- Success/failure rates
- Uptime statistics

---

## 🚨 Troubleshooting

### Common Issues

**1. Can't login with admin/admin123**
```fish
# Reset user credentials
./stop.fish
rm users.json
./start.fish
```

**2. Server won't start (port in use)**
```fish
# Kill processes on port 8000
lsof -ti:8000 | xargs kill -9
./start.fish
```

**3. Email notifications not working**
- Check if `.env` file exists: `ls -la .env`
- Verify Mailgun credentials in `.env` file
- Get API key from: https://app.mailgun.com/app/account/security/api_keys
- Test with `php monitor.php` and check logs

**4. Monitors not updating**
- Check `monitor.log` for errors
- Verify URL format (include http/https)
- Run manual check: `php monitor.php`

### Log Files
- `server.log` - PHP server logs
- `monitor.log` - Monitoring script logs
- `alerts.json` - Alert history

---

## 📈 System Requirements

- **PHP**: 8.0 or higher
- **Extensions**: cURL, JSON, Session
- **Memory**: Minimum 64MB PHP memory limit
- **Disk Space**: ~50MB for application and dependencies
- **Network**: Outbound HTTP/HTTPS access for monitoring

---

## 🎉 Quick Commands Reference

```fish
# Initial setup
cp .env.example .env
# Edit .env with your Mailgun credentials

# Start everything
./start.fish

# Stop everything  
./stop.fish

# Check monitors manually
php monitor.php

# Run tests
npm test

# Reset application
./stop.fish && rm users.json monitors.json alerts.json && ./start.fish
```

---

## 📞 Support

This is a minimal monitoring application designed for simplicity and reliability. 

**Key Features**:
- ✅ Real-time website monitoring
- ✅ Email notifications via Mailgun
- ✅ User authentication system
- ✅ Visual dashboard interface
- ✅ Automated cron monitoring
- ✅ Comprehensive test coverage

**Login**: `admin` / `admin123`  
**URL**: `http://localhost:8000`

---

*Last updated: June 10, 2025*
