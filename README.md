# ✅ Web Monitoring Application – Minimal Issue Set

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

- [ ] **Issue #4**: Send email alerts when monitored site is down  
  - [ ] Compose meaningful message with URL and timestamp  
  - [ ] Use PHP `mail()` or similar method  
  - [ ] Ensure email is only sent once per incident  

---

## 🖥️ Minimal Interface

- [ ] **Issue #5**: Build a simple user interface for login and URL submission  
  - [ ] Use plain HTML (optional minimal CSS)  
  - [ ] Ensure clarity without advanced styling  
  - [ ] No JavaScript required unless essential  

---

## 📁 Deployment

- [ ] **Issue #6**: Prepare application for server deployment  
  - [ ] PHP script runnable from cron (`*/5 * * * *`)  
  - [ ] Confirm compatibility with Linux environment (Apache/Nginx)  
  - [ ] Ensure necessary permissions for file reading/writing  