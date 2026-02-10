# Quick Setup Guide - Procurement Management System

## 🚀 5-Minute Setup

Follow these steps to get your system running:

### 1. Extract Files
```bash
unzip procurement-system.zip
cd procurement-system
```

### 2. Setup Database

**Option A: Command Line**
```bash
mysql -u root -p < database/schema.sql
```

**Option B: phpMyAdmin**
1. Open phpMyAdmin
2. Create new database: `procurement_system`
3. Import `database/schema.sql`

**Option C: MySQL Workbench**
1. Open MySQL Workbench
2. File → Run SQL Script
3. Select `database/schema.sql`

### 3. Configure Database Connection

Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');      // Usually localhost
define('DB_NAME', 'procurement_system');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', 'your_password');  // Your MySQL password
```

### 4. Create Uploads Directory
```bash
mkdir uploads
chmod 755 uploads
```

Windows users can skip chmod.

### 5. Start the Server

```bash
php -S localhost:8000
```

### 6. Access the Application

Open: http://localhost:8000/login.php

**Login with:**
- Username: `admin`
- Password: `admin123`

## ✅ Verification Checklist

- [ ] MySQL is running
- [ ] Database `procurement_system` exists
- [ ] All tables created (check with `SHOW TABLES;`)
- [ ] config/config.php has correct credentials
- [ ] uploads/ directory exists
- [ ] PHP server is running
- [ ] Can access login page
- [ ] Can login with default credentials

## 🔧 Common Issues

### "Connection refused"
**Problem:** MySQL not running  
**Solution:** Start MySQL service
```bash
# Linux/Mac
sudo systemctl start mysql

# Windows
Start MySQL from Services or XAMPP/WAMP
```

### "Access denied for user"
**Problem:** Wrong database credentials  
**Solution:** Verify username/password in config/config.php

### "Database does not exist"
**Problem:** Schema not imported  
**Solution:** Run `mysql -u root -p < database/schema.sql`

### "Permission denied" on uploads
**Problem:** Directory permissions  
**Solution:** `chmod 755 uploads` or check web server user

### Blank page / errors
**Problem:** PHP errors  
**Solution:** Check `error_reporting` in config.php, review PHP error logs

## 🎯 Next Steps

After successful setup:

1. **Change default passwords** (Security Settings)
2. **Create test procurement** (New Procurement button)
3. **Explore dashboard** (View statistics and charts)
4. **Add users** (Admin panel)
5. **Customize** (Edit config, colors, departments)

## 📞 Need Help?

1. Check README.md for detailed documentation
2. Review database/schema.sql for table structure
3. Check includes/helpers.php for available functions
4. Examine error logs

## 🎉 You're Ready!

The system is now running. Start by creating your first procurement request!
