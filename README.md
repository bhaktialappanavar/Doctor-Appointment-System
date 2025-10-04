# MediCare - Complete Doctor Appointment System

## 🏥 **Professional Healthcare Management System**

A comprehensive, secure doctor appointment system with role-based access for patients, doctors, and administrators. Built with modern security practices and professional healthcare workflows.

## 🚀 **Quick Setup**

### **1. Database Setup**
```sql
-- Create database and import schema
CREATE DATABASE doc_appointment_pro;
mysql -u root -p doc_appointment_pro < professional_database.sql
```

### **2. XAMPP Configuration**
1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Copy project to `C:\xampp\htdocs\medicare\`
3. Access: `http://localhost/medicare/`
4. Ensure PHP 7.4+ and MySQL 5.7+ are running

### **3. Default Login Credentials**
```
Admin:  admin@hospital.com / admin123
Doctor: dr.smith@hospital.com / admin123  
Patient: patient@email.com / admin123
```

## 👥 **User Roles & Features**

### **🩺 Patient Features**
- ✅ **Dashboard** - Appointment statistics and recent appointments overview
- ✅ **Book Appointments** - Interactive doctor search with specialty filtering
- ✅ **My Appointments** - Complete appointment history with status tracking
- ✅ **Medical Records** - View diagnosis, treatments, and prescription history
- ✅ **Health Reports** - Upload and manage lab reports, X-rays, PDFs (max 5MB)
- ✅ **Download Prescriptions** - PDF format prescription downloads
- ✅ **Find Doctors** - Advanced search and filter by specialty/name/availability
- ✅ **Profile Management** - Edit profile with photo upload and contact details

### **👨⚕️ Doctor Features**
- ✅ **Dashboard** - Patient statistics and daily appointment overview
- ✅ **My Appointments** - View, update status, and manage daily schedule
- ✅ **My Patients** - Complete patient database with search functionality
- ✅ **Patient Profiles** - Comprehensive medical history and health reports
- ✅ **Add Medical Records** - Create detailed diagnosis, treatment plans, prescriptions
- ✅ **Prescription Templates** - Save and reuse common prescription templates
- ✅ **Doctor Availability** - Weekly schedule management with unavailable day marking
- ✅ **Profile Management** - Update qualifications, specialization, and consultation fees

### **👨💼 Admin Features**
- ✅ **System Dashboard** - Complete analytics with user and appointment statistics
- ✅ **Manage Users** - View, activate/deactivate, and delete user accounts
- ✅ **Manage Doctors** - Approve/verify doctor registrations and profiles
- ✅ **Manage Patients** - Patient overview with detailed statistics
- ✅ **View Appointments** - System-wide appointment monitoring and status updates
- ✅ **System Reports** - Revenue tracking, user analytics, and top doctor reports

## 🔒 **Security Features**

- ✅ **Role-based Access Control** - Strict Patient/Doctor/Admin permissions
- ✅ **Secure Admin Access** - No public admin registration (database-level security)
- ✅ **CSRF Protection** - Token-based form security for all submissions
- ✅ **SQL Injection Prevention** - PDO prepared statements throughout
- ✅ **XSS Protection** - Comprehensive input sanitization and output encoding
- ✅ **Secure Password Hashing** - PHP password_hash() with strong algorithms
- ✅ **File Upload Security** - Type validation, size limits, and secure storage
- ✅ **Session Security** - Secure session management with regeneration
- ✅ **Input Validation** - Server-side validation for all user inputs
- ✅ **Error Handling** - Secure error messages without information disclosure

## 💻 **Technical Stack**

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+ with optimized schema
- **Frontend**: Bootstrap 5.3, Font Awesome 6.0, Responsive Design
- **Server**: Apache 2.4+ (XAMPP recommended)
- **Security**: PDO prepared statements, CSRF tokens, Input validation, XSS protection
- **File Handling**: Secure uploads with validation
- **PDF Generation**: Built-in prescription PDF system

## 📁 **File Structure**

```
medicare/
├── config.php                    # Main configuration & security functions
├── professional_database.sql     # Complete database schema
├── index.php                     # Professional homepage
├── login.php / register.php      # Secure authentication
├── logout.php                    # Session cleanup
├── dashboard.php                 # Role-based dashboard router
├── patient_dashboard.php         # Patient interface
├── doctor_dashboard.php          # Doctor interface  
├── admin_dashboard.php           # Admin control panel
├── book_appointment.php          # Interactive appointment booking
├── appointments.php              # Appointment management
├── patients.php                  # Doctor's patient list
├── schedule.php                  # Doctor's schedule view
├── profile.php                   # User profile management
├── medical_records.php           # Patient medical history
├── health_reports.php            # Health report uploads
├── prescription_templates.php    # Doctor prescription templates
├── doctor_availability.php       # Weekly availability management
├── manage_users.php              # Admin user management
├── manage_doctors.php            # Admin doctor verification
├── manage_patients.php           # Admin patient overview
├── view_appointments.php         # Admin appointment monitoring
├── reports.php                   # Admin system reports
├── download_prescription.php     # PDF prescription generation
└── uploads/                      # Secure file storage (photos, reports)
```

