from django.db import models
from apps.residents.models import Resident
import json


class CertificateRequest(models.Model):
    CERTIFICATE_TYPES = [
        ('BRGY. CLEARANCE', 'Barangay Clearance'),
        ('BRGY. INDIGENCY', 'Barangay Indigency'),
        ('PROOF OF RESIDENCY', 'Proof of Residency'),
        ('CEDULA', 'Community Tax Certificate'),
        ('TRICYCLE PERMIT', 'Tricycle Permit'),
        ('BRGY. ID', 'Barangay ID'),
    ]
    
    STATUS_CHOICES = [
        ('pending', 'Pending'),
        ('processing', 'Processing'),
        ('ready', 'Ready'),
        ('released', 'Released'),
    ]
    
    user = models.ForeignKey(Resident, on_delete=models.CASCADE, related_name='certificate_requests')
    full_name = models.CharField(max_length=255)
    address = models.CharField(max_length=500)
    mobile_number = models.CharField(max_length=20, blank=True, null=True)
    civil_status = models.CharField(max_length=50, blank=True, null=True)
    gender = models.CharField(max_length=20, blank=True, null=True)
    birth_date = models.DateField()
    birth_place = models.CharField(max_length=255)
    citizenship = models.CharField(max_length=100, blank=True, null=True)
    years_of_residence = models.IntegerField(blank=True, null=True)
    certificate_type = models.CharField(max_length=100)
    purpose = models.TextField()
    additional_data = models.TextField(blank=True, null=True, help_text='JSON data')
    proof_image = models.CharField(max_length=255, blank=True, null=True)
    photo_2x2 = models.CharField(max_length=255, blank=True, null=True, help_text='2x2 passport photo')
    # Tricycle permit fields
    vehicle_make_type = models.CharField(max_length=255, blank=True, null=True)
    motor_no = models.CharField(max_length=100, blank=True, null=True)
    chassis_no = models.CharField(max_length=100, blank=True, null=True)
    plate_no = models.CharField(max_length=50, blank=True, null=True)
    vehicle_color = models.CharField(max_length=50, blank=True, null=True)
    year_model = models.IntegerField(blank=True, null=True)
    body_no = models.CharField(max_length=100, blank=True, null=True)
    operator_license = models.CharField(max_length=100, blank=True, null=True)
    tricycle_photo = models.CharField(max_length=255, blank=True, null=True)
    submitted_at = models.DateTimeField(auto_now_add=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    queue_ticket = models.ForeignKey('queue.QueueTicket', on_delete=models.SET_NULL, null=True, blank=True, related_name='certificate_requests')
    queue_ticket_number = models.CharField(max_length=20, blank=True, null=True)
    notes = models.TextField()
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'certificate_requests'

    def __str__(self):
        return f"{self.certificate_type} - {self.full_name}"


class CaptainClearance(models.Model):
    CLEARANCE_TYPE_CHOICES = [
        ('form_access', 'Form Access'),
        ('certificate_request', 'Certificate Request'),
        ('business_permit', 'Business Permit'),
        ('general', 'General'),
    ]
    
    STATUS_CHOICES = [
        ('active', 'Active'),
        ('expired', 'Expired'),
        ('revoked', 'Revoked'),
    ]
    
    resident = models.ForeignKey(Resident, on_delete=models.CASCADE, related_name='captain_clearances')
    clearance_type = models.CharField(max_length=50, choices=CLEARANCE_TYPE_CHOICES)
    reason = models.TextField()
    granted_by = models.CharField(max_length=100)
    granted_date = models.DateTimeField(auto_now_add=True)
    expires_at = models.DateTimeField(blank=True, null=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='active')
    notes = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'captain_clearances'

    def __str__(self):
        return f"Clearance {self.clearance_type} - {self.resident}"
