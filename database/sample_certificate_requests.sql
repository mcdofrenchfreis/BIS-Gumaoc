-- Sample Certificate Requests with 4 unique certificate types
-- Each user has a different certificate type as requested

INSERT INTO `certificate_requests`(`id`, `full_name`, `address`, `mobile_number`, `civil_status`, `gender`, `birth_date`, `birth_place`, `citizenship`, `years_of_residence`, `certificate_type`, `purpose`, `submitted_at`, `status`) 
VALUES 
(1, 'Maria Santos Cruz', '123 Purok 1, Barangay Gumaoc East, San Carlos City, Pangasinan', '09123456789', 'Married', 'Female', '1985-03-15', 'San Carlos City, Pangasinan', 'Filipino', 15, 'BRGY. CLEARANCE', 'For employment application at ABC Company', '2024-01-15 09:30:00', 'pending'),

(2, 'Juan Dela Cruz Reyes', '456 Purok 2, Barangay Gumaoc East, San Carlos City, Pangasinan', '09234567890', 'Single', 'Male', '1992-07-22', 'Dagupan City, Pangasinan', 'Filipino', 8, 'BRGY. INDIGENCY', 'For scholarship application at State University', '2024-01-16 14:15:00', 'processing'),

(3, 'Ana Maria Gonzales', '789 Purok 3, Barangay Gumaoc East, San Carlos City, Pangasinan', '09345678901', 'Widowed', 'Female', '1978-11-08', 'San Carlos City, Pangasinan', 'Filipino', 25, 'CEDULA', 'For business permit renewal', '2024-01-17 11:45:00', 'ready'),

(4, 'Pedro Martinez Aquino', '321 Purok 4, Barangay Gumaoc East, San Carlos City, Pangasinan', '09456789012', 'Married', 'Male', '1980-05-12', 'Urdaneta City, Pangasinan', 'Filipino', 12, 'PROOF OF RESIDENCY', 'For bank loan application', '2024-01-18 16:20:00', 'released');
