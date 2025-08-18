<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';
require_once 'email_config.php';

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setupSMTP();
    }
    
    private function setupSMTP() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_ENCRYPTION;
            $this->mailer->Port = SMTP_PORT;
            
            // Default sender
            $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
            
        } catch (Exception $e) {
            error_log("SMTP setup failed: " . $e->getMessage());
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
}
?>