@echo off
:: Database Migration Script for GUMAOC System
:: This script automates the migration process

echo ========================================================
echo GUMAOC Database Migration Tool
echo ========================================================
echo.

:: Check if MySQL is installed and accessible
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: MySQL is not installed or not in PATH
    echo Please install MySQL or add it to your system PATH
    pause
    exit /b 1
)

:: Get database credentials
echo Please enter your MySQL credentials:
set /p db_user="Username: "
set /p db_name="Database name (default: gumaoc_db): "
if "%db_name%"=="" set db_name=gumaoc_db
echo.

:: Create backup
echo Creating database backup...
mysqldump -u %db_user% -p %db_name% > %db_name%_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.sql
if %errorlevel% neq 0 (
    echo ERROR: Failed to create database backup
    pause
    exit /b 1
)
echo Backup created successfully!
echo.

:: Run migration
echo Running database migration...
mysql -u %db_user% -p %db_name% < database_migration_script.sql
if %errorlevel% neq 0 (
    echo ERROR: Migration failed
    echo Restoring from backup...
    mysql -u %db_user% -p %db_name% < %db_name%_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.sql
    if %errorlevel% neq 0 (
        echo ERROR: Failed to restore from backup
    ) else (
        echo Successfully restored from backup
    )
    pause
    exit /b 1
)

echo.
echo ========================================================
echo Migration completed successfully!
echo ========================================================
echo.
echo Next steps:
echo 1. Verify the migration by checking the new tables
echo 2. Test the application functionality
echo 3. Update any application code that references the new schema
echo.
pause