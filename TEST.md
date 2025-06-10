# ✅ Web Monitoring Application – Minimal

> Purpose: Create a minimal web application to monitor website availability, notify via email if down, and support user login. It must run on a server and execute checks automatically via cron. UI should be kept minimal. No extra features beyond requirements.

## 🚀 Quick Start

**Kiire alustamine:** [QUICK_START.md](QUICK_START.md)  
**Detailne juhend:** [USAGE_GUIDE.md](USAGE_GUIDE.md)

```bash
# 1. Käivita server
php -S localhost:8080

# 2. Ava brauser
open http://localhost:8080

# 3. Logi sisse: admin/admin
```

---

## 📧 Email Notifications

- [x] **Issue #4**: Send email alerts when monitored site is down  
  - [x] Compose meaningful message with URL and timestamp  
  - [x] Use PHP `mail()` or similar method (Mailgun implemented)  
  - [x] Ensure email is only sent once per incident  

---

> Purpose: Create a minimal web application to monitor website availability, notify via email if down, and support user login. It must run on a server and execute checks automatically via cron. UI should be kept minimal. No extra features beyond requirements.

---

## 🔐 User Authentication

- [x] **Issue #1**: Implement user login system  
  - [x] Create login form (username and password)  
  - [x] Authenticate user against credentials in a database or file  
  - [x] Maintain login state using PHP sessions  
  - [x] Add logout functionality  
  - [x] Show monitor form only when logged in  

---

## 🌐 Monitor Configuration

- [x] **Issue #2**: Add form to submit monitored URLs and notification emails  
  - [x] Input fields: URL, email address  
  - [x] Validate format of URL and email  
  - [x] Save to a data store (e.g. JSON file or database)  
  - [x] Prevent duplicate entries  

---

## 🕒 Automated Server Monitoring

- [x] **Issue #3**: Create a cron-compatible PHP script to check monitored URLs  
  - [x] Read all monitored URLs from storage  
  - [x] Make HTTP requests to each  
  - [x] Detect failures (e.g. connection error, non-200 response)  
  - [x] Log or report status  

---

## 📧 Email Notifications

- [x] **Issue #4**: Send email alerts when monitored site is down  
  - [x] Compose meaningful message with URL and timestamp  
  - [x] Use PHP `mail()` or similar method  
  - [x] Ensure email is only sent once per incident  

---

## 🖥️ Minimal Interface

- [x] **Issue #5**: Build a simple user interface for login and URL submission  
  - [x] Use plain HTML (optional minimal CSS)  
  - [x] Ensure clarity without advanced styling  
  - [x] No JavaScript required unless essential  

---
