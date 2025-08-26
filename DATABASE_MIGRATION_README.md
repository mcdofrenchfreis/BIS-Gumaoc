# Database Migration Guide

## Overview
This document explains the differences between the original `gumaoc_db.sql` and the updated `gumaoc_db Latest.sql` files, and provides instructions for migrating your database to the latest schema.

## Key Changes in the Latest Database Schema

### 1. New Tables Added
The latest schema includes several new tables that enhance the functionality of the barangay management system:

- **access_logs**: Tracks resident access attempts to various forms
- **barangay_blotter**: Manages barangay incident reports and complaints
- **blotter_attachments**: Stores attachments related to blotter entries
- **captain_clearances**: Manages clearances issued by the barangay captain
- **resident_status**: Tracks the status of residents based on their involvement in incidents
- **user_reports**: Allows residents to submit incident reports

### 2. Enhanced Existing Tables

#### admin_users
- Added a new admin user for blotter management

#### business_applications
- Added `business_description` field
- Added `capital_amount` field
- Added `owner_contact` field
- Added `proof_image` field for business documentation

#### certificate_requests
- Added `user_id` to link requests to residents
- Added `additional_data` for flexible data storage (JSON format)
- Added `proof_image` for document verification
- Added `notes` field for administrative comments

### 3. Database Functions and Triggers

#### update_resident_status Function
A new database function that automatically calculates and updates a resident's status based on their involvement in barangay incidents.

#### Triggers
- **update_resident_status_after_blotter_insert**: Automatically updates resident status when a new blotter entry is created
- **update_resident_status_after_blotter_update**: Automatically updates resident status when a blotter entry is modified

### 4. Improved Data Integrity
- Added foreign key constraints for better data relationships
- Enhanced indexing for improved query performance
- Updated AUTO_INCREMENT values to match current data

## Migration Process

### Prerequisites
- Backup your current database before running the migration
- Ensure you have appropriate privileges to modify the database schema

### Steps to Migrate

1. **Backup Your Database**
   ```sql
   mysqldump -u [username] -p gumaoc_db > gumaoc_db_backup.sql
   ```

2. **Run the Migration Script**
   Execute the provided migration script:
   ```sql
   mysql -u [username] -p gumaoc_db < database_migration_script.sql
   ```

3. **Verify the Migration**
   Check that all new tables, columns, and constraints have been properly created.

### Manual Steps (If Required)

Some changes may need to be applied manually depending on your current database state:

1. **Update Certificate Requests**
   Ensure all existing certificate requests have a valid `user_id`:
   ```sql
   UPDATE certificate_requests SET user_id = [appropriate_user_id] WHERE user_id = 0;
   ```

2. **Populate Resident Status**
   Run the function to populate initial resident statuses:
   ```sql
   SELECT update_resident_status(resident_id) FROM residents;
   ```

## Recent Fixes Applied

The migration script has been updated to fix the following issues:

1. **Foreign Key Constraint Syntax**: Fixed syntax error when adding foreign key constraints to existing tables
2. **Data Cleanup**: Added data validation steps to ensure referential integrity before adding constraints
3. **Function Conflicts**: Added proper handling for existing functions and triggers to prevent conflicts
4. **Error Handling**: Improved error handling and rollback procedures

## Testing the Migration

After migration, verify that:

1. All new tables exist and have the correct structure
2. Existing data is preserved and properly linked
3. New functionality (blotter management, resident status tracking) works correctly
4. Admin panels for new features load without errors

## Rollback Plan

If issues are encountered after migration:

1. Restore the database from your backup:
   ```sql
   mysql -u [username] -p gumaoc_db < gumaoc_db_backup.sql
   ```

2. Report any issues with the migration script

## Support

For issues with this migration, please contact the development team with:
- Details of any errors encountered
- The state of your database before migration
- Any custom modifications you may have made to the original schema

---
*Last Updated: August 26, 2025*