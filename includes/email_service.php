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
}
?>