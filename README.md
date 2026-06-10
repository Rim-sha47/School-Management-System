# 🏫 Providence School Management System

A **full-featured, web-based School Management System** built with PHP, MySQL, Bootstrap 5, and modern vanilla CSS. Designed for schools to manage all their daily operations — students, teachers, fees, attendance, results, timetables, library, transport, and much more — through a clean, responsive, and premium-looking admin interface.

---

## ✨ Features

### 🔐 Authentication & Role-Based Access
- Secure login system with session-based authentication
- Three roles: **Super Admin**, **Admin**, **Teacher**, **Student**
- Each role sees only their relevant portal and features

---

### 🖥️ Admin Portal
Full CRUD (Create, Read, Update, Delete) operations across every module:

| Module | Features |
|---|---|
| **Dashboard** | Live stats, recent students, notices, quick actions |
| **Students** | Add, edit, remove students; manage class assignments |
| **Teachers** | Full teacher profile management |
| **Classes & Sections** | Manage class groups and sections |
| **Subjects** | Add and organize subjects per class |
| **Timetable** | Schedule classes by day/time with teacher assignment |
| **Attendance** | Mark daily attendance with Present / Absent / Late / Leave |
| **Exam Results** | Enter marks per subject/exam; auto grade calculation |
| **Fees & Payments** | Fee structures, collect payments, track status |
| **Noticeboard** | Post and manage notices with priority levels |
| **Events** | Create and manage school calendar events |
| **Library** | Book inventory, issue & return tracking |
| **Transport** | Manage routes and vehicles |
| **Parents** | Parent contact management |
| **Reports** | Overview reports and data summaries |
| **Settings** | School-wide configuration |

---

### 👨‍🏫 Teacher Portal
- Personalized dashboard with class summary and attendance
- View assigned classes and timetable
- Mark and submit student attendance
- Manage assignments and view results
- Access school notices

---

### 🎓 Student Portal
- Personal academic dashboard
- View attendance record and subject-wise performance charts
- Pending assignments tracker
- Syllabus progress overview
- Access notices and timetable

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8+ (PDO for database) |
| **Database** | MySQL 5.7+ |
| **Frontend** | HTML5, Vanilla CSS, JavaScript (ES6+) |
| **UI Framework** | Bootstrap 5.3 |
| **Icons** | FontAwesome 6 |
| **Charts** | Chart.js |
| **Fonts** | Google Fonts — Outfit |
| **Server** | Apache via XAMPP |

---

## 📁 Project Structure

```
School_Management_System/
│
├── index.html              # Landing page
├── login.html              # Login page
├── login_action.php        # Login authentication handler
├── logout.php              # Session logout
├── config.php              # Database connection (PDO)
├── database.sql            # Full database schema & seed data
├── setup_db.php            # DB setup helper script
│
├── admin/                  # Admin portal
│   ├── dashboard.php
│   ├── students.php
│   ├── teachers.php
│   ├── classes.php
│   ├── subjects.php
│   ├── timetable.php
│   ├── attendance.php
│   ├── results.php
│   ├── fees.php
│   ├── exams.php
│   ├── noticeboard.php
│   ├── events.php
│   ├── library.php
│   ├── transport.php
│   ├── parents.php
│   ├── reports.php
│   ├── settings.php
│   ├── includes/           # Shared partials (sidebar, topbar, header)
│   └── *_action.php        # AJAX action handlers for each module
│
├── teacher/                # Teacher portal
│   ├── dashboard.php
│   ├── classes.php
│   ├── attendance.php
│   ├── assignments.php
│   ├── results.php
│   ├── timetable.php
│   ├── notices.php
│   └── includes/
│
├── student/                # Student portal
│   ├── dashboard.php
│   └── includes/
│
└── assets/                 # Static assets
    ├── css/
    │   ├── style.css       # Global design tokens & variables
    │   ├── landing.css     # Landing page styles
    │   └── dashboard.css   # Admin/portal shared styles
    └── js/
        ├── admin.js        # Admin AJAX & interactivity
        ├── teacher.js
        └── student.js
```

---

## 🚀 Installation Guide

### Prerequisites
- **XAMPP** (or any Apache + PHP + MySQL stack)
- PHP 8.0 or higher
- MySQL 5.7 or higher

### Step-by-Step Setup

**1. Clone or Copy the Project**
```bash
# Place the project folder inside your XAMPP htdocs directory
C:\xampp\htdocs\School_Management_System\
```

**2. Start XAMPP Services**
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

**3. Create the Database**
- Open your browser and go to: `http://localhost/phpmyadmin`
- Create a new database named: **`providence_school`**
- Select the database, click **Import**, and upload `database.sql`

**4. Configure Database Connection**

Open `config.php` and verify your credentials:
```php
$host     = 'localhost';
$dbname   = 'providence_school';
$username = 'root';    // Default XAMPP username
$password = '';        // Default XAMPP password is empty
```

**5. Run the Application**

Open your browser and navigate to:
```
http://localhost/School_Management_System/
```

---

## 🔑 Default Login Credentials

| Role | Username | Password |
|---|---|---|
| Super Admin | `superadmin` | `password` |
| Admin | `admin` | `password` |
| Teacher | `teacher1` | `password` |
| Student | `student1` | `password` |

> ⚠️ **Important:** Change all default passwords immediately after first login in a production environment.

---

## 📸 Portal Overview

| Portal | URL |
|---|---|
| Landing Page | `/index.html` |
| Login | `/login.html` |
| Admin Dashboard | `/admin/dashboard.php` |
| Teacher Dashboard | `/teacher/dashboard.php` |
| Student Dashboard | `/student/dashboard.php` |

---

## 🎨 Design System

The UI uses a consistent design system with CSS custom properties:

- **Primary Color:** Deep Indigo / Purple gradient
- **Font:** Outfit (Google Fonts)
- **Style:** Glassmorphism, soft shadows, smooth micro-animations
- **Responsive:** Mobile-first, fully responsive on all screen sizes
- **Dark-mode-ready:** CSS variable architecture supports theming

---

## 🔒 Security Notes

- All database queries use **PDO prepared statements** to prevent SQL injection
- Session-based authentication with role checks on every page
- User input is escaped with `htmlspecialchars()` before rendering
- Action scripts return JSON responses (no direct page access)

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

## 👨‍💻 Author

**Providence School Management System**  
Built with ❤️ using PHP + MySQL + Bootstrap 5

---

> 💡 **Tip:** For production deployment, remember to set `PDO::ERRMODE_EXCEPTION` to silent mode, use HTTPS, and secure your `config.php` file.
