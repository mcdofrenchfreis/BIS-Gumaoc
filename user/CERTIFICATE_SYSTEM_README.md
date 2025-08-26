# Certificate Request System for Users

## Overview
This system allows registered users to request certificates online through a user-friendly interface, similar to the existing admin certificate request page but optimized for end-users.

## Files Created

### 1. User Certificate Request (`/user/certificate-request.php`)
- **Purpose**: Main certificate request form for users
- **Features**:
  - Clean, modern interface with certificate type selection
  - Auto-populates user data from their profile
  - Supports multiple certificate types (Barangay Clearance, Indigency, Residency, CTC)
  - Mobile-responsive design
  - Form validation and user feedback

### 2. Request Processing (`/user/process_certificate_request.php`)
- **Purpose**: Handles form submission and database storage
- **Features**:
  - Server-side validation
  - Sanitizes input data
  - Stores requests in database
  - Generates request tracking ID
  - Error handling and user feedback

### 3. Request Status Tracking (`/user/my-requests.php`)
- **Purpose**: Allows users to view and track their certificate requests
- **Features**:
  - Displays all user's requests with status
  - Real-time status updates
  - Processing time tracking
  - Administrative notes display
  - Auto-refresh functionality
  - Desktop notifications for ready certificates

### 4. Database Setup (`/user/setup_certificate_system.php`)
- **Purpose**: Initializes the certificate_requests table
- **Features**:
  - Creates necessary database structure
  - Sets up proper indexes for performance
  - One-time setup script

### 5. SQL Schema (`/database/create_certificate_requests_table.sql`)
- **Purpose**: Database schema for certificate requests
- **Features**:
  - Comprehensive table structure
  - Status tracking (pending, processing, ready, released)
  - User relationship management
  - Timestamps for audit trail

## Installation Steps

### 1. Database Setup
Run the setup script to create the necessary table:
```bash
php /user/setup_certificate_system.php
```

Or manually execute the SQL file:
```sql
-- Run the contents of /database/create_certificate_requests_table.sql
```

### 2. File Permissions
Ensure proper file permissions are set for PHP execution.

### 3. Integration
The system is already integrated into:
- User dashboard (`/user/dashboard.php`)
- E-services page (`/user/e-services.php`)
- Navigation menus

## Usage

### For Users:
1. **Login** to the user portal
2. **Navigate** to Dashboard or E-Services
3. **Click** "Request Certificate" or "Certificate Requests"
4. **Select** certificate type from the visual grid
5. **Fill** out the form (auto-populated with profile data)
6. **Submit** request
7. **Track** status via "My Requests" page

### For Administrators:
- Certificate requests appear in the existing admin system
- Can be processed using existing admin tools
- Status updates reflect in user interface

## Certificate Types Supported

1. **Barangay Clearance**
   - Certificate of good moral character
   - Standard processing time

2. **Indigency Certificate**
   - Certificate of financial status
   - For various social services

3. **Residency Certificate**
   - Proof of residence
   - For employment, school, etc.

4. **Community Tax Certificate (CTC/Cedula)**
   - Tax certificate
   - Legal identification document

## Features

### User Experience:
- **Auto-population**: User profile data fills form automatically
- **Visual selection**: Certificate types displayed as cards
- **Responsive design**: Works on desktop, tablet, and mobile
- **Real-time validation**: Immediate feedback on form errors
- **Status tracking**: Live updates on request progress

### Security:
- **Authentication required**: Must be logged in
- **Input sanitization**: All data cleaned before storage
- **SQL injection protection**: Prepared statements used
- **XSS prevention**: Output escaped properly

### Performance:
- **Optimized queries**: Proper database indexing
- **Efficient loading**: Minimal database calls
- **Caching friendly**: Static assets optimized

## Database Schema

```sql
CREATE TABLE certificate_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                    -- Links to users table
    certificate_type VARCHAR(100) NOT NULL,  -- Type of certificate
    full_name VARCHAR(255) NOT NULL,         -- Applicant name
    address TEXT NOT NULL,                   -- Applicant address
    mobile_number VARCHAR(20),               -- Contact number
    civil_status VARCHAR(50),                -- Marital status
    gender VARCHAR(10),                      -- Gender
    birth_date DATE,                         -- Date of birth
    birth_place VARCHAR(255),                -- Place of birth
    citizenship VARCHAR(100) DEFAULT 'Filipino', -- Citizenship
    years_of_residence INT,                  -- Years living in barangay
    purpose TEXT NOT NULL,                   -- Purpose of request
    status ENUM('pending', 'processing', 'ready', 'released'), -- Status
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,             -- When status changed
    notes TEXT,                              -- Admin notes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Status Workflow

1. **Pending** - Request submitted, awaiting review
2. **Processing** - Being processed by admin staff
3. **Ready** - Certificate ready for pickup
4. **Released** - Certificate picked up/completed

## Customization

### Adding New Certificate Types:
1. Update the certificate selection grid in `certificate-request.php`
2. Add corresponding icons and descriptions
3. Update validation if needed

### Styling Changes:
- Modify CSS styles in the `<style>` sections
- Maintain responsive design principles
- Follow existing color scheme

### Additional Fields:
1. Add form fields in `certificate-request.php`
2. Update database schema if needed
3. Modify processing script to handle new fields

## Troubleshooting

### Common Issues:

1. **Database Connection Errors**
   - Check `../includes/db_connect.php` configuration
   - Verify database credentials
   - Ensure database exists

2. **Form Not Submitting**
   - Check JavaScript console for errors
   - Verify form action URL is correct
   - Ensure authentication is working

3. **Auto-population Not Working**
   - Verify user is logged in
   - Check session variables
   - Confirm user data exists in database

4. **Status Not Updating**
   - Check admin system for status changes
   - Verify database updates are working
   - Clear browser cache

## Future Enhancements

Potential improvements:
- Email notifications for status changes
- SMS notifications
- Document upload for supporting files
- Payment integration for fees
- Digital certificate delivery
- Advanced search and filtering
- Bulk operations for admins

## Support

For technical issues or feature requests, check:
1. PHP error logs
2. Browser developer console
3. Database query logs
4. Session management

## Security Considerations

- Regular security updates
- Input validation on both client and server
- Proper session management
- Database access controls
- File upload restrictions (if implemented)
- Rate limiting for form submissions