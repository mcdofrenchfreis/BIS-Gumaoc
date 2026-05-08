from django.db import models
from apps.residents.models import Resident


class BarangayBlotter(models.Model):
    INCIDENT_TYPE_CHOICES = [
        ('complaint', 'Complaint'),
        ('incident', 'Incident'),
        ('dispute', 'Dispute'),
        ('violation', 'Violation'),
        ('other', 'Other'),
    ]
    
    CLASSIFICATION_CHOICES = [
        ('minor', 'Minor'),
        ('major', 'Major'),
        ('critical', 'Critical'),
    ]
    
    STATUS_CHOICES = [
        ('filed', 'Filed'),
        ('under_investigation', 'Under Investigation'),
        ('mediation', 'Mediation'),
        ('resolved', 'Resolved'),
        ('dismissed', 'Dismissed'),
        ('referred_to_court', 'Referred to Court'),
    ]
    
    blotter_number = models.CharField(max_length=50, unique=True)
    incident_type = models.CharField(max_length=20, choices=INCIDENT_TYPE_CHOICES)
    complainant_id = models.IntegerField(blank=True, null=True)
    complainant_name = models.CharField(max_length=255)
    complainant_address = models.CharField(max_length=500)
    complainant_contact = models.CharField(max_length=20, blank=True, null=True)
    respondent_id = models.IntegerField(blank=True, null=True)
    respondent_name = models.CharField(max_length=255)
    respondent_address = models.CharField(max_length=500)
    respondent_contact = models.CharField(max_length=20, blank=True, null=True)
    incident_date = models.DateTimeField()
    reported_date = models.DateTimeField(auto_now_add=True)
    location = models.CharField(max_length=500)
    description = models.TextField()
    classification = models.CharField(max_length=20, choices=CLASSIFICATION_CHOICES, default='minor')
    status = models.CharField(max_length=30, choices=STATUS_CHOICES, default='filed')
    investigating_officer = models.CharField(max_length=255, blank=True, null=True)
    settlement_details = models.TextField(blank=True, null=True)
    action_taken = models.TextField(blank=True, null=True)
    case_disposition = models.TextField(blank=True, null=True)
    created_by = models.CharField(max_length=100)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'barangay_blotter'

    def __str__(self):
        return f"Blotter {self.blotter_number} - {self.incident_type}"


class BlotterAttachment(models.Model):
    blotter = models.ForeignKey(BarangayBlotter, on_delete=models.CASCADE, related_name='attachments')
    file_name = models.CharField(max_length=255)
    file_path = models.CharField(max_length=500)
    file_type = models.CharField(max_length=50)
    file_size = models.IntegerField()
    uploaded_by = models.CharField(max_length=100)
    uploaded_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'blotter_attachments'

    def __str__(self):
        return f"{self.blotter} - {self.file_name}"
