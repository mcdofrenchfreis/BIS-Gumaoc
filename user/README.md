# User System Documentation

## Overview
This user system provides a complete user authentication and service management platform for the Barangay Gumaoc East E-Services System. Users can register, login, submit reports, and access various e-services.

## Features

### User Authentication
- User registration with validation
- Secure login system
- Password hashing for security
- Session management

### Reports Management
- Submit incident reports
- Track report status
- View report history
- Priority levels (Low, Medium, High)
- Status tracking (Pending, Processing, Completed, Rejected)

### E-Services Portal
- Access to all available services
- Document requests
- Business applications
- Community services
- Emergency services
- Infrastructure requests
- Health services (coming soon)
- Social services (coming soon)
- Events & activities

## File Structure

```
user/
├── login.php              # User login page
├── register.php           # User registration page
├── dashboard.php          # User dashboard
├── reports.php            # Reports management page
├── e-services.php         # E-services portal page
├── auth_check.php         # Authentication middleware
├── logout.php             # Logout functionality
├── setup_user_db.php      # Database setup script
└── README.md              # This documentation
```

## Database Tables

### users
- id (Primary Key)
- full_name
- email (Unique)
- password (Hashed)
- phone
- address
- created_at
- updated_at

### user_reports
- id (Primary Key)
- user_id (Foreign Key)
- incident_type
- location
- description
- priority
- contact_number
- status
- admin_notes
- created_at
- updated_at

### user_service_requests
- id (Primary Key)
- user_id (Foreign Key)
- service_type
- request_details
- status
- admin_notes
- created_at
- updated_at

## Setup Instructions

1. **Database Setup**
   - Run `setup_user_db.php` to create necessary tables
   - This will create all required tables and a sample user

2. **Sample User Credentials**
   - Email: user@example.com
   - Password: user123

3. **Access the System**
   - Navigate to `user/login.php` to access the user login
   - Or click "User Login" in the main navigation

## User Flow

1. **Registration**: New users can register with their details
2. **Login**: Users authenticate with email and password
3. **Dashboard**: Overview of user's activities and quick access to services
4. **Reports**: Submit and track incident reports
5. **E-Services**: Access various electronic services
6. **Logout**: Secure session termination

## Security Features

- Password hashing using PHP's password_hash()
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention with prepared statements
- XSS protection with htmlspecialchars()

## Integration

The user system integrates with the existing barangay system:
- Links to existing forms and services
- Consistent design and styling
- Shared database connection
- Unified navigation structure

## Future Enhancements

- Email verification for new registrations
- Password reset functionality
- Profile management
- Notification system
- Mobile app integration
- Advanced reporting features
- Service tracking and history
- Payment integration for service fees

## Support

For technical support or questions about the user system, please contact the system administrator. 