# User System Documentation

## Overview
This user system provides a complete user authentication and service management platform for the Barangay Gumaoc East E-Services System. The system integrates with the main residents database and supports both email/password and RFID authentication.

## Features

### User Authentication
- **Email/Password Login**: Standard authentication using email and password
- **RFID Authentication**: Quick access using RFID cards
- **Integrated with Main Database**: Uses the central `residents` table
- **Profile Completion**: Automatic redirect for incomplete profiles
- **Password Reset**: Email and phone-based password recovery

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

## Authentication Methods

### Email/Password Login
- Users login with their email address and password
- Password can be reset using email and contact number verification
- Secure password hashing using PHP's password_hash()

### RFID Login
- Quick access using RFID cards
- Auto-submit when RFID code is detected
- Integrates with the main RFID system
- Fallback to manual RFID code entry

## File Structure

```
user/
├── login.php              # Dual authentication (Email/RFID)
├── register.php           # Redirects to census registration
├── dashboard.php          # User dashboard
├── reports.php            # Reports management page
├── e-services.php         # E-services portal page
├── auth_check.php         # Authentication middleware
├── logout.php             # Logout functionality
├── reset_password.php     # Password reset using email
├── setup_user_db.php      # Database setup script
└── README.md              # This documentation
```

## Database Integration

### Primary Table: `residents`
- id (Primary Key)
- first_name, middle_name, last_name
- email (Unique)
- phone (Contact number)
- password (Hashed)
- rfid_code, rfid (RFID identifiers)
- address, house_number
- status (active, inactive, pending)
- profile_complete (0/1)
- created_at, updated_at

### Supporting Tables

#### `user_reports`
- id (Primary Key)
- user_id (Foreign Key to residents.id)
- incident_type
- location
- description
- priority
- contact_number
- status
- admin_notes
- created_at, updated_at

#### `user_service_requests`
- id (Primary Key)
- user_id (Foreign Key to residents.id)
- service_type
- request_details
- status
- admin_notes
- created_at, updated_at

## Setup Instructions

1. **Database Setup**
   - Ensure the main `residents` table exists
   - Run `setup_user_db.php` to create supporting tables
   - The system will create user_reports and user_service_requests tables

2. **User Registration**
   - Users must complete the census registration process
   - This automatically creates accounts in the residents table
   - Login credentials are provided upon registration completion

3. **Access the System**
   - Navigate to `user/login.php` to access the user login
   - Choose between Email/Password or RFID authentication
   - First-time users should complete their profile if prompted

## User Flow

1. **Registration**: Complete census registration via main system
2. **Login**: Choose Email/Password or RFID authentication
3. **Profile Check**: System checks if profile is complete
4. **Dashboard**: Overview of user's activities and quick access to services
5. **Services**: Access reports, e-services, and other features
6. **Logout**: Secure session termination

## Security Features

- **Dual Authentication**: Email/password and RFID options
- **Password Hashing**: Using PHP's password_hash() with default algorithm
- **Session Management**: Secure session handling with proper cleanup
- **Input Validation**: Comprehensive validation and sanitization
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: htmlspecialchars() for output
- **Active User Check**: Validates user status on each request
- **Profile Completion**: Enforces complete user profiles

## Integration Features

### Main System Integration
- **Shared Database**: Uses central residents table
- **RFID Compatibility**: Works with existing RFID infrastructure
- **Service Linking**: Links to existing forms and services
- **Consistent Design**: Unified styling and navigation
- **Admin Integration**: Reports accessible via admin panel

### RFID System Integration
- **Automatic Detection**: Auto-submit when RFID is scanned
- **Manual Fallback**: Option to manually enter RFID codes
- **Real-time Validation**: Immediate authentication feedback
- **Profile Completion**: Redirects to complete profile if needed

## Authentication Examples

### Email/Password Authentication
```php
// Email: biofrostyv@gmail.com
// Password: (user's password)
```

### RFID Authentication
```php
// RFID Code: A9ZS6XI3EJ (example)
// Auto-detected or manually entered
```

## Future Enhancements

- **Two-Factor Authentication**: SMS or email-based 2FA
- **Social Login**: Integration with social media platforms
- **Mobile App Integration**: API endpoints for mobile applications
- **Advanced Reporting**: Enhanced analytics and reporting features
- **Real-time Notifications**: WebSocket-based notifications
- **Payment Integration**: Online payment for service fees
- **Document Management**: Digital document storage and retrieval
- **Appointment Scheduling**: Online appointment booking system

## Troubleshooting

### Common Issues

1. **Login Issues**
   - Verify email/password combination
   - Check if user account is active
   - Ensure profile is complete

2. **RFID Issues**
   - Verify RFID code is registered
   - Check RFID reader connectivity
   - Try manual RFID code entry

3. **Profile Issues**
   - Complete profile via prompted redirect
   - Verify all required fields are filled
   - Contact admin for profile activation

### Error Messages
- "Invalid email or password" - Check credentials
- "Invalid RFID or user not found" - Verify RFID registration
- "Profile incomplete" - Complete profile via redirect

## Support

For technical support or questions about the user system, please contact the system administrator or refer to the main system documentation. 