## 🌟 **Key Features**

### **Advanced Appointment Management**
- Interactive booking with real-time doctor search and filtering
- Specialty-based doctor discovery with availability checking
- Comprehensive status tracking (scheduled/completed/cancelled/no-show)
- Admin-level appointment monitoring and management
- Consultation type selection (consultation/follow-up/emergency)

### **Complete Medical Records System**
- Comprehensive patient medical history tracking
- Doctor-created diagnosis and treatment records
- Professional PDF prescription generation with templates
- Health report uploads (lab reports, X-rays, medical documents)
- Prescription template system for doctors

### **Professional Healthcare Features**
- Doctor verification and approval system
- Weekly availability scheduling with unavailable day marking
- Patient profile management with complete medical history
- Revenue tracking and comprehensive system reports
- Profile photo uploads for all users

### **Indian Healthcare Context**
- ₹ (Indian Rupee) currency support throughout
- Local medical specialties (Cardiology, Dermatology, etc.)
- Indian healthcare workflow and terminology
- Professional consultation fee management

## 🛠 **Installation Steps**

1. **Download XAMPP** (PHP 7.4+) and start Apache + MySQL services
2. **Create database**: `doc_appointment_pro` in phpMyAdmin
3. **Import SQL schema**: Run `professional_database.sql` in the database
4. **Copy project files** to `C:\xampp\htdocs\medicare\`
5. **Set permissions**: Ensure `uploads/` directory is writable
6. **Access system**: Navigate to `http://localhost/medicare/`
7. **Test login** with default credentials provided above
8. **Configure**: Update `config.php` if needed for custom settings

## 🔐 **Admin Access & Security**

**Secure Admin Setup:**
- ✅ **No public admin registration** - Admins cannot be created through public registration
- ✅ **Default admin account** - Use `admin@hospital.com` / `admin123`
- ✅ **Database-level security** - Admin accounts must be created manually in database
- ✅ **Full system control** - Complete user, doctor, and patient management
- ✅ **System monitoring** - Comprehensive appointment and revenue tracking

**User Registration Security:**
- **Patients** - Can register publicly with email verification
- **Doctors** - Can register but require admin approval and verification
- **Admins** - Cannot register publicly (security feature - database creation only)

**Additional Security Measures:**
- Role-based access control with strict permission checking
- CSRF protection on all forms and sensitive operations
- Secure file upload handling with type and size validation
- Session security with proper timeout and regeneration

## 📱 **Responsive Design & UI/UX**

- ✅ **Mobile-first Bootstrap 5.3** responsive interface
- ✅ **Professional healthcare theme** with medical color scheme
- ✅ **Role-specific navigation** tailored for patients, doctors, and admins
- ✅ **Clean, modern UI/UX** with intuitive user flows
- ✅ **Accessibility compliant** design with proper contrast and navigation
- ✅ **Font Awesome 6.0 icons** for enhanced visual experience
- ✅ **Interactive elements** with proper feedback and validation

## 🔧 **System Requirements**

**Server Requirements:**
- **PHP**: 7.4 or higher (with PDO MySQL extension)
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Apache**: 2.4 or higher (with mod_rewrite enabled)
- **Disk Space**: Minimum 100MB (more for file uploads)

**Client Requirements:**
- **Browser**: Modern browser with JavaScript enabled
- **Supported Browsers**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Internet Connection**: Required for CDN resources (Bootstrap, Font Awesome)

## 📞 **Support & Troubleshooting**

**Common Issues:**
- **Database Connection**: Verify MySQL service is running and credentials in `config.php`
- **File Permissions**: Ensure `uploads/` directory has write permissions
- **PHP Extensions**: Verify PDO MySQL extension is enabled
- **XAMPP Issues**: Check Apache and MySQL status in XAMPP Control Panel

**Configuration:**
- Database settings in `config.php`
- Upload limits and security settings
- Session configuration and timeout settings
- Error reporting levels for development/production

**Logs & Debugging:**
- PHP error logs in XAMPP logs directory
- Apache access and error logs
- Database query logs for performance monitoring

## 🚀 **Recent Updates & Features**

**Latest Version Includes:**
- ✅ **Enhanced Security**: Comprehensive CSRF protection and input validation
- ✅ **File Management**: Secure photo uploads and health report management
- ✅ **PDF Generation**: Professional prescription PDF downloads
- ✅ **Admin Panel**: Complete user and system management
- ✅ **Doctor Tools**: Prescription templates and availability scheduling
- ✅ **Patient Portal**: Health reports upload and medical history access
- ✅ **Responsive Design**: Mobile-optimized Bootstrap 5.3 interface

## 📄 **License**

This project is open-source and available for educational and commercial use.

---

**MediCare** - Professional Healthcare Management System  
*Secure • Comprehensive • Professional • User-Friendly*

**Built with modern security practices and professional healthcare workflows in mind.**