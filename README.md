# ✈️ XetEviation - Aviation Management System

**XetEviation** is a web-based aviation management system developed using **PHP** and **MySQL**. It is designed to manage flights, passengers, bookings, and staff for an aviation service or educational purpose. This project provides a simple and scalable solution to simulate or manage aviation operations.

---

# About - XetEviation: Aviation System

**XetEviation** is a PHP and MySQL-based web application designed to streamline and manage aviation operations digitally. Built with scalability and modularity in mind, this system aims to assist airport authorities, airlines, or aviation-related agencies in efficiently managing flights, passengers, staff, and bookings through a secure and user-friendly interface.

### Key Objectives

- Digitize aviation management and minimize manual errors.
- Provide role-based access to administrators, staff, and users.
- Improve data visibility and accessibility across departments.
- Maintain records of flight operations, schedules, and crew assignments.

### Technologies Used

- **Backend**: PHP (OOP with PDO)
- **Frontend**: HTML5, CSS3, JavaScript (Bootstrap for responsive UI)
- **Database**: MySQL
- **Tools**: VS Code, phpMyAdmin

### Modules & Features

- User Authentication (Admin & Staff)
- Flight Scheduling & Management
- Passenger Registration
- Staff Management (Pilots, Ground Crew, etc.)
- Ticket Booking System
- Real-Time Flight Status Updates
- Reports & Logs

This system is ideal for small to mid-sized aviation organizations or educational projects simulating real-world aviation management.

---


## 🚀 Features

- 🔐 Secure Admin and Staff Login
- 🛫 Flight Scheduling and Management
- 🧍 Passenger Registration and Tracking
- 🧑‍✈️ Staff Management (Pilots, Crew, Ground Staff)
- 🎫 Ticket Booking System
- 📊 Report Generation
- 🔍 Search and Filter Functionality
- 🧩 Modular Code Structure (OOP and PDO)
- ⚙️ Easy to configure and deploy

---

## 🛠️ Built With

- **PHP** (with OOP and PDO for secure DB interaction)
- **MySQL** (Relational Database)
- **HTML5, CSS3, Bootstrap** (Frontend UI)
- **JavaScript** (Interactivity and validation)
- **phpMyAdmin** (Database management)

---

## 📁 Folder Structure

```bash
aviation-system_xeteviation/
├── config/             # Database and init config
├── public/             # Main UI (index.php, login.php, etc.)
├── admin/              # Admin Panel
├── staff/              # Staff Panel
├── includes/           # Shared components
├── assets/             # CSS, JS, images
├── sql/                # Sample database (import .sql)
└── README.md

⚙️ Installation

    Clone the repository

git clone https://github.com/esteham/aviation-system_xeteviation.git
cd aviation-system_xeteviation

Import the database

    Open phpMyAdmin or any MySQL interface.

    Create a new database (e.g., aviation_system).

    Import the SQL file located in /sql/aviation_system.sql.

Configure database connection

    Open /config/init.php

    Set your DB credentials:

        define('DB_HOST', 'localhost');
        define('DB_NAME', 'aviation_system');
        define('DB_USER', 'root');
        define('DB_PASS', '');

    Run the project

        Use a local server like XAMPP, Laragon, or MAMP.

        Navigate to http://localhost/aviation-system_xeteviation/public/ in your browser.

🔐 Default Admin Login (for demo/testing)

Username: admin
Password: admin123

(You can change these from the database or admin panel after login.)
📸 Screenshots

    (Add screenshots in /assets/screenshots/ and link them here using ![screenshot](assets/screenshots/1.png))

📜 License

This project is open-source and free to use under the MIT License.
🤝 Contributing

Pull requests are welcome! If you'd like to improve this system, feel free to fork and submit changes.
📧 Contact

Developer: Esteham H Zihad Ansari
GitHub: @esteham