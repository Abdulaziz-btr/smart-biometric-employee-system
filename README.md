# Smart-Based Employee Entrance Detector 🏢🔗

A comprehensive IoT and Web-based Employee Management System developed as a case study for **AVATA Trading Ltd**. This system replaces traditional manual attendance with an automated biometric (fingerprint) entrance detector, seamlessly integrating hardware with a PHP/MySQL management dashboard.

## 🚀 Key Features
* **Biometric IoT Attendance:** ESP32 and Adafruit Fingerprint sensor integration for secure, spoof-proof employee check-ins via a custom REST API.
* **Dual-Portal Access:** * **Admin Portal:** Manage employees, oversee daily sales reports, approve absences, track attendance, and generate automated payroll/salary summaries.
  * **Employee Portal:** View personal attendance logs, track overtime, submit daily sales targets, and request absences using a secure OTP-based login system.
* **Automated Payroll:** Automatically calculates Net Salary based on daily rates, days present, approved absences, and overtime hours.
* **Real-time Performance Reporting:** Track employee performance against daily sales targets with visual status indicators.

## 🛠️ Tech Stack
* **IoT Hardware:** ESP32 Microcontroller, Adafruit Fingerprint Sensor, 16x2 I2C LCD.
* **Hardware Firmware:** C++ (Arduino IDE)
* **Backend:** PHP 8 (Procedural MySQLi)
* **Database:** MySQL
* **Frontend:** HTML5, CSS3 (Custom responsive UI matching modern dashboard aesthetics)

## ⚙️ Installation & Setup (Local Server)

1. **Start Local Server:** Install and start XAMPP or WAMP (ensure Apache and MySQL are running).
2. **Clone Project:** Extract this repository into your `htdocs` (or `www`) directory inside a folder named exactly `smart_employee`.
3. **Database Setup:** * Open phpMyAdmin and create a database named `smart_employee`.
   * Import the provided `setup.sql` file to build the tables and insert default employee data.
4. **Access the System:**
   * **Admin Portal:** `http://localhost/smart_employee/admin_login.php`
     * *Default Credentials:* `admin` / `admin123`
   * **Employee Portal:** `http://localhost/smart_employee/login.php`
     * *Login Method:* Uses Email/Fingerprint ID to generate an OTP for secure access.

## 🔌 Hardware Setup (ESP32)
1. Wire the Adafruit Fingerprint Sensor to the ESP32 (RX to Pin 2, TX to Pin 4).
2. Connect the 16x2 I2C LCD.
3. Open `signing attendency on google sheet.cpp.txt` in the Arduino IDE.
4. Update the WiFi credentials (`ssid` and `password`).
5. Update the API endpoint URL to point to your local machine's IP address (e.g., `http://192.168.x.x/smart_employee/api/checkin.php`).
6. Flash the ESP32.

## 🔒 Troubleshooting & Recovery
If you are ever locked out of the Admin account, run the included `fix_admin.php` script in your browser to forcefully reset the credentials to the default, then delete the file immediately for security.

## 🎓 Academic Context
Developed as a Case Study for AVATA Trading Ltd by **Butera Abdulaziz** at Mount Kigali University.
