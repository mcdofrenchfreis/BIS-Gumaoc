from django.db import models
from apps.residents.models import Resident


class RFIDUser(models.Model):
    ID_TYPE_CHOICES = [
        ('National ID', 'National ID'),
        ('Drivers License', 'Drivers License'),
        ('Passport', 'Passport'),
        ('Other', 'Other'),
    ]
    
    STATUS_CHOICES = [
        ('active', 'Active'),
        ('inactive', 'Inactive'),
        ('suspended', 'Suspended'),
    ]
    
    rfid_tag = models.CharField(max_length=20, unique=True)
    full_name = models.CharField(max_length=255)
    email = models.EmailField(max_length=255, blank=True, null=True)
    phone = models.CharField(max_length=20, blank=True, null=True)
    address = models.TextField(blank=True, null=True)
    id_type = models.CharField(max_length=50, choices=ID_TYPE_CHOICES, default='National ID')
    id_number = models.CharField(max_length=50, blank=True, null=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='active')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'rfid_users'

    def __str__(self):
        return f"{self.full_name} - {self.rfid_tag}"


class RFIDRegistration(models.Model):
    CARD_TYPE_CHOICES = [
        ('resident', 'Resident'),
        ('employee', 'Employee'),
        ('visitor', 'Visitor'),
    ]
    
    STATUS_CHOICES = [
        ('pending', 'Pending'),
        ('approved', 'Approved'),
        ('rejected', 'Rejected'),
        ('active', 'Active'),
        ('blocked', 'Blocked'),
    ]
    
    rfid_number = models.CharField(max_length=50, unique=True)
    first_name = models.CharField(max_length=100)
    middle_name = models.CharField(max_length=100, blank=True, null=True)
    last_name = models.CharField(max_length=100)
    birth_date = models.DateField()
    contact_number = models.CharField(max_length=20)
    address = models.TextField()
    card_type = models.CharField(max_length=20, choices=CARD_TYPE_CHOICES, default='resident')
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    issued_date = models.DateField(blank=True, null=True)
    expires_date = models.DateField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'rfid_registrations'

    def __str__(self):
        return f"{self.first_name} {self.last_name} - {self.rfid_number}"


class RFIDAccessLog(models.Model):
    user = models.ForeignKey(Resident, on_delete=models.SET_NULL, null=True, blank=True, related_name='rfid_logs')
    rfid_tag = models.CharField(max_length=20, blank=True, null=True)
    full_name = models.CharField(max_length=255, blank=True, null=True)
    access_time = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'rfid_access_logs'
        ordering = ['-access_time']

    def __str__(self):
        return f"{self.full_name or self.rfid_tag} - {self.access_time}"


class ScannedRFIDCode(models.Model):
    STATUS_CHOICES = [
        ('available', 'Available'),
        ('assigned', 'Assigned'),
        ('disabled', 'Disabled'),
        ('archived', 'Archived'),
    ]
    
    rfid_code = models.CharField(max_length=50, unique=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='available')
    scanned_at = models.DateTimeField(auto_now_add=True)
    assigned_at = models.DateTimeField(blank=True, null=True)
    assigned_to_resident = models.ForeignKey(Resident, on_delete=models.SET_NULL, null=True, blank=True, related_name='scanned_codes')
    assigned_to_email = models.CharField(max_length=255, blank=True, null=True)
    scanned_by_admin = models.ForeignKey('users.AdminUser', on_delete=models.SET_NULL, null=True, blank=True)
    notes = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'scanned_rfid_codes'

    def __str__(self):
        return f"{self.rfid_code} - {self.status}"
