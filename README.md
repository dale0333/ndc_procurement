# Procurement Management System v2.0

A comprehensive, production-ready procurement management system built with PHP, MySQL, and modern UI/UX design. Features complete workflow tracking, user authentication, role-based access control, analytics dashboard, and audit trails.

## 🌟 Features

### Core Functionality
- **7-Stage Procurement Workflow**:
  1. Budget Formulation
  2. Budget Review & Approval
  3. Procurement Planning
  4. Contract Execution
  5. Disbursement & Payment
  6. Monitoring & Reporting
  7. Audit & Feedback

### Advanced Features
- ✅ **User Authentication & Authorization** - Secure login with role-based access control
- ✅ **Role-Based Permissions** - 6 different user roles (Admin, Budget Officer, Procurement Officer, etc.)
- ✅ **Dashboard Analytics** - Real-time statistics and charts
- ✅ **Activity Logging** - Complete audit trail of all actions
- ✅ **Comments System** - Collaborative commenting on procurements
- ✅ **Document Management** - Upload and track documents
- ✅ **Notifications** - User notifications for important events
- ✅ **Modern UI/UX** - Responsive design with Tailwind CSS
- ✅ **Data Visualization** - Charts using Chart.js

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server
- Composer (optional, for future dependencies)

## 🚀 Installation

### Step 1: Database Setup

1. **Create the database:**
   ```bash
   mysql -u root -p
   ```

2. **Run the schema file:**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

   Or manually:
   ```sql
   CREATE DATABASE procurement_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE procurement_system;
   SOURCE database/schema.sql;
   ```

### Step 2: Configure Database Connection

Edit `config/config.php` and update database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'procurement_system');
define('DB_USER', 'root');           // Change this
define('DB_PASS', 'your_password');  // Change this
```

### Step 3: Set File Permissions

```bash
# Create uploads directory
mkdir uploads
chmod 755 uploads

# Ensure PHP can write to session directory
chmod 755 /tmp
```

### Step 4: Start the Application

**Option A: PHP Built-in Server (Development)**
```bash
php -S localhost:8000
```

**Option B: Apache/Nginx (Production)**
- Point document root to the application directory
- Configure virtual host with proper PHP handling
- Enable mod_rewrite (Apache) or equivalent

### Step 5: Access the Application

Open your browser and navigate to:
- Development: `http://localhost:8000`
- Production: Your configured domain

## 🔐 Default Login Credentials

### Administrator
- Username: `admin`
- Password: `admin123`

### Budget Officer
- Username: `budget_officer`
- Password: `admin123`

### Procurement Officer
- Username: `procurement`
- Password: `admin123`

**⚠️ IMPORTANT:** Change these passwords immediately after first login in production!

## 📁 Project Structure

```
procurement-system/
│
├── config/
│   └── config.php              # Application & database configuration
│
├── database/
│   └── schema.sql              # Database schema and initial data
│
├── includes/
│   ├── Auth.php                # Authentication class
│   ├── Database.php            # Database connection class
│   ├── Procurement.php         # Procurement business logic
│   └── helpers.php             # Helper functions
│
├── uploads/                    # File uploads directory
│
├── login.php                   # Login page
├── logout.php                  # Logout handler
├── dashboard.php               # Main dashboard
├── budget-formulation.php      # Stage 1
├── view-details.php           # View procurement details
└── README.md                  # This file
```

## 🎨 Design & Technology Stack

- **Frontend**: Tailwind CSS 3.x
- **Typography**: Space Grotesk (headings) + Outfit (body)
- **Charts**: Chart.js
- **Icons**: Heroicons (SVG)
- **Backend**: PHP 7.4+
- **Database**: MySQL with PDO
- **Architecture**: MVC-inspired with separation of concerns

## 👥 User Roles & Permissions

| Role | Permissions |
|------|-------------|
| **Admin** | Full access to all stages and system management |
| **Department Head** | Create procurements, view departmental data |
| **Budget Officer** | Budget formulation and review stages |
| **Procurement Officer** | Procurement planning and contract execution |
| **Finance Officer** | Disbursement and payment processing |
| **Auditor** | Monitoring and audit stages |

## 🔄 Workflow Process

1. **Create Procurement** → Department head creates request
2. **Budget Formulation** → Define budget and justification
3. **Budget Review** → Budget officer reviews and approves
4. **Procurement Planning** → Plan procurement method and specifications
5. **Contract Execution** → Award contract and set terms
6. **Disbursement** → Process payments to contractor
7. **Monitoring** → Track progress and deliverables
8. **Audit** → Final compliance check and completion

## 📊 Database Schema

### Main Tables:
- `users` - User accounts and authentication
- `procurements` - Main procurement records
- `budget_formulation` - Stage 1 data
- `budget_review` - Stage 2 data
- `procurement_planning` - Stage 3 data
- `contract_execution` - Stage 4 data
- `disbursements` - Stage 5 data
- `monitoring_reports` - Stage 6 data
- `audits` - Stage 7 data
- `activity_logs` - Audit trail
- `comments` - Collaboration
- `documents` - File attachments
- `notifications` - User notifications

## 🔧 Configuration Options

Edit `config/config.php` to customize:

```php
// Application Settings
define('APP_NAME', 'Procurement Hub');
define('APP_URL', 'http://localhost:8000');

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 10485760); // 10MB

// Security Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enable in production with HTTPS
```

## 🛡️ Security Features

- ✅ **Password Hashing** - bcrypt with PHP's password_hash()
- ✅ **SQL Injection Protection** - PDO prepared statements
- ✅ **XSS Prevention** - htmlspecialchars() on all output
- ✅ **CSRF Protection** - Session-based tokens (implement for production)
- ✅ **Input Sanitization** - Server-side validation
- ✅ **Activity Logging** - Complete audit trail with IP tracking

## 📈 Analytics Dashboard

The dashboard provides:
- Total procurements count
- Active/In-progress count
- Completed procurements
- Total procurement value
- Distribution by stage (donut chart)
- Distribution by department (bar chart)

## 🐛 Troubleshooting

### Database Connection Errors
- Verify MySQL is running: `sudo systemctl status mysql`
- Check credentials in `config/config.php`
- Ensure database exists: `SHOW DATABASES;`

### Permission Errors
- Check file permissions: `ls -la uploads/`
- Ensure web server can write: `chmod 755 uploads/`

### Session Issues
- Check PHP session settings: `php -i | grep session`
- Clear session data: Delete files in `/tmp/sess_*`

### Login Not Working
- Verify user exists: `SELECT * FROM users WHERE username='admin';`
- Check password hash is valid
- Clear browser cookies/cache

## 📝 Quick Start Guide

1. **Clone/Download** the project
2. **Run database setup**: `mysql -u root -p < database/schema.sql`
3. **Configure** `config/config.php` with your database credentials
4. **Create uploads folder**: `mkdir uploads && chmod 755 uploads`
5. **Start server**: `php -S localhost:8000`
6. **Login** with `admin` / `admin123`
7. **Create** your first procurement request

## 📄 License

This is a demonstration/educational project. Modify as needed for your organization.

---

**Version**: 2.0  
**Last Updated**: February 2025  
**Built with**: PHP + MySQL + Tailwind CSS + Chart.js
# ndc_procurement
