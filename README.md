# 🔍 Uptime Monitor - Automated Web Monitoring System

> **Purpose**: Automated web monitoring application that continuously monitors website availability and sends email notifications when sites go down/up. Includes user authentication and real-time dashboard.

> **🚀 Quick Start**: One command to start everything - web interface + automatic monitoring!

---

## 🚀 Quick Start

### 🔧 First Time Setup

**1. Clone and Setup:**
```bash
git clone <repository-url>
cd uptime-monitor1
./setup.sh  # Creates necessary data files
```

**2. Configure Environment:**
```bash
# Edit .env file with your Brevo API key
nano .env  # or your preferred editor
```

### ⚡ Automatic System (Recommended)

**Start Complete System (Web + Monitoring):**
```bash
# Fish Shell (automatic web server + monitoring)
./start.fish

# Access dashboard: http://localhost:8000
# Login: admin / admin123
# Monitoring runs automatically every 30 seconds
```

**Stop Everything:**
```bash
./stop.fish
# Or press Ctrl+C in terminal
```

### 🎯 What You Get:

- **🌐 Web Dashboard**: http://localhost:8000 (auto-refreshes every 30s)
- **� Automatic Monitoring**: Checks every 30 seconds automatically  
- **📧 Email Alerts**: Sent only on status changes (UP ↔ DOWN)
- **📊 Real-time Logs**: See monitoring activity in terminal
- **🕐 Estonian Time**: All timestamps in Europe/Tallinn timezone

### 📋 Default Login:
- **Username**: `admin`
- **Password**: `admin123`

---






## 🛠️ Installation & Setup

### Prerequisites

- PHP 8.0+ with cURL extension
- Composer (for dependencies)  
- Fish shell (or Bash as fallback)

### Quick Setup

1. **Clone Repository**
```bash
git clone <repository-url>
cd uptime-monitor1
```

2. **Install Dependencies**
```bash
composer install
```

3. **Configure Email (Optional)**
```bash
# Copy example configuration
cp .env.example .env

# Edit .env with your Brevo settings
nano .env
```

4. **Start System**
```bash
./start.fish
```

5. **Access Dashboard**
- Open: http://localhost:8000
- Login: admin / admin123
- Add your websites to monitor

### 🔧 Configuration

**Email Setup (Brevo):**
- Get free API key from [Brevo.com](https://brevo.com)
- Update `.env` file with your credentials
- See [BREVO_SETUP.md](BREVO_SETUP.md) for detailed setup

**Without Email:**
- System works without email configuration
- Monitoring still functions, just no notifications sent
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

### 1. Starting the System

```bash
./start.fish
```

**What happens:**
- ✅ Starts web server on http://localhost:8000
- ✅ Starts automatic monitoring (30-second intervals)
- ✅ Opens browser automatically
- ✅ Shows real-time logs in terminal

### 2. Stopping the System

```bash
./stop.fish
# Or press Ctrl+C in the terminal
```

### 3. Using the Dashboard

1. **Login**: http://localhost:8000
   - Username: `admin`
   - Password: `admin123`

2. **Add Website to Monitor**:
   - Enter URL (e.g., `https://example.com`)
   - Enter your email address
   - Click "Add Monitor"

3. **Monitor Status**:
   - **🟢 GREEN**: Website is UP and responding
   - **🔴 RED**: Website is DOWN or not responding
   - **Auto-refresh**: Dashboard updates every 30 seconds

### 4. Email Notifications

**When you receive emails:**
- ❌ **DOWN alert**: When website goes offline
- ✅ **UP alert**: When website comes back online

**Email features:**
- Professional templates with HTML/text versions
- Estonian timezone (EET/EEST)
- Anti-spam optimized headers
- Delivered via Brevo (SendinBlue)

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
   BREVO_API_KEY=your-actual-brevo-api-key
   BREVO_FROM_EMAIL=noreply@yourdomain.com
   BREVO_FROM_NAME="Your Monitor Name"
   
   # Optional customizations
   ADMIN_EMAIL=your-admin@email.com
   MONITOR_TIMEOUT=10
   ```

### Email Setup (Brevo)

1. **Get Brevo credentials**:
   - API Key: https://app.brevo.com/settings/keys/api
   - Verified sender email: https://app.brevo.com/senders/domain/new

2. **Add to .env file**:
   ```bash
   BREVO_API_KEY=xkeysib-your-api-key-here
   BREVO_FROM_EMAIL=noreply@yourdomain.com
   BREVO_FROM_NAME="Uptime Monitor"
   ```

### Alternative: Environment Variables

You can also set environment variables directly:
```fish
export BREVO_API_KEY="your-api-key"
export BREVO_FROM_EMAIL="noreply@yourdomain.com"
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

## 📋 Quick Reference

### Essential Commands

```bash
# Start complete system (web + monitoring)
./start.fish

# Stop everything
./stop.fish

# Manual monitoring check (optional)
php monitor.php
```

### URLs & Login

- **Dashboard**: http://localhost:8000
- **Username**: `admin`
- **Password**: `admin123`

### Log Files

- **Auto-monitor**: `auto-monitor.log` - Monitoring activity
- **Web server**: `server.log` - Web server access logs
- **Monitor**: `monitor.log` - Detailed monitoring logs

### Configuration Files

- **`.env`** - Email API keys and settings
- **`monitors.json`** - List of monitored websites
- **`monitor_status.json`** - Current status cache
- **`alerts.json`** - Alert history

### Email Status

- ✅ **Emails working**: Check `.env` for Brevo API key
- ❌ **No emails**: System still monitors, just no notifications
- 📧 **Test email**: `php test_brevo.php`

---

## � Additional Documentation

- **[📧 Anti-Spam Email Guide](ANTI_SPAM_GUIDE.md)** - Detailed guide to prevent emails from going to spam
- **[🔧 Brevo Setup Guide](BREVO_SETUP.md)** - Email service configuration
- **[⚡ Auto-Monitor Guide](AUTO_MONITOR_GUIDE.md)** - Automated monitoring setup
- **[🧹 Data Cleanup Guide](DATA_CLEANUP_GUIDE.md)** - Data management and cleanup
- **[🕐 Timezone Guide](TIMEZONE_FIXED.md)** - Estonian timezone configuration


**Login**: `admin` / `admin123`  
**URL**: `http://localhost:8000`

---

*Last updated: June 10, 2025*
