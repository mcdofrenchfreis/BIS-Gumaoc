-- Dummy Census Registration Data for Testing
-- This script creates 5 diverse registration entries to test the enhanced admin view functionality

-- Registration 1: Large Family with Multiple Disabilities and Organizations
INSERT INTO `resident_registrations` (
    `first_name`, `middle_name`, `last_name`, `age`, `birth_date`, `gender`, `civil_status`, `contact_number`, `house_number`, `pangkabuhayan`, `cooking_energy`, `toilet_type`, `electricity_source`, `water_source`, `waste_disposal`, `appliances`, `transportation`, `business`, `contraceptive`, `status`, `submitted_at`, `interviewer`, `interviewer_title`
) VALUES (
    'Maria', 'Santos', 'Cruz', 34, '1990-03-15', 'Female', 'Married', '09171234567', '123', 'Teacher', 'LPG', 'Flush', 'Kuryente', 'Water District', 'Kinokolekta', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles', 'Kotse,Motorsiklo', 'Sari-Sari Store', 'Pills', 'pending', NOW(), 'Admin User', 'Data Entry Clerk'
);

-- Registration 2: Single Professional with Basic Info Only
INSERT INTO `resident_registrations` (
    `first_name`, `middle_name`, `last_name`, `age`, `birth_date`, `gender`, `civil_status`, `contact_number`, `house_number`, `pangkabuhayan`, `cooking_energy`, `toilet_type`, `electricity_source`, `water_source`, `waste_disposal`, `appliances`, `transportation`, `business`, `contraceptive`, `status`, `submitted_at`, `interviewer`, `interviewer_title`
) VALUES (
    'John', 'Miguel', 'Reyes', 28, '1996-08-22', 'Male', 'Single', '09287654321', '456', 'Software Engineer', 'Gaas', 'De-buhos', 'Kuryente', 'Nawasa', 'Sinusunog', 'Telebisyon,Refrigerator', 'Motorsiklo', '', 'Wala', 'approved', DATE_SUB(NOW(), INTERVAL 2 DAY), 'Admin User', 'Data Entry Clerk'
);

-- Registration 3: Senior Citizen with Organizations
INSERT INTO `resident_registrations` (
    `first_name`, `middle_name`, `last_name`, `age`, `birth_date`, `gender`, `civil_status`, `contact_number`, `house_number`, `pangkabuhayan`, `cooking_energy`, `toilet_type`, `electricity_source`, `water_source`, `waste_disposal`, `appliances`, `transportation`, `business`, `contraceptive`, `status`, `submitted_at`, `interviewer`, `interviewer_title`
) VALUES (
    'Rosa', 'Dela', 'Cruz', 68, '1956-12-05', 'Female', 'Widow', '09345678901', '789', 'Retired', 'Wood', 'Hinuhukay/Balon', 'Gaas', 'Poso Artesiyano', 'Hukay na may takip', 'Radyo/Stereo', 'Tricycle', '', 'Wala', 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY), 'Admin User', 'Data Entry Clerk'
);

-- Registration 4: Young Family with Disabilities
INSERT INTO `resident_registrations` (
    `first_name`, `middle_name`, `last_name`, `age`, `birth_date`, `gender`, `civil_status`, `contact_number`, `house_number`, `pangkabuhayan`, `cooking_energy`, `toilet_type`, `electricity_source`, `water_source`, `waste_disposal`, `appliances`, `transportation`, `business`, `contraceptive`, `status`, `submitted_at`, `interviewer`, `interviewer_title`
) VALUES (
    'Pedro', 'Jose', 'Garcia', 32, '1992-05-18', 'Male', 'Married', '09456789012', '321', 'Construction Worker', 'LPG', 'Flush', 'Kuryente', 'Water District', 'Kinokolekta', 'Radyo/Stereo,Telebisyon', 'Jeep', 'Rice Mill', 'Condom', 'rejected', DATE_SUB(NOW(), INTERVAL 5 DAY), 'Admin User', 'Data Entry Clerk'
);

-- Registration 5: Complete Family with All Data Types
INSERT INTO `resident_registrations` (
    `first_name`, `middle_name`, `last_name`, `age`, `birth_date`, `gender`, `civil_status`, `contact_number`, `house_number`, `pangkabuhayan`, `cooking_energy`, `toilet_type`, `electricity_source`, `water_source`, `waste_disposal`, `appliances`, `transportation`, `business`, `contraceptive`, `status`, `submitted_at`, `interviewer`, `interviewer_title`
) VALUES (
    'Ana', 'Luz', 'Villanueva', 29, '1995-11-30', 'Female', 'Married', '09567890123', '654', 'Nurse', 'LPG', 'Flush', 'Kuryente', 'Water District', 'Kinokolekta', 'Radyo/Stereo,Telebisyon,Refrigerator,Muwebles', 'Sasakyan,Kotse,Motorsiklo', 'Sari-Sari Store,Patahian', 'IUD', 'pending', DATE_SUB(NOW(), INTERVAL 3 HOUR), 'Admin User', 'Data Entry Clerk'
);

-- Get the registration IDs for inserting related data
SET @reg1_id = LAST_INSERT_ID() - 4;
SET @reg2_id = LAST_INSERT_ID() - 3;
SET @reg3_id = LAST_INSERT_ID() - 2;
SET @reg4_id = LAST_INSERT_ID() - 1;
SET @reg5_id = LAST_INSERT_ID();

