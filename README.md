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
2. **Install dependencies**:
   ```fish
   composer install
   npm install  # for testing (optional)
   ```
3. **Configure email** (optional):
   - Set `MAILGUN_API_KEY` and `MAILGUN_DOMAIN` environment variables
   - Or edit values directly in `config.php`
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

### Email Setup (Mailgun)

1. **Environment Variables** (recommended):
   ```fish
   export MAILGUN_API_KEY="your-api-key"
   export MAILGUN_DOMAIN="your-domain.com"
   ```

2. **Direct Configuration**:
   Edit `config.php`:
   ```php
   define('MAILGUN_API_KEY', 'your-api-key');
   define('MAILGUN_DOMAIN', 'your-domain.com');
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

### Data Files
- `users.json` - User credentials (JSON)
- `monitors.json` - Monitor configurations
- `alerts.json` - Alert history and tracking

### Scripts
- `start.fish` / `start.sh` - Application startup scripts
- `stop.fish` / `stop.sh` - Server stop scripts
- `package.json` - NPM scripts configuration

### Documentation
- `README.md` - This file
- `TEST.md` - Testing documentation

---

## 🧪 Testing

### Run Test Suite

```fish
# All tests
npm test

# Headed mode (visible browser)
npm run test:headed

# Interactive UI mode
npm run test:ui
```

### Test Categories

- **Authentication Tests**: Login/logout/session handling
- **Monitor Configuration**: Adding/validating monitors
- **Email Alerts**: Notification system testing
- **Cron Monitoring**: Automated checking functionality
- **UI Interface**: User interface validation

**Test Results**: 35/35 tests passing ✅

---

## 🔧 Development

### Available NPM Scripts

```fish
npm start          # Start application with browser
npm stop           # Stop the server
npm run dev        # Development mode (same as start)
npm run server     # PHP server only (no browser)
npm run monitor    # Run monitoring check
npm run test       # Run all tests
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
- Check Mailgun API credentials
- Verify email configuration in `config.php`
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
