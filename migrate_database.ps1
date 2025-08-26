# Database Migration Script for GUMAOC System
# This script automates the migration process using PowerShell

Write-Host "========================================================" -ForegroundColor Green
Write-Host "GUMAOC Database Migration Tool" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host ""

# Check if MySQL is installed and accessible
try {
    $mysqlVersion = mysql --version 2>$null
    if (-not $mysqlVersion) {
        throw "MySQL not found"
    }
    Write-Host "MySQL found: $mysqlVersion" -ForegroundColor Green
} catch {
    Write-Host "ERROR: MySQL is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install MySQL or add it to your system PATH" -ForegroundColor Red
    pause
    exit 1
}

# Get database credentials
Write-Host "Please enter your MySQL credentials:" -ForegroundColor Yellow
$dbUser = Read-Host "Username"
$dbName = Read-Host "Database name (default: gumaoc_db)"
if (-not $dbName) { $dbName = "gumaoc_db" }
Write-Host ""

# Create backup
Write-Host "Creating database backup..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFile = "${dbName}_backup_${timestamp}.sql"

try {
    & mysqldump -u $dbUser -p $dbName > $backupFile
    if ($LASTEXITCODE -ne 0) {
        throw "mysqldump failed"
    }
    Write-Host "Backup created successfully: $backupFile" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to create database backup" -ForegroundColor Red
    pause
    exit 1
}
Write-Host ""

# Run migration
Write-Host "Running database migration..." -ForegroundColor Yellow
try {
    & mysql -u $dbUser -p $dbName < database_migration_script.sql
    if ($LASTEXITCODE -ne 0) {
        throw "Migration failed"
    }
    Write-Host "Migration completed successfully!" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Migration failed" -ForegroundColor Red
    Write-Host "Restoring from backup..." -ForegroundColor Yellow
    
    try {
        & mysql -u $dbUser -p $dbName < $backupFile
        if ($LASTEXITCODE -ne 0) {
            throw "Restore failed"
        }
        Write-Host "Successfully restored from backup" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: Failed to restore from backup" -ForegroundColor Red
    }
    pause
    exit 1
}

Write-Host ""
Write-Host "========================================================" -ForegroundColor Green
Write-Host "Migration completed successfully!" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Verify the migration by checking the new tables" -ForegroundColor Yellow
Write-Host "2. Test the application functionality" -ForegroundColor Yellow
Write-Host "3. Update any application code that references the new schema" -ForegroundColor Yellow
Write-Host ""
pause