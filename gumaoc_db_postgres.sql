-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 24, 2025 at 03:13 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34











--
-- Database: "u138614204_gumaoc_db"
--


--
-- Functions
--
/* PostgreSQL requires PL/pgSQL for functions and triggers:
CREATE DEFINER="u138614204_gumaoc"@"127.0.0.1" FUNCTION "update_resident_status" ("resident_id_param" INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA BEGIN
    DECLARE complaint_count INT DEFAULT 0;
    DECLARE incident_count INT DEFAULT 0;
    DECLARE pending_count INT DEFAULT 0;
    DECLARE resolved_count INT DEFAULT 0;
    DECLARE new_status VARCHAR(20) DEFAULT 'good';
    DECLARE requires_clearance BOOLEAN DEFAULT FALSE;
    
    -- Count complaints where resident is complainant
    SELECT COUNT(*) INTO complaint_count 
    FROM barangay_blotter 
    WHERE complainant_id = resident_id_param;
    
    -- Count incidents where resident is respondent
    SELECT COUNT(*) INTO incident_count 
    FROM barangay_blotter 
    WHERE respondent_id = resident_id_param;
    
    -- Count pending cases where resident is respondent
    SELECT COUNT(*) INTO pending_count 
    FROM barangay_blotter 
    WHERE respondent_id = resident_id_param 
    AND status IN ('filed', 'under_investigation', 'mediation');
    
    -- Count resolved cases where resident is respondent
    SELECT COUNT(*) INTO resolved_count 
    FROM barangay_blotter 
    WHERE respondent_id = resident_id_param 
    AND status IN ('resolved', 'dismissed');
    
    -- Determine status based on incident count and pending cases
    IF incident_count = 0 THEN
        SET new_status = 'good';
        SET requires_clearance = FALSE;
    ELSEIF incident_count <= 2 AND pending_count = 0 THEN
        SET new_status = 'minor_issues';
        SET requires_clearance = FALSE;
    ELSEIF incident_count <= 5 OR pending_count > 0 THEN
        SET new_status = 'major_issues';
        SET requires_clearance = TRUE;
    ELSE
        SET new_status = 'critical';
        SET requires_clearance = TRUE;
    END IF;
    
    -- Update or insert resident status
    INSERT INTO resident_status (
        resident_id, resident_name, record_status, total_complaints, 
        total_incidents, pending_cases, resolved_cases, requires_captain_clearance
    ) VALUES (
        resident_id_param, 
        (SELECT CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) FROM residents WHERE id = resident_id_param),
        new_status, complaint_count, incident_count, pending_count, resolved_count, requires_clearance
    ) ON DUPLICATE KEY UPDATE
        record_status = new_status,
        total_complaints = complaint_count,
        total_incidents = incident_count,
        pending_cases = pending_count,
        resolved_cases = resolved_count,
        requires_captain_clearance = requires_clearance,
        last_updated = CURRENT_TIMESTAMP;
    
    RETURN new_status;
END
*/



-- --------------------------------------------------------

--
-- Table structure for table "access_logs"
--

