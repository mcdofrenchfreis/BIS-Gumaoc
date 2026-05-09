import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.base import MIMEBase
from email import encoders
import os
from django.conf import settings
import logging

logger = logging.getLogger(__name__)


class EmailService:
    def __init__(self):
        self.smtp_server = getattr(settings, 'EMAIL_HOST', 'smtp.gmail.com')
        self.smtp_port = getattr(settings, 'EMAIL_PORT', 587)
        self.smtp_username = getattr(settings, 'EMAIL_HOST_USER', '')
        self.smtp_password = getattr(settings, 'EMAIL_HOST_PASSWORD', '')
        self.from_email = getattr(settings, 'DEFAULT_FROM_EMAIL', self.smtp_username)
        self.use_tls = getattr(settings, 'EMAIL_USE_TLS', True)

    def send_email(self, to_email, subject, body, is_html=False, attachments=None):
        try:
            msg = MIMEMultipart()
            msg['From'] = self.from_email
            msg['To'] = to_email
            msg['Subject'] = subject

            if is_html:
                msg.attach(MIMEText(body, 'html'))
            else:
                msg.attach(MIMEText(body, 'plain'))

            if attachments:
                for attachment in attachments:
                    if os.path.isfile(attachment):
                        part = MIMEBase('application', 'octet-stream')
                        with open(attachment, 'rb') as file:
                            part.set_payload(file.read())
                        encoders.encode_base64(part)
                        part.add_header(
                            'Content-Disposition',
                            f'attachment; filename= {os.path.basename(attachment)}'
                        )
                        msg.attach(part)

            server = smtplib.SMTP(self.smtp_server, self.smtp_port)
            if self.use_tls:
                server.starttls()
            server.login(self.smtp_username, self.smtp_password)
            server.send_message(msg)
            server.quit()

            logger.info(f"Email sent successfully to {to_email}")
            return True
        except Exception as e:
            logger.error(f"Failed to send email to {to_email}: {str(e)}")
            return False

    def send_certificate_notification(self, to_email, full_name, certificate_type, status):
        subject = f"Certificate Request Update - {certificate_type}"
        body = f"""
        Dear {full_name},
        
        Your request for {certificate_type} has been {status}.
        
        Please log in to your account for more details.
        
        Best regards,
        Barangay Information System - Gumaoc
        """
        return self.send_email(to_email, subject, body)

    def send_queue_notification(self, to_email, full_name, ticket_number, queue_position, estimated_time):
        subject = f"Queue Ticket Generated - {ticket_number}"
        body = f"""
        Dear {full_name},
        
        Your queue ticket number is: {ticket_number}
        Your position in queue: {queue_position}
        Estimated time: {estimated_time}
        
        Please arrive at the barangay hall at least 10 minutes before your estimated time.
        
        Best regards,
        Barangay Information System - Gumaoc
        """
        return self.send_email(to_email, subject, body)

    def send_business_application_notification(self, to_email, full_name, business_name, status):
        subject = f"Business Application Update - {business_name}"
        body = f"""
        Dear {full_name},
        
        Your business application for "{business_name}" has been {status}.
        
        Please log in to your account for more details.
        
        Best regards,
        Barangay Information System - Gumaoc
        """
        return self.send_email(to_email, subject, body)

    def send_blotter_notification(self, to_email, full_name, blotter_number, status):
        subject = f"Blotter Report Update - {blotter_number}"
        body = f"""
        Dear {full_name},
        
        Your blotter report (#{blotter_number}) has been {status}.
        
        Please log in to your account for more details.
        
        Best regards,
        Barangay Information System - Gumaoc
        """
        return self.send_email(to_email, subject, body)
