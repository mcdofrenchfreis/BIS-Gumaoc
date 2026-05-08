from django.db import models
from apps.residents.models import Resident


class CertificateRequest(models.Model):
    CERTIFICATE_TYPES = [
        ('BRGY. CLEARANCE', 'Barangay Clearance'),
        ('BRGY. INDIGENCY', 'Barangay Indigency'),
        ('RESIDENCY', 'Certificate of Residency'),
        ('CEDULA', 'Community Tax Certificate'),
        ('TRICYCLE PERMIT', 'Tricycle Permit'),
    ]
    
    STATUS_CHOICES = [
        ('pending', 'Pending'),
        ('processing', 'Processing'),
        ('approved', 'Approved'),
        ('rejected', 'Rejected'),
        ('completed', 'Completed'),
    ]
    
    resident = models.ForeignKey(Resident, on_delete=models.CASCADE, related_name='certificate_requests', null=True, blank=True)
    certificate_type = models.CharField(max_length=50, choices=CERTIFICATE_TYPES)
    applicant_name = models.CharField(max_length=255)
    applicant_address = models.TextField()
    purpose = models.TextField()
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    certificate_number = models.CharField(max_length=50, blank=True, null=True)
    issued_date = models.DateField(blank=True, null=True)
    processed_by = models.CharField(max_length=100, blank=True, null=True)
    remarks = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'certificate_requests'

    def __str__(self):
        return f"{self.certificate_type} - {self.applicant_name}"


class CaptainClearance(models.Model):
    resident = models.ForeignKey(Resident, on_delete=models.CASCADE, related_name='captain_clearances')
    clearance_number = models.CharField(max_length=50, unique=True)
    purpose = models.TextField()
    issued_date = models.DateField()
    expires_date = models.DateField(blank=True, null=True)
    issued_by = models.CharField(max_length=100)
    remarks = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'captain_clearances'

    def __str__(self):
        return f"Clearance {self.clearance_number} - {self.resident}"