CREATE TABLE "access_logs" (
  "id" INTEGER NOT NULL,
  "resident_id" INTEGER DEFAULT NULL,
  "form_type" varchar(100) NOT NULL,
  "access_granted" BOOLEAN NOT NULL,
  "reason" varchar(255) NOT NULL,
  "attempted_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "ip_address" varchar(45) DEFAULT NULL,
  "user_agent" text DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table "admin_logs"
--

CREATE TABLE "admin_logs" (
  "id" INTEGER NOT NULL,
  "admin_id" varchar(100) DEFAULT 'system',
  "action_type" varchar(50) NOT NULL,
  "target_type" varchar(50) NOT NULL,
  "target_id" INTEGER DEFAULT NULL,
  "description" text NOT NULL,
  "details" JSONB,
  "ip_address" varchar(45) DEFAULT NULL,
  "user_agent" text DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table "admin_logs"
--


-- --------------------------------------------------------

--
-- Table structure for table "admin_users"
--

CREATE TABLE "admin_users" (
  "id" INTEGER NOT NULL,
  "username" varchar(50) NOT NULL,
  "password" varchar(255) NOT NULL,
  "full_name" varchar(100) NOT NULL,
  "email" varchar(100) NOT NULL,
  "role" VARCHAR(255) NOT NULL DEFAULT 'admin',
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Dumping data for table "admin_users"
--


-- --------------------------------------------------------

--
-- Table structure for table "barangay_blotter"
--

CREATE TABLE "barangay_blotter" (
  "id" INTEGER NOT NULL,
  "blotter_number" varchar(50) NOT NULL,
  "incident_type" VARCHAR(255) NOT NULL,
  "complainant_id" INTEGER DEFAULT NULL,
  "complainant_name" varchar(255) NOT NULL,
  "complainant_address" varchar(500) NOT NULL,
  "complainant_contact" varchar(20) DEFAULT NULL,
  "respondent_id" INTEGER DEFAULT NULL,
  "respondent_name" varchar(255) NOT NULL,
  "respondent_address" varchar(500) NOT NULL,
  "respondent_contact" varchar(20) DEFAULT NULL,
  "incident_date" TIMESTAMP NOT NULL,
  "reported_date" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "location" varchar(500) NOT NULL,
  "description" text NOT NULL,
  "classification" VARCHAR(255) DEFAULT 'minor',
  "status" VARCHAR(255) DEFAULT 'filed',
  "investigating_officer" varchar(255) DEFAULT NULL,
  "settlement_details" text DEFAULT NULL,
  "action_taken" text DEFAULT NULL,
  "case_disposition" text DEFAULT NULL,
  "created_by" varchar(100) NOT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Triggers "barangay_blotter"
--

/* PostgreSQL requires PL/pgSQL for functions and triggers:
CREATE TRIGGER "update_resident_status_after_blotter_insert" AFTER INSERT ON "barangay_blotter" FOR EACH ROW BEGIN
    -- Update complainant status if they are a registered resident
    IF NEW.complainant_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.complainant_id);
    END IF;
    
    -- Update respondent status if they are a registered resident
    IF NEW.respondent_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.respondent_id);
    END IF;
END

*/


/* PostgreSQL requires PL/pgSQL for functions and triggers:
CREATE TRIGGER "update_resident_status_after_blotter_update" AFTER UPDATE ON "barangay_blotter" FOR EACH ROW BEGIN
    -- Update complainant status if they are a registered resident
    IF NEW.complainant_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.complainant_id);
    END IF;
    
    -- Update respondent status if they are a registered resident
    IF NEW.respondent_id IS NOT NULL THEN
        SET @result = update_resident_status(NEW.respondent_id);
    END IF;
    
    -- Also update old complainant/respondent if they changed
    IF OLD.complainant_id IS NOT NULL AND OLD.complainant_id != NEW.complainant_id THEN
        SET @result = update_resident_status(OLD.complainant_id);
    END IF;
    
    IF OLD.respondent_id IS NOT NULL AND OLD.respondent_id != NEW.respondent_id THEN
        SET @result = update_resident_status(OLD.respondent_id);
    END IF;
END

*/


-- --------------------------------------------------------

--
-- Table structure for table "blotter_attachments"
--

CREATE TABLE "blotter_attachments" (
  "id" INTEGER NOT NULL,
  "blotter_id" INTEGER NOT NULL,
  "file_name" varchar(255) NOT NULL,
  "file_path" varchar(500) NOT NULL,
  "file_type" varchar(50) NOT NULL,
  "file_size" INTEGER NOT NULL,
  "uploaded_by" varchar(100) NOT NULL,
  "uploaded_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table "business_applications"
--

CREATE TABLE "business_applications" (
  "id" INTEGER NOT NULL,
  "user_id" INTEGER DEFAULT NULL,
  "reference_no" varchar(50) DEFAULT NULL,
  "application_date" date DEFAULT NULL,
  "first_name" varchar(100) DEFAULT NULL,
  "middle_name" varchar(100) DEFAULT NULL,
  "last_name" varchar(100) DEFAULT NULL,
  "business_location" text DEFAULT NULL,
  "or_number" varchar(100) DEFAULT NULL,
  "ctc_number" varchar(100) DEFAULT NULL,
  "business_name" varchar(255) NOT NULL,
  "business_type" varchar(100) NOT NULL,
  "business_address" varchar(500) NOT NULL,
  "business_description" text DEFAULT NULL,
  "capital_amount" decimal(15,2) DEFAULT NULL,
  "owner_name" varchar(255) NOT NULL,
  "owner_address" text DEFAULT NULL,
  "owner_contact" varchar(20) DEFAULT NULL,
  "contact_number" varchar(20) NOT NULL,
  "years_operation" INTEGER NOT NULL,
  "investment_capital" decimal(15,2) NOT NULL,
  "proof_image" varchar(255) DEFAULT NULL ,
  "submitted_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "status" VARCHAR(255) DEFAULT 'pending'
) ;

--
-- Dumping data for table "business_applications"
--


-- --------------------------------------------------------

--
-- Table structure for table "captain_clearances"
--

CREATE TABLE "captain_clearances" (
  "id" INTEGER NOT NULL,
  "resident_id" INTEGER NOT NULL,
  "clearance_type" VARCHAR(255) NOT NULL,
  "reason" text NOT NULL,
  "granted_by" varchar(100) NOT NULL,
  "granted_date" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "expires_at" TIMESTAMP DEFAULT NULL,
  "status" VARCHAR(255) DEFAULT 'active',
  "notes" text DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

-- --------------------------------------------------------

--
-- Table structure for table "certificate_requests"
--

CREATE TABLE "certificate_requests" (
  "id" INTEGER NOT NULL,
  "user_id" INTEGER NOT NULL,
  "full_name" varchar(255) NOT NULL,
  "address" varchar(500) NOT NULL,
  "mobile_number" varchar(20) DEFAULT NULL,
  "civil_status" varchar(50) DEFAULT NULL,
  "gender" varchar(20) DEFAULT NULL,
  "birth_date" date NOT NULL,
  "birth_place" varchar(255) NOT NULL,
  "citizenship" varchar(100) DEFAULT NULL,
  "years_of_residence" INTEGER DEFAULT NULL,
  "certificate_type" varchar(100) NOT NULL,
  "purpose" text NOT NULL,
  "additional_data" JSONB,
  "proof_image" varchar(255) DEFAULT NULL,
  "photo_2x2" varchar(255) DEFAULT NULL ,
  "vehicle_make_type" varchar(255) DEFAULT NULL,
  "motor_no" varchar(100) DEFAULT NULL,
  "chassis_no" varchar(100) DEFAULT NULL,
  "plate_no" varchar(50) DEFAULT NULL,
  "vehicle_color" varchar(50) DEFAULT NULL,
  "year_model" INTEGER DEFAULT NULL,
  "body_no" varchar(100) DEFAULT NULL,
  "operator_license" varchar(100) DEFAULT NULL,
  "tricycle_photo" varchar(255) DEFAULT NULL,
  "submitted_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "status" VARCHAR(255) DEFAULT 'pending',
  "queue_ticket_id" INTEGER DEFAULT NULL,
  "queue_ticket_number" varchar(20) DEFAULT NULL,
  "notes" text NOT NULL
) ;

--
-- Dumping data for table "certificate_requests"
--


-- --------------------------------------------------------

--
-- Table structure for table "family_disabilities"
--

CREATE TABLE "family_disabilities" (
  "id" INTEGER NOT NULL,
  "registration_id" INTEGER NOT NULL,
  "name" varchar(255) NOT NULL,
  "disability_type" varchar(255) NOT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table "family_members"
--

CREATE TABLE "family_members" (
  "id" INTEGER NOT NULL,
  "registration_id" INTEGER NOT NULL,
  "full_name" varchar(255) NOT NULL,
  "relationship" varchar(100) DEFAULT NULL,
  "birth_date" date DEFAULT NULL,
  "age" INTEGER DEFAULT NULL,
  "gender" VARCHAR(255) DEFAULT NULL,
  "civil_status" varchar(50) DEFAULT NULL,
  "email" varchar(255) DEFAULT NULL,
  "education" varchar(100) DEFAULT NULL,
  "occupation" varchar(100) DEFAULT NULL,
  "is_deceased" BOOLEAN DEFAULT false,
  "has_account" BOOLEAN DEFAULT false,
  "skills" varchar(255) DEFAULT NULL,
  "monthly_income" decimal(10,2) DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table "family_members"
--


-- --------------------------------------------------------

--
-- Table structure for table "family_organizations"
--

CREATE TABLE "family_organizations" (
  "id" INTEGER NOT NULL,
  "registration_id" INTEGER NOT NULL,
  "name" varchar(255) NOT NULL,
  "organization_type" varchar(255) NOT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table "family_organizations"
--


-- --------------------------------------------------------

--
-- Table structure for table "notifications"
--

CREATE TABLE "notifications" (
  "id" INTEGER NOT NULL,
  "user_id" INTEGER NOT NULL,
  "title" varchar(255) NOT NULL,
  "message" text NOT NULL,
  "type" VARCHAR(255) DEFAULT 'info',
  "action_url" varchar(500) DEFAULT NULL,
  "is_read" BOOLEAN DEFAULT false,
  "read_at" timestamp NULL DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

-- --------------------------------------------------------

--
-- Table structure for table "queue_counters"
--

CREATE TABLE "queue_counters" (
  "id" INTEGER NOT NULL,
  "counter_number" varchar(10) NOT NULL,
  "counter_name" varchar(50) NOT NULL,
  "service_id" INTEGER DEFAULT NULL,
  "operator_name" varchar(100) DEFAULT NULL,
  "is_active" BOOLEAN DEFAULT true,
  "current_ticket_id" INTEGER DEFAULT NULL,
  "last_called_at" timestamp NULL DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Dumping data for table "queue_counters"
--


-- --------------------------------------------------------

--
-- Table structure for table "queue_services"
--

CREATE TABLE "queue_services" (
  "id" INTEGER NOT NULL,
  "service_name" varchar(100) NOT NULL,
  "service_code" varchar(10) NOT NULL,
  "description" text DEFAULT NULL,
  "estimated_time" INTEGER DEFAULT 15 ,
  "max_daily_capacity" INTEGER DEFAULT 50,
  "is_active" BOOLEAN DEFAULT true,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Dumping data for table "queue_services"
--


-- --------------------------------------------------------

--
-- Table structure for table "queue_tickets"
--

CREATE TABLE "queue_tickets" (
  "id" INTEGER NOT NULL,
  "ticket_number" varchar(20) NOT NULL,
  "service_id" INTEGER NOT NULL,
  "customer_name" varchar(100) NOT NULL,
  "mobile_number" varchar(20) DEFAULT NULL,
  "user_id" INTEGER DEFAULT NULL,
  "purpose" text DEFAULT NULL,
  "priority_level" VARCHAR(255) DEFAULT 'normal',
  "status" VARCHAR(255) DEFAULT 'waiting',
  "queue_position" INTEGER DEFAULT NULL,
  "estimated_time" TIMESTAMP DEFAULT NULL,
  "called_at" timestamp NULL DEFAULT NULL,
  "served_at" timestamp NULL DEFAULT NULL,
  "completed_at" timestamp NULL DEFAULT NULL,
  "served_by" varchar(100) DEFAULT NULL,
  "notes" text DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Dumping data for table "queue_tickets"
--


-- --------------------------------------------------------

--
-- Table structure for table "queue_windows"
--

CREATE TABLE "queue_windows" (
  "id" INTEGER NOT NULL,
  "window_number" varchar(10) NOT NULL,
  "window_name" varchar(50) NOT NULL,
  "service_id" INTEGER DEFAULT NULL,
  "operator_name" varchar(100) DEFAULT NULL,
  "is_active" BOOLEAN DEFAULT true,
  "current_ticket_id" INTEGER DEFAULT NULL,
  "last_called_at" timestamp NULL DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Dumping data for table "queue_windows"
--


-- --------------------------------------------------------

--
-- Table structure for table "residents"
--

CREATE TABLE "residents" (
  "id" INTEGER NOT NULL,
  "first_name" varchar(100) NOT NULL,
  "middle_name" varchar(100) DEFAULT NULL,
  "last_name" varchar(100) NOT NULL,
  "email" varchar(255) NOT NULL,
  "phone" varchar(20) NOT NULL,
  "password" varchar(255) DEFAULT NULL,
  "address" text NOT NULL,
  "house_number" varchar(20) DEFAULT NULL,
  "barangay" varchar(100) DEFAULT 'Gumaoc East',
  "sitio" varchar(100) DEFAULT 'BLOCK',
  "interviewer" varchar(255) DEFAULT NULL,
  "interviewer_title" varchar(255) DEFAULT NULL,
  "birthdate" date NOT NULL,
  "birth_place" varchar(255) DEFAULT NULL,
  "gender" VARCHAR(255) NOT NULL,
  "civil_status" VARCHAR(255) NOT NULL,
  "rfid_code" varchar(50) DEFAULT NULL,
  "rfid" varchar(50) DEFAULT NULL,
  "status" VARCHAR(255) NOT NULL DEFAULT 'active',
  "reset_otp" varchar(6) DEFAULT NULL,
  "otp_expiry" timestamp NULL DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  "profile_complete" BOOLEAN DEFAULT true ,
  "created_by" INTEGER DEFAULT NULL ,
  "relationship_to_head" varchar(100) DEFAULT NULL 
) ;

--
-- Dumping data for table "residents"
--


-- --------------------------------------------------------

--
-- Table structure for table "resident_registrations"
--

CREATE TABLE "resident_registrations" (
  "id" INTEGER NOT NULL,
  "first_name" varchar(100) NOT NULL,
  "middle_name" varchar(100) DEFAULT NULL,
  "last_name" varchar(100) NOT NULL,
  "birth_date" date NOT NULL,
  "birth_place" varchar(255) DEFAULT NULL,
  "age" INTEGER NOT NULL,
  "civil_status" varchar(50) NOT NULL,
  "gender" varchar(20) NOT NULL,
  "contact_number" varchar(20) DEFAULT NULL,
  "email" varchar(255) DEFAULT NULL,
  "house_number" varchar(20) DEFAULT NULL,
  "address" text DEFAULT NULL,
  "pangkabuhayan" varchar(100) DEFAULT NULL,
  "submitted_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "status" VARCHAR(255) DEFAULT 'pending',
  "land_ownership" varchar(100) DEFAULT NULL,
  "land_ownership_other" varchar(255) DEFAULT NULL,
  "house_ownership" varchar(100) DEFAULT NULL,
  "house_ownership_other" varchar(255) DEFAULT NULL,
  "farmland" varchar(100) DEFAULT NULL,
  "cooking_energy" varchar(100) DEFAULT NULL,
  "cooking_energy_other" varchar(255) DEFAULT NULL,
  "toilet_type" varchar(100) DEFAULT NULL,
  "toilet_type_other" varchar(255) DEFAULT NULL,
  "electricity_source" varchar(100) DEFAULT NULL,
  "electricity_source_other" varchar(255) DEFAULT NULL,
  "water_source" varchar(100) DEFAULT NULL,
  "water_source_other" varchar(255) DEFAULT NULL,
  "waste_disposal" varchar(100) DEFAULT NULL,
  "waste_disposal_other" varchar(255) DEFAULT NULL,
  "appliances" text DEFAULT NULL,
  "transportation" text DEFAULT NULL,
  "transportation_other" varchar(255) DEFAULT NULL,
  "business" text DEFAULT NULL,
  "business_other" varchar(255) DEFAULT NULL,
  "contraceptive" text DEFAULT NULL,
  "interviewer" varchar(255) DEFAULT NULL,
  "interviewer_title" varchar(255) DEFAULT NULL,
  "resident_disability" varchar(255) DEFAULT NULL,
  "resident_organization" varchar(255) DEFAULT NULL
) ;

--
-- Dumping data for table "resident_registrations"
--


-- --------------------------------------------------------

--
-- Table structure for table "resident_status"
--

CREATE TABLE "resident_status" (
  "id" INTEGER NOT NULL,
  "resident_id" INTEGER NOT NULL,
  "resident_name" varchar(255) NOT NULL,
  "record_status" VARCHAR(255) DEFAULT 'good',
  "total_complaints" INTEGER DEFAULT 0,
  "total_incidents" INTEGER DEFAULT 0,
  "pending_cases" INTEGER DEFAULT 0,
  "resolved_cases" INTEGER DEFAULT 0,
  "requires_captain_clearance" BOOLEAN DEFAULT false,
  "captain_clearance_granted" BOOLEAN DEFAULT false,
  "captain_clearance_date" TIMESTAMP DEFAULT NULL,
  "captain_clearance_reason" text DEFAULT NULL,
  "captain_clearance_expires" TIMESTAMP DEFAULT NULL,
  "last_incident_date" TIMESTAMP DEFAULT NULL,
  "last_updated" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table "rfid_access_logs"
--

CREATE TABLE "rfid_access_logs" (
  "id" INTEGER NOT NULL,
  "user_id" INTEGER DEFAULT NULL,
  "rfid_tag" varchar(20) DEFAULT NULL,
  "full_name" varchar(255) DEFAULT NULL,
  "access_time" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table "rfid_registrations"
--

CREATE TABLE "rfid_registrations" (
  "id" INTEGER NOT NULL,
  "rfid_number" varchar(50) NOT NULL,
  "first_name" varchar(100) NOT NULL,
  "middle_name" varchar(100) DEFAULT NULL,
  "last_name" varchar(100) NOT NULL,
  "birth_date" date NOT NULL,
  "contact_number" varchar(20) NOT NULL,
  "address" text NOT NULL,
  "card_type" VARCHAR(255) DEFAULT 'resident',
  "status" VARCHAR(255) DEFAULT 'pending',
  "issued_date" date DEFAULT NULL,
  "expires_date" date DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

-- --------------------------------------------------------

--
-- Table structure for table "rfid_users"
--

CREATE TABLE "rfid_users" (
  "id" INTEGER NOT NULL,
  "rfid_tag" varchar(20) NOT NULL,
  "full_name" varchar(255) NOT NULL,
  "email" varchar(255) DEFAULT NULL,
  "phone" varchar(20) DEFAULT NULL,
  "address" text DEFAULT NULL,
  "id_type" VARCHAR(255) DEFAULT 'National ID',
  "id_number" varchar(50) DEFAULT NULL,
  "status" VARCHAR(255) DEFAULT 'active',
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

-- --------------------------------------------------------

--
-- Table structure for table "scanned_rfid_codes"
--

CREATE TABLE "scanned_rfid_codes" (
  "id" INTEGER NOT NULL,
  "rfid_code" varchar(50) NOT NULL,
  "status" VARCHAR(255) DEFAULT 'available',
  "scanned_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "assigned_at" timestamp NULL DEFAULT NULL,
  "assigned_to_resident_id" INTEGER DEFAULT NULL,
  "assigned_to_email" varchar(255) DEFAULT NULL,
  "scanned_by_admin_id" INTEGER DEFAULT NULL,
  "notes" text DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Dumping data for table "scanned_rfid_codes"
--


-- --------------------------------------------------------

--
-- Table structure for table "services"
--

CREATE TABLE "services" (
  "id" INTEGER NOT NULL,
  "title" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "icon" varchar(50) NOT NULL,
  "button_text" varchar(100) NOT NULL,
  "button_link" varchar(255) NOT NULL,
  "is_featured" BOOLEAN DEFAULT false,
  "features" text DEFAULT NULL,
  "display_order" INTEGER DEFAULT 0,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Dumping data for table "services"
--


-- --------------------------------------------------------

--
-- Table structure for table "updates"
--

CREATE TABLE "updates" (
  "id" INTEGER NOT NULL,
  "title" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "badge_text" varchar(50) NOT NULL,
  "badge_type" VARCHAR(255) DEFAULT 'info',
  "date" varchar(50) NOT NULL,
  "status" varchar(50) NOT NULL,
  "is_priority" BOOLEAN DEFAULT false,
  "display_order" INTEGER DEFAULT 0,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Dumping data for table "updates"
--


-- --------------------------------------------------------

--
-- Table structure for table "user_reports"
--

CREATE TABLE "user_reports" (
  "id" INTEGER NOT NULL,
  "user_id" INTEGER NOT NULL,
  "incident_type" varchar(100) NOT NULL,
  "location" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "priority" VARCHAR(255) DEFAULT 'medium',
  "contact_number" varchar(20) NOT NULL,
  "proof_image" varchar(255) DEFAULT NULL ,
  "status" VARCHAR(255) DEFAULT 'pending',
  "admin_notes" text DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table "access_logs"
--
ALTER TABLE "access_logs"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "admin_logs"
--
ALTER TABLE "admin_logs"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "admin_users"
--
ALTER TABLE "admin_users"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "username" UNIQUE ("username"),
  ADD CONSTRAINT "email" UNIQUE ("email");

--
-- Indexes for table "barangay_blotter"
--
ALTER TABLE "barangay_blotter"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "blotter_number" UNIQUE ("blotter_number");

--
-- Indexes for table "blotter_attachments"
--
ALTER TABLE "blotter_attachments"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "business_applications"
--
ALTER TABLE "business_applications"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "captain_clearances"
--
ALTER TABLE "captain_clearances"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "certificate_requests"
--
ALTER TABLE "certificate_requests"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "family_disabilities"
--
ALTER TABLE "family_disabilities"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "family_members"
--
ALTER TABLE "family_members"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "family_organizations"
--
ALTER TABLE "family_organizations"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "notifications"
--
ALTER TABLE "notifications"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "queue_counters"
--
ALTER TABLE "queue_counters"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "counter_number" UNIQUE ("counter_number");

--
-- Indexes for table "queue_services"
--
ALTER TABLE "queue_services"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "service_code" UNIQUE ("service_code");

--
-- Indexes for table "queue_tickets"
--
ALTER TABLE "queue_tickets"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "ticket_number" UNIQUE ("ticket_number");

--
-- Indexes for table "queue_windows"
--
ALTER TABLE "queue_windows"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "window_number" UNIQUE ("window_number");

--
-- Indexes for table "residents"
--
ALTER TABLE "residents"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "residents_email" UNIQUE ("email"),
  ADD CONSTRAINT "residents_phone" UNIQUE ("phone"),
  ADD CONSTRAINT "residents_rfid_code" UNIQUE ("rfid_code"),
  ADD CONSTRAINT "residents_rfid" UNIQUE ("rfid");

--
-- Indexes for table "resident_registrations"
--
ALTER TABLE "resident_registrations"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "resident_status"
--
ALTER TABLE "resident_status"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "resident_id" UNIQUE ("resident_id");

--
-- Indexes for table "rfid_access_logs"
--
ALTER TABLE "rfid_access_logs"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "rfid_registrations"
--
ALTER TABLE "rfid_registrations"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "rfid_number" UNIQUE ("rfid_number");

--
-- Indexes for table "rfid_users"
--
ALTER TABLE "rfid_users"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "rfid_tag" UNIQUE ("rfid_tag");

--
-- Indexes for table "scanned_rfid_codes"
--
ALTER TABLE "scanned_rfid_codes"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "rfid_code" UNIQUE ("rfid_code");

--
-- Indexes for table "services"
--
ALTER TABLE "services"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "updates"
--
ALTER TABLE "updates"
  ADD PRIMARY KEY ("id");

--
-- Indexes for table "user_reports"
--
ALTER TABLE "user_reports"
  ADD PRIMARY KEY ("id");

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table "access_logs"
--
ALTER TABLE "access_logs"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "admin_logs"
--
ALTER TABLE "admin_logs"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "admin_users"
--
ALTER TABLE "admin_users"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "barangay_blotter"
--
ALTER TABLE "barangay_blotter"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "blotter_attachments"
--
ALTER TABLE "blotter_attachments"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "business_applications"
--
ALTER TABLE "business_applications"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "captain_clearances"
--
ALTER TABLE "captain_clearances"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "certificate_requests"
--
ALTER TABLE "certificate_requests"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "family_disabilities"
--
ALTER TABLE "family_disabilities"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "family_members"
--
ALTER TABLE "family_members"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "family_organizations"
--
ALTER TABLE "family_organizations"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "notifications"
--
ALTER TABLE "notifications"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "queue_counters"
--
ALTER TABLE "queue_counters"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "queue_services"
--
ALTER TABLE "queue_services"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "queue_tickets"
--
ALTER TABLE "queue_tickets"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "queue_windows"
--
ALTER TABLE "queue_windows"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "residents"
--
ALTER TABLE "residents"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "resident_registrations"
--
ALTER TABLE "resident_registrations"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "resident_status"
--
ALTER TABLE "resident_status"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "rfid_access_logs"
--
ALTER TABLE "rfid_access_logs"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "rfid_registrations"
--
ALTER TABLE "rfid_registrations"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "rfid_users"
--
ALTER TABLE "rfid_users"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "scanned_rfid_codes"
--
ALTER TABLE "scanned_rfid_codes"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "services"
--
ALTER TABLE "services"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "updates"
--
ALTER TABLE "updates"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- AUTO_INCREMENT for table "user_reports"
--
ALTER TABLE "user_reports"
  ALTER COLUMN "id" ADD GENERATED BY DEFAULT AS IDENTITY;

--
-- Constraints for dumped tables
--

--
-- Constraints for table "access_logs"
--
ALTER TABLE "access_logs"
  ADD CONSTRAINT "access_logs_ibfk_1" FOREIGN KEY ("resident_id") REFERENCES "residents" ("id") ON DELETE SET NULL;

--
-- Constraints for table "blotter_attachments"
--
ALTER TABLE "blotter_attachments"
  ADD CONSTRAINT "blotter_attachments_ibfk_1" FOREIGN KEY ("blotter_id") REFERENCES "barangay_blotter" ("id") ON DELETE CASCADE;

--
-- Constraints for table "business_applications"
--
ALTER TABLE "business_applications"
  ADD CONSTRAINT "business_applications_ibfk_1" FOREIGN KEY ("user_id") REFERENCES "residents" ("id") ON DELETE SET NULL;

--
-- Constraints for table "captain_clearances"
--
ALTER TABLE "captain_clearances"
  ADD CONSTRAINT "captain_clearances_ibfk_1" FOREIGN KEY ("resident_id") REFERENCES "residents" ("id") ON DELETE CASCADE;

--
-- Constraints for table "certificate_requests"
--
ALTER TABLE "certificate_requests"
  ADD CONSTRAINT "certificate_requests_ibfk_1" FOREIGN KEY ("user_id") REFERENCES "residents" ("id"),
  ADD CONSTRAINT "fk_cert_queue_ticket" FOREIGN KEY ("queue_ticket_id") REFERENCES "queue_tickets" ("id") ON DELETE SET NULL;

--
-- Constraints for table "family_disabilities"
--
ALTER TABLE "family_disabilities"
  ADD CONSTRAINT "family_disabilities_ibfk_1" FOREIGN KEY ("registration_id") REFERENCES "resident_registrations" ("id") ON DELETE CASCADE;

--
-- Constraints for table "family_members"
--
ALTER TABLE "family_members"
  ADD CONSTRAINT "family_members_ibfk_1" FOREIGN KEY ("registration_id") REFERENCES "resident_registrations" ("id") ON DELETE CASCADE;

--
-- Constraints for table "family_organizations"
--
ALTER TABLE "family_organizations"
  ADD CONSTRAINT "family_organizations_ibfk_1" FOREIGN KEY ("registration_id") REFERENCES "resident_registrations" ("id") ON DELETE CASCADE;

--
-- Constraints for table "queue_counters"
--
ALTER TABLE "queue_counters"
  ADD CONSTRAINT "queue_counters_ibfk_1" FOREIGN KEY ("service_id") REFERENCES "queue_services" ("id") ON DELETE SET NULL,
  ADD CONSTRAINT "queue_counters_ibfk_2" FOREIGN KEY ("current_ticket_id") REFERENCES "queue_tickets" ("id") ON DELETE SET NULL;

--
-- Constraints for table "queue_tickets"
--
ALTER TABLE "queue_tickets"
  ADD CONSTRAINT "queue_tickets_ibfk_1" FOREIGN KEY ("service_id") REFERENCES "queue_services" ("id") ON DELETE CASCADE;

--
-- Constraints for table "queue_windows"
--
ALTER TABLE "queue_windows"
  ADD CONSTRAINT "queue_windows_ibfk_1" FOREIGN KEY ("service_id") REFERENCES "queue_services" ("id") ON DELETE SET NULL,
  ADD CONSTRAINT "queue_windows_ibfk_2" FOREIGN KEY ("current_ticket_id") REFERENCES "queue_tickets" ("id") ON DELETE SET NULL;

--
-- Constraints for table "residents"
--
ALTER TABLE "residents"
  ADD CONSTRAINT "fk_residents_created_by" FOREIGN KEY ("created_by") REFERENCES "residents" ("id") ON DELETE SET NULL;

--
-- Constraints for table "resident_status"
--
ALTER TABLE "resident_status"
  ADD CONSTRAINT "resident_status_ibfk_1" FOREIGN KEY ("resident_id") REFERENCES "residents" ("id") ON DELETE CASCADE;

--
-- Constraints for table "rfid_access_logs"
--
ALTER TABLE "rfid_access_logs"
  ADD CONSTRAINT "rfid_access_logs_ibfk_1" FOREIGN KEY ("user_id") REFERENCES "rfid_users" ("id") ON DELETE CASCADE;

--
-- Constraints for table "scanned_rfid_codes"
--
ALTER TABLE "scanned_rfid_codes"
  ADD CONSTRAINT "scanned_rfid_codes_ibfk_1" FOREIGN KEY ("scanned_by_admin_id") REFERENCES "admin_users" ("id") ON DELETE SET NULL;

--
-- Constraints for table "user_reports"
--
ALTER TABLE "user_reports"
  ADD CONSTRAINT "user_reports_ibfk_1" FOREIGN KEY ("user_id") REFERENCES "residents" ("id") ON DELETE CASCADE;
COMMIT;




