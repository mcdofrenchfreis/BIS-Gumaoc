<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'email_config.php';

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setupSMTP();
    }
    
    private function setupSMTP() {
        try {
            // Check if email is configured
            if (SMTP_USERNAME === 'your-gmail@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
                error_log('WARNING: Email not configured - using placeholder credentials');
                return false;
            }
            
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_ENCRYPTION;
            $this->mailer->Port = SMTP_PORT;
            
            // Enable verbose debug output (comment out in production)
            // $this->mailer->SMTPDebug = 2;
            
            // Default sender
            $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
            
            return true;
            
        } catch (Exception $e) {
            error_log("SMTP setup failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendOTP($email, $otp, $name) {
        try {
            // Recipients
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset OTP - GUMAOC';
            
            $htmlBody = $this->getOTPEmailTemplate($name, $otp);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Dear $name,\n\nYour password reset OTP is: $otp\n\nThis OTP will expire in 15 minutes.\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nGUMAOC Team";
            
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
    
    public function sendWelcomeEmail($email, $name, $tempPassword = null) {
        try {
            // Recipients
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to GUMAOC - Account Created';
            
            $htmlBody = $this->getWelcomeEmailTemplate($name, $tempPassword);
            $this->mailer->Body = $htmlBody;
            
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Welcome email failed: " . $e->getMessage());
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
    
    private function getOTPEmailTemplate($name, $otp) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .otp-box { background: white; border: 2px solid #667eea; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
                .otp-code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>GUMAOC System</h1>
                    <p>Password Reset Request</p>
                </div>
                <div class='content'>
                    <h2>Hello $name,</h2>
                    <p>You have requested to reset your password. Please use the following One-Time Password (OTP) to proceed:</p>
                    
                    <div class='otp-box'>
                        <p>Your OTP Code:</p>
                        <div class='otp-code'>$otp</div>
                        <p><small>This code will expire in 15 minutes</small></p>
                    </div>
                    
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>This OTP is valid for 15 minutes only</li>
                        <li>Do not share this code with anyone</li>
                        <li>If you did not request this reset, please ignore this email</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>This is an automated message from GUMAOC System. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    public function sendRFIDActivationEmail($email, $name, $rfidCode, $tempPassword) {
        try {
            // Check if email is configured
            if (SMTP_USERNAME === 'your-gmail@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
                error_log("Email not configured - RFID: $rfidCode, Password: $tempPassword for $email ($name)");
                return false;
            }
            
            // Recipients
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'GUMAOC Account Activated - RFID & Login Credentials';
            
            $htmlBody = $this->getRFIDActivationEmailTemplate($name, $rfidCode, $tempPassword);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Dear $name,\n\nYour GUMAOC account has been activated!\n\nRFID Code: $rfidCode\nTemporary Password: $tempPassword\n\nPlease change your password after logging in.\n\nBest regards,\nGUMAOC Team";
            
            $result = $this->mailer->send();
            error_log("RFID activation email sent successfully to $email ($name)");
            return $result;
            
        } catch (Exception $e) {
            error_log("RFID activation email failed for $email: " . $e->getMessage());
            error_log("RFID credentials for $email ($name): RFID=$rfidCode, Password=$tempPassword");
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
    
    public static function generateUniqueRFID($pdo, $length = 10) {
        // First try to get an available pre-scanned RFID code
        $available_rfid = self::getAvailableRFIDCode($pdo);
        
        if ($available_rfid) {
            return $available_rfid;
        }
        
        // Fallback to generating random RFID if no pre-scanned codes available
        do {
            // Generate RFID with numbers and uppercase letters
            $rfid = '';
            $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < $length; $i++) {
                $rfid .= $chars[random_int(0, strlen($chars) - 1)];
            }
            
            // Check if RFID already exists in residents table
            $stmt = $pdo->prepare("SELECT id FROM residents WHERE rfid_code = ? OR rfid = ?");
            $stmt->execute([$rfid, $rfid]);
            $exists = $stmt->fetch();
            
            // Also check if it exists in scanned RFID codes
            if (!$exists) {
                $stmt = $pdo->prepare("SELECT id FROM scanned_rfid_codes WHERE rfid_code = ?");
                $stmt->execute([$rfid]);
                $exists = $stmt->fetch();
            }
            
        } while ($exists);
        
        return $rfid;
    }
    
    public static function getAvailableRFIDCode($pdo) {
        try {
            // Get the oldest available RFID code
            $stmt = $pdo->prepare("
                SELECT id, rfid_code 
                FROM scanned_rfid_codes 
                WHERE status = 'available' 
                ORDER BY scanned_at ASC 
                LIMIT 1
            ");
            $stmt->execute();
            $rfid_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rfid_data) {
                return $rfid_data['rfid_code'];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error getting available RFID code: " . $e->getMessage());
            return null;
        }
    }
    
    public static function assignRFIDCode($pdo, $rfid_code, $resident_id = null, $email = null) {
        try {
            $stmt = $pdo->prepare("
                UPDATE scanned_rfid_codes 
                SET status = 'assigned', 
                    assigned_at = NOW(), 
                    assigned_to_resident_id = ?, 
                    assigned_to_email = ? 
                WHERE rfid_code = ? AND status = 'available'
            ");
            
            return $stmt->execute([$resident_id, $email, $rfid_code]);
            
        } catch (Exception $e) {
            error_log("Error assigning RFID code: " . $e->getMessage());
            return false;
        }
    }
    
    public static function generateTempPassword($length = 8) {
        // Generate a readable temporary password
        $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
    
    public function sendRegistrationConfirmationEmail($email, $name) {
        try {
            // Check if email is configured
            if (SMTP_USERNAME === 'your-gmail@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
                error_log("Email not configured - Registration confirmation for $email ($name)");
                return false;
            }
            
            // Recipients
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'GUMAOC Registration Confirmation';
            
            $htmlBody = $this->getRegistrationConfirmationEmailTemplate($name);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Dear $name,\n\nThank you for registering with GUMAOC Barangay Information System. Your registration has been received and will be processed as soon as possible.\n\nYou will receive your login credentials once your registration is approved.\n\nBest regards,\nGUMAOC Team";
            
            $result = $this->mailer->send();
            error_log("Registration confirmation email sent successfully to $email ($name)");
            return $result;
            
        } catch (Exception $e) {
            error_log("Registration confirmation email failed for $email: " . $e->getMessage());
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
    
    private function getRegistrationConfirmationEmailTemplate($name) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .confirmation-box { background: white; border: 2px solid #4caf50; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .important { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏛️ GUMAOC System</h1>
                    <p>Registration Confirmation</p>
                </div>
                <div class='content'>
                    <h2>Hello $name,</h2>
                    <p>Thank you for registering with the GUMAOC East Barangay Information System!</p>
                    
                    <div class='confirmation-box'>
                        <h3>✅ Registration Received</h3>
                        <p>Your registration has been successfully submitted and is now in our processing queue.</p>
                        <p><strong>What happens next:</strong></p>
                        <ul>
                            <li>Your registration will be reviewed by our staff</li>
                            <li>You will receive your login credentials via email once approved</li>
                            <li>You'll be notified when your account is ready for use</li>
                        </ul>
                    </div>
                    
                    <div class='important'>
                        <h4>📋 Important Information:</h4>
                        <ul>
                            <li>Please keep this email for your records</li>
                            <li>Processing typically takes 1-2 business days</li>
                            <li>If you don't receive credentials within 3 days, please contact the barangay office</li>
                        </ul>
                    </div>
                    
                    <p><strong>Available Services (After Approval):</strong></p>
                    <ul>
                        <li>📄 Certificate Requests</li>
                        <li>🏢 Business Applications</li>
                        <li>📋 Document Processing</li>
                        <li>🎫 Queue Management</li>
                        <li>👤 Profile Management</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>This is an automated message from GUMAOC System. Please do not reply to this email.</p>
                    <p>For support, visit the barangay office or contact your administrator.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getRFIDActivationEmailTemplate($name, $rfidCode, $tempPassword) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .credentials-box { background: white; border: 2px solid #4caf50; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .credential-item { margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; }
                .credential-label { font-weight: bold; color: #2e7d32; font-size: 14px; }
                .credential-value { font-size: 20px; font-weight: bold; color: #1565c0; letter-spacing: 2px; font-family: monospace; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .important { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏷️ GUMAOC System</h1>
                    <p>Account Activated Successfully!</p>
                </div>
                <div class='content'>
                    <h2>Hello $name,</h2>
                    <p>Congratulations! Your GUMAOC account has been successfully activated. You can now access all barangay services.</p>
                    
                    <div class='credentials-box'>
                        <h3>🔐 Your Login Credentials</h3>
                        <div class='credential-item'>
                            <div class='credential-label'>🏷️ RFID Code:</div>
                            <div class='credential-value'>$rfidCode</div>
                        </div>
                        <div class='credential-item'>
                            <div class='credential-label'>🔒 Temporary Password:</div>
                            <div class='credential-value'>$tempPassword</div>
                        </div>
                    </div>
                    
                    <div class='important'>
                        <h4>📋 Important Instructions:</h4>
                        <ul>
                            <li><strong>RFID Login:</strong> Use your RFID code for quick access</li>
                            <li><strong>Manual Login:</strong> Use your email and temporary password</li>
                            <li><strong>Security:</strong> Please change your password after first login</li>
                            <li><strong>Keep Safe:</strong> Store your RFID code securely</li>
                        </ul>
                    </div>
                    
                    <p><strong>Available Services:</strong></p>
                    <ul>
                        <li>📄 Certificate Requests</li>
                        <li>🏢 Business Applications</li>
                        <li>📋 Document Processing</li>
                        <li>🎫 Queue Management</li>
                        <li>👤 Profile Management</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>This is an automated message from GUMAOC System. Please do not reply to this email.</p>
                    <p>For support, visit the barangay office or contact your administrator.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    public function sendFamilyMemberNotification($email, $familyMemberName, $registrantName, $relationship = '') {
        try {
            // Recipients
            $this->mailer->addAddress($email, $familyMemberName);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Family Member Registration - GUMAOC East Barangay Information System';
            
            $htmlBody = $this->getFamilyMemberNotificationTemplate($familyMemberName, $registrantName, $relationship);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Dear $familyMemberName,\n\n$registrantName has added you as a family member" . ($relationship ? " ($relationship)" : '') . " in the GUMAOC East Barangay Information System resident registration.\n\nThis registration helps our barangay maintain accurate records for better service delivery to our community.\n\nIf you have any questions, please contact the barangay office.\n\nBest regards,\nGUMAOC East Barangay Office";
            
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Family member notification email failed: " . $e->getMessage());
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
    
    private function getWelcomeEmailTemplate($name, $tempPassword) {
        $passwordInfo = $tempPassword ? "<p><strong>Temporary Password:</strong> $tempPassword</p><p>Please change your password after logging in.</p>" : "";
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to GUMAOC</h1>
                </div>
                <div class='content'>
                    <h2>Hello $name,</h2>
                    <p>Welcome to the GUMAOC System! Your account has been successfully created.</p>
                    $passwordInfo
                    <p>You can now access our services using your RFID card or login credentials.</p>
                    <p>Thank you for being part of our community!</p>
                </div>
                <div class='footer'>
                    <p>GUMAOC System - Serving our community with excellence</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getFamilyMemberNotificationTemplate($familyMemberName, $registrantName, $relationship) {
        $relationshipText = $relationship ? " as <strong>$relationship</strong>" : '';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .notification-box { background: white; border: 2px solid #4caf50; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .registrant-info { background: #e8f5e9; border-radius: 5px; padding: 15px; margin: 15px 0; }
                .info-label { font-weight: bold; color: #1b5e20; }
                .info-value { color: #2e7d32; font-size: 16px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .important { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
                .barangay-logo { font-size: 24px; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='barangay-logo'>🏛️</div>
                    <h1>GUMAOC East Barangay</h1>
                    <p>Information System Notification</p>
                </div>
                <div class='content'>
                    <h2>Hello $familyMemberName,</h2>
                    <p>We hope this message finds you well.</p>
                    
                    <div class='notification-box'>
                        <h3>👨‍👩‍👧‍👦 Family Member Registration Notice</h3>
                        <p><strong>$registrantName</strong> has added you$relationshipText as a family member in the GUMAOC East Barangay Information System resident registration.</p>
                        
                        <div class='registrant-info'>
                            <div class='info-label'>Registered by:</div>
                            <div class='info-value'>$registrantName</div>
                            " . ($relationship ? "<div class='info-label'>Your relationship:</div><div class='info-value'>$relationship</div>" : '') . "
                            <div class='info-label'>Registration date:</div>
                            <div class='info-value'>" . date('F j, Y') . "</div>
                        </div>
                    </div>
                    
                    <div class='important'>
                        <h4>📋 About This Registration:</h4>
                        <ul>
                            <li><strong>Purpose:</strong> This registration helps our barangay maintain accurate family records</li>
                            <li><strong>Benefits:</strong> Better service delivery and community planning</li>
                            <li><strong>Privacy:</strong> Your information is securely stored and used only for official barangay purposes</li>
                            <li><strong>Questions:</strong> Contact the barangay office if you have any concerns</li>
                        </ul>
                    </div>
                    
                    <p><strong>Available Barangay Services:</strong></p>
                    <ul>
                        <li>📄 Certificate Requests (Barangay Clearance, Residency Certificate)</li>
                        <li>🏢 Business Permit Applications</li>
                        <li>📋 Document Processing Services</li>
                        <li>🎫 Queue Management System</li>
                        <li>🆘 Emergency Response Services</li>
                    </ul>
                    
                    <p>Thank you for being part of our GUMAOC East community!</p>
                </div>
                <div class='footer'>
                    <p>This is an automated notification from GUMAOC East Barangay Information System.</p>
                    <p>📍 Barangay GUMAOC East, San Jose del Monte, Bulacan</p>
                    <p>For inquiries, please visit the barangay office during business hours.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Send approval email with login credentials
     * 
     * This email is sent when admin approves a resident registration.
     * Workflow:
     * 1. User registers -> gets confirmation email (no credentials)
     * 2. Admin approves -> account activated + approval email with RFID & password
     * 3. User can now login with credentials
     * 
     * @param string $email Recipient email
     * @param string $name Recipient name  
     * @param string $rfidCode Generated RFID code
     * @param string $tempPassword Temporary password
     * @return bool Success status
     */
    public function sendApprovalEmail($email, $name, $rfidCode, $tempPassword) {
        try {
            // Check if email is configured
            if (SMTP_USERNAME === 'your-gmail@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
                error_log("Email not configured - Approval: RFID: $rfidCode, Password: $tempPassword for $email ($name)");
                return false;
            }
            
            // Log attempt
            error_log("EmailService: Attempting to send approval email to $email ($name) with RFID: $rfidCode");
            
            // Recipients
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Registration Approved - GUMAOC Account Activated';
            
            $htmlBody = $this->getApprovalEmailTemplate($name, $rfidCode, $tempPassword);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Dear $name,\n\nCongratulations! Your GUMAOC resident registration has been approved and your account has been activated.\n\nRFID Code: $rfidCode\nTemporary Password: $tempPassword\n\nPlease change your password after logging in.\n\nBest regards,\nGUMAOC Team";
            
            $result = $this->mailer->send();
            error_log("EmailService: Approval email sent successfully to $email ($name)");
            return $result;
            
        } catch (Exception $e) {
            error_log("EmailService: Approval email failed for $email: " . $e->getMessage());
            error_log("EmailService: Approval credentials for $email ($name): RFID=$rfidCode, Password=$tempPassword");
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
    
    public function sendRejectionEmail($email, $name) {
        try {
            // Recipients
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Registration Status Update - GUMAOC';
            
            $htmlBody = $this->getRejectionEmailTemplate($name);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Dear $name,\n\nWe regret to inform you that your application for residency has been rejected. Please visit the barangay office for a follow-up consultation to address any concerns or missing requirements.\n\nThank you for your understanding.\n\nBest regards,\nGUMAOC Team";
            
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Rejection email failed: " . $e->getMessage());
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
    
    public function sendTestEmail($email, $name = 'Test User') {
        try {
            // Check if email is configured
            if (SMTP_USERNAME === 'your-gmail@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
                error_log("Email not configured - Test email for $email ($name)");
                return false;
            }
            
            error_log("EmailService: Sending test email to $email ($name)");
            
            // Recipients
            $this->mailer->addAddress($email, $name);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Test Email - GUMAOC System';
            $this->mailer->Body = "<h1>Test Email</h1><p>Hello $name,</p><p>This is a test email from the GUMAOC system to verify email functionality is working.</p>";
            $this->mailer->AltBody = "Hello $name, This is a test email from the GUMAOC system to verify email functionality is working.";
            
            $result = $this->mailer->send();
            error_log("EmailService: Test email sent successfully to $email");
            return $result;
            
        } catch (Exception $e) {
            error_log("EmailService: Test email failed for $email: " . $e->getMessage());
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
    
    private function getApprovalEmailTemplate($name, $rfidCode, $tempPassword) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .credentials-box { background: white; border: 2px solid #4caf50; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .credential-item { margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; }
                .credential-label { font-weight: bold; color: #2e7d32; font-size: 14px; }
                .credential-value { font-size: 20px; font-weight: bold; color: #1565c0; letter-spacing: 2px; font-family: monospace; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .important { background: #e8f5e9; border: 1px solid #4caf50; border-radius: 5px; padding: 15px; margin: 20px 0; }
                .success-badge { background: #4caf50; color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 GUMAOC System</h1>
                    <p><span class='success-badge'>APPROVED</span></p>
                    <p>Registration Successfully Approved!</p>
                </div>
                <div class='content'>
                    <h2>Congratulations $name!</h2>
                    <p>We are pleased to inform you that your resident registration application has been <strong>approved</strong>. Your account has been activated and you now have full access to all barangay services.</p>
                    
                    <div class='credentials-box'>
                        <h3>🔐 Your Login Credentials</h3>
                        <div class='credential-item'>
                            <div class='credential-label'>🏷️ RFID Code:</div>
                            <div class='credential-value'>$rfidCode</div>
                        </div>
                        <div class='credential-item'>
                            <div class='credential-label'>🔒 Temporary Password:</div>
                            <div class='credential-value'>$tempPassword</div>
                        </div>
                    </div>
                    
                    <div class='important'>
                        <h4>📋 Next Steps:</h4>
                        <ul>
                            <li><strong>Login Options:</strong> Use your RFID code for quick access or login with your email and temporary password</li>
                            <li><strong>Security:</strong> Please change your password after your first login for security</li>
                            <li><strong>RFID Card:</strong> Visit the barangay office to get your physical RFID card</li>
                            <li><strong>Services:</strong> You can now request certificates, apply for business permits, and access other barangay services</li>
                        </ul>
                    </div>
                    
                    <p><strong>Available Services:</strong></p>
                    <ul>
                        <li>📄 Barangay Clearance & Certificates</li>
                        <li>🏢 Business Permit Applications</li>
                        <li>📋 Document Processing</li>
                        <li>🎫 Queue Management System</li>
                        <li>👤 Profile Management</li>
                    </ul>
                    
                    <p>Welcome to the GUMAOC community! We look forward to serving you.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from GUMAOC System. Please do not reply to this email.</p>
                    <p>📍 For support, visit the barangay office or contact your administrator.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getRejectionEmailTemplate($name) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .info-box { background: white; border: 2px solid #f44336; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .important { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
                .status-badge { background: #f44336; color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; }
                .action-required { background: #ffebee; border: 1px solid #f44336; border-radius: 5px; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📋 GUMAOC System</h1>
                    <p><span class='status-badge'>REQUIRES ATTENTION</span></p>
                    <p>Registration Status Update</p>
                </div>
                <div class='content'>
                    <h2>Dear $name,</h2>
                    <p>Thank you for your interest in registering as a resident of Barangay GUMAOC East.</p>
                    
                    <div class='info-box'>
                        <h3>📄 Registration Status Update</h3>
                        <p>We regret to inform you that your application for residency registration requires further review and has been marked for follow-up.</p>
                    </div>
                    
                    <div class='action-required'>
                        <h4>📍 Action Required</h4>
                        <p><strong>Please visit the barangay office for a follow-up consultation.</strong></p>
                        <p>Our staff will:</p>
                        <ul>
                            <li>Review your application in detail</li>
                            <li>Identify any missing requirements</li>
                            <li>Provide guidance on next steps</li>
                            <li>Address any questions or concerns</li>
                        </ul>
                    </div>
                    
                    <div class='important'>
                        <h4>📋 What to Bring:</h4>
                        <ul>
                            <li>Valid government-issued ID</li>
                            <li>Proof of residence (utility bills, lease agreement, etc.)</li>
                            <li>Any supporting documents mentioned in your application</li>
                            <li>This email for reference</li>
                        </ul>
                    </div>
                    
                    <div class='info-box'>
                        <h4>🏢 Barangay Office Information</h4>
                        <p><strong>Address:</strong> Barangay GUMAOC East, San Jose del Monte, Bulacan</p>
                        <p><strong>Office Hours:</strong> Monday to Friday, 8:00 AM - 5:00 PM</p>
                        <p><strong>What to expect:</strong> A friendly consultation to help complete your registration</p>
                    </div>
                    
                    <p>We appreciate your understanding and look forward to assisting you at the barangay office. Our goal is to ensure all residents are properly registered and can access our services.</p>
                    
                    <p>Thank you for being part of our community.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from GUMAOC System. Please do not reply to this email.</p>
                    <p>📍 For immediate assistance, please visit the barangay office during business hours.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>
    }