-- Family Members for Registration 1 (Maria Cruz - Large Family)
INSERT INTO `family_members` (`registration_id`, `full_name`, `relationship`, `age`, `gender`, `civil_status`, `email`, `occupation`) VALUES
(@reg1_id, 'Roberto Santos Cruz', 'Spouse', 36, 'Male', 'Married', 'roberto.cruz@email.com', 'Engineer'),
(@reg1_id, 'Sofia Cruz Santos', 'Daughter', 12, 'Female', 'Single', '', 'Student'),
(@reg1_id, 'Miguel Cruz Santos', 'Son', 10, 'Male', 'Single', '', 'Student'),
(@reg1_id, 'Carmen Santos Cruz', 'Mother-in-law', 65, 'Female', 'Widow', 'carmen.santos@email.com', 'Retired'),
(@reg1_id, 'Luis Cruz Santos', 'Son', 8, 'Male', 'Single', '', 'Student');

-- Family Members for Registration 3 (Rosa Dela Cruz - Senior with Adult Children)
INSERT INTO `family_members` (`registration_id`, `full_name`, `relationship`, `age`, `gender`, `civil_status`, `email`, `occupation`) VALUES
(@reg3_id, 'Eduardo Dela Cruz Jr.', 'Son', 45, 'Male', 'Married', 'eduardo.delacruz@email.com', 'Driver'),
(@reg3_id, 'Melinda Cruz Soriano', 'Daughter', 42, 'Female', 'Married', 'melinda.soriano@email.com', 'Housewife');

-- Family Members for Registration 4 (Pedro Garcia - Young Family)
INSERT INTO `family_members` (`registration_id`, `full_name`, `relationship`, `age`, `gender`, `civil_status`, `email`, `occupation`) VALUES
(@reg4_id, 'Isabella Garcia Lopez', 'Spouse', 28, 'Female', 'Married', 'isabella.garcia@email.com', 'Housewife'),
(@reg4_id, 'Carlos Garcia Lopez', 'Son', 6, 'Male', 'Single', '', 'Student'),
(@reg4_id, 'Elena Garcia Lopez', 'Daughter', 4, 'Female', 'Single', '', 'Pre-school');

-- Family Members for Registration 5 (Ana Villanueva - Complete Family)
INSERT INTO `family_members` (`registration_id`, `full_name`, `relationship`, `age`, `gender`, `civil_status`, `email`, `occupation`) VALUES
(@reg5_id, 'Mark Anthony Villanueva', 'Spouse', 31, 'Male', 'Married', 'mark.villanueva@email.com', 'Business Owner'),
(@reg5_id, 'Sophie Villanueva Santos', 'Daughter', 7, 'Female', 'Single', '', 'Student'),
(@reg5_id, 'Gabriel Villanueva Santos', 'Son', 5, 'Male', 'Single', '', 'Pre-school'),
(@reg5_id, 'Lourdes Santos Villanueva', 'Mother', 58, 'Female', 'Widow', 'lourdes.santos@email.com', 'Retired Teacher');

-- Family Disabilities for Registration 1 (Maria Cruz)
INSERT INTO `family_disabilities` (`registration_id`, `name`, `disability_type`) VALUES
(@reg1_id, 'Miguel Cruz Santos', 'Hearing Impairment'),
(@reg1_id, 'Carmen Santos Cruz', 'Mobility Impairment - Uses Walking Cane');

-- Family Disabilities for Registration 4 (Pedro Garcia)
INSERT INTO `family_disabilities` (`registration_id`, `name`, `disability_type`) VALUES
(@reg4_id, 'Carlos Garcia Lopez', 'Autism Spectrum Disorder'),
(@reg4_id, 'Pedro Jose Garcia', 'Partial Vision Impairment');

-- Family Disabilities for Registration 5 (Ana Villanueva)
INSERT INTO `family_disabilities` (`registration_id`, `name`, `disability_type`) VALUES
(@reg5_id, 'Lourdes Santos Villanueva', 'Arthritis - Joint Mobility Issues');

-- Family Organizations for Registration 1 (Maria Cruz)
INSERT INTO `family_organizations` (`registration_id`, `name`, `organization_type`) VALUES
(@reg1_id, 'Maria Santos Cruz', 'Teachers Association of Bulacan'),
(@reg1_id, 'Roberto Santos Cruz', 'Engineers Society Philippines'),
(@reg1_id, 'Carmen Santos Cruz', 'Senior Citizens Association');

-- Family Organizations for Registration 3 (Rosa Dela Cruz)
INSERT INTO `family_organizations` (`registration_id`, `name`, `organization_type`) VALUES
(@reg3_id, 'Rosa Dela Cruz', 'Senior Citizens Club'),
(@reg3_id, 'Rosa Dela Cruz', 'Barangay Health Workers Association'),
(@reg3_id, 'Eduardo Dela Cruz Jr.', 'Drivers Union Local Chapter');

-- Family Organizations for Registration 5 (Ana Villanueva)
INSERT INTO `family_organizations` (`registration_id`, `name`, `organization_type`) VALUES
(@reg5_id, 'Ana Luz Villanueva', 'Philippine Nurses Association'),
(@reg5_id, 'Mark Anthony Villanueva', 'Chamber of Commerce'),
(@reg5_id, 'Ana Luz Villanueva', 'Barangay Health Committee'),
(@reg5_id, 'Lourdes Santos Villanueva', 'Retired Teachers Association');

-- Summary of Created Records:
-- Registration 1: Maria Cruz (Large family with 5 members, 2 disabilities, 3 organizations) - PENDING
-- Registration 2: John Reyes (Single professional, no additional data) - APPROVED  
-- Registration 3: Rosa Dela Cruz (Senior with 2 family members, 0 disabilities, 3 organizations) - PENDING
-- Registration 4: Pedro Garcia (Young family with 3 members, 2 disabilities, 0 organizations) - REJECTED
-- Registration 5: Ana Villanueva (Complete family with 4 members, 1 disability, 4 organizations) - PENDING