# 🎲 Dummy Census Registration Data

This directory contains tools to generate realistic test data for the enhanced census registration system.

## 📋 Generated Test Data

### 5 Diverse Registration Profiles Created:

#### 1. **Maria Santos Cruz** - 🏠 Large Family
- **Profile**: 34-year-old married teacher
- **Family**: 5 members (spouse, 3 children, mother-in-law)
- **Disabilities**: 2 records (hearing impairment, mobility issues)
- **Organizations**: 3 memberships (professional associations)
- **Status**: Pending ⏳
- **Data Richness**: ⭐⭐⭐⭐⭐ Complete

#### 2. **John Miguel Reyes** - 👤 Single Professional  
- **Profile**: 28-year-old single software engineer
- **Family**: None (lives alone)
- **Disabilities**: None
- **Organizations**: None
- **Status**: Approved ✅
- **Data Richness**: ⭐ Basic info only

#### 3. **Rosa Dela Cruz** - 👵 Senior Citizen
- **Profile**: 68-year-old widow, retired
- **Family**: 2 adult children
- **Disabilities**: None
- **Organizations**: 3 memberships (senior groups, health workers)
- **Status**: Pending ⏳
- **Data Richness**: ⭐⭐⭐ Family + Organizations

#### 4. **Pedro Jose Garcia** - 👨‍👩‍👧‍👦 Young Family
- **Profile**: 32-year-old married construction worker
- **Family**: 3 members (spouse, 2 young children)
- **Disabilities**: 2 records (autism, vision impairment)
- **Organizations**: None
- **Status**: Rejected ❌
- **Data Richness**: ⭐⭐⭐ Family + Disabilities

#### 5. **Ana Luz Villanueva** - 🏆 Complete Profile
- **Profile**: 29-year-old married nurse
- **Family**: 4 members (spouse, 2 children, mother)
- **Disabilities**: 1 record (arthritis)
- **Organizations**: 4 memberships (professional, business, health)
- **Status**: Pending ⏳
- **Data Richness**: ⭐⭐⭐⭐⭐ All categories

## 🎯 Testing Scenarios

### Perfect for Testing:

1. **Enhanced Admin View Features**
   - Data badge display (family, disability, organization counts)
   - Visual indicators for different data richness levels
   - Status management workflow

2. **Form View Functionality**
   - All 5 tabs displayed correctly
   - Dynamic row management
   - Read-only mode verification

3. **Database Integration**
   - Foreign key relationships
   - Complex data queries
   - Multi-table joins

4. **User Experience**
   - Responsive design across different screen sizes
   - Search and filter functionality
   - Status progression validation

## 📊 Data Statistics

- **Total Registrations**: 5
- **Total Family Members**: 14
- **Total Disability Records**: 5  
- **Total Organization Memberships**: 10
- **Status Distribution**: 3 Pending, 1 Approved, 1 Rejected

## 🛠️ How to Generate

### Option 1: Web Interface (Recommended)
1. Navigate to `/database/generate_dummy_interface.php`
2. Click "Generate Dummy Census Data"
3. View results and confirmation

### Option 2: Command Line
```bash
php database/generate_dummy_data.php
```

### Option 3: Direct SQL
```bash
mysql -u root -p gumaoc_db < database/insert_dummy_registrations.sql
```

## 🔍 What to Test

### In Admin View (`/admin/view-resident-registrations.php`):
- [ ] Data badges display correctly
- [ ] Different badge colors for each data type
- [ ] Hover tooltips show exact counts
- [ ] "View Complete Form" button works
- [ ] Status management functions properly
- [ ] Search and filter work with new data

### In Registration Form View:
- [ ] All 5 tabs load correctly
- [ ] Family members display properly in Tab 2
- [ ] Disabilities show in Tab 4
- [ ] Organizations appear in Tab 5
- [ ] Read-only mode prevents editing
- [ ] Navigation between tabs works

### Responsive Design:
- [ ] Desktop view (1200px+)
- [ ] Tablet view (768px-1024px)
- [ ] Mobile view (< 768px)
- [ ] Badge layout adapts properly

## 🧹 Cleanup

To remove dummy data:
```sql
DELETE FROM family_organizations WHERE registration_id IN (SELECT id FROM resident_registrations WHERE contact_number LIKE '09%71234567' OR contact_number LIKE '09%87654321' OR contact_number LIKE '09%45678901' OR contact_number LIKE '09%56789012' OR contact_number LIKE '09%67890123');
DELETE FROM family_disabilities WHERE registration_id IN (SELECT id FROM resident_registrations WHERE contact_number LIKE '09%71234567' OR contact_number LIKE '09%87654321' OR contact_number LIKE '09%45678901' OR contact_number LIKE '09%56789012' OR contact_number LIKE '09%67890123');
DELETE FROM family_members WHERE registration_id IN (SELECT id FROM resident_registrations WHERE contact_number LIKE '09%71234567' OR contact_number LIKE '09%87654321' OR contact_number LIKE '09%45678901' OR contact_number LIKE '09%56789012' OR contact_number LIKE '09%67890123');
DELETE FROM resident_registrations WHERE contact_number LIKE '09%71234567' OR contact_number LIKE '09%87654321' OR contact_number LIKE '09%45678901' OR contact_number LIKE '09%56789012' OR contact_number LIKE '09%67890123';
```

## 📝 Notes

- All dummy phone numbers use specific patterns for easy identification (09171234567, 09287654321, etc.)
- Realistic Filipino names and addresses used
- Diverse age ranges and family structures
- Various disability types and organization memberships
- Mixed approval statuses for testing workflow
- **Fixed**: Updated to match actual database table structure (removed non-existent columns like birth_place, email, address, etc.)