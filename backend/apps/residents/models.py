from django.db import models
from django.contrib.auth import get_user_model

User = get_user_model()


class Resident(models.Model):
    GENDER_CHOICES = [
        ('Male', 'Male'),
        ('Female', 'Female'),
        ('Other', 'Other'),
    ]
    
    CIVIL_STATUS_CHOICES = [
        ('Single', 'Single'),
        ('Married', 'Married'),
        ('Widowed', 'Widowed'),
        ('Separated', 'Separated'),
        ('Divorced', 'Divorced'),
    ]
    
    STATUS_CHOICES = [
        ('active', 'Active'),
        ('inactive', 'Inactive'),
        ('pending', 'Pending'),
    ]
    
    first_name = models.CharField(max_length=100)
    middle_name = models.CharField(max_length=100, blank=True, null=True)
    last_name = models.CharField(max_length=100)
    email = models.EmailField(max_length=255)
    phone = models.CharField(max_length=20)
    password = models.CharField(max_length=255, blank=True, null=True)
    address = models.TextField()
    house_number = models.CharField(max_length=20, blank=True, null=True)
    barangay = models.CharField(max_length=100, default='Gumaoc East')
    sitio = models.CharField(max_length=100, default='BLOCK')
    interviewer = models.CharField(max_length=255, blank=True, null=True)
    interviewer_title = models.CharField(max_length=255, blank=True, null=True)
    birthdate = models.DateField()
    birth_place = models.CharField(max_length=255, blank=True, null=True)
    gender = models.CharField(max_length=10, choices=GENDER_CHOICES)
    civil_status = models.CharField(max_length=20, choices=CIVIL_STATUS_CHOICES)
    rfid_code = models.CharField(max_length=50, blank=True, null=True)
    rfid = models.CharField(max_length=50, blank=True, null=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='active')
    reset_otp = models.CharField(max_length=6, blank=True, null=True)
    otp_expiry = models.DateTimeField(blank=True, null=True)
    profile_complete = models.BooleanField(default=True)
    created_by = models.ForeignKey('self', on_delete=models.SET_NULL, blank=True, null=True)
    relationship_to_head = models.CharField(max_length=100, blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'residents'

    def __str__(self):
        return f"{self.first_name} {self.last_name}"


class ResidentRegistration(models.Model):
    CIVIL_STATUS_CHOICES = [
        ('Single', 'Single'),
        ('Married', 'Married'),
        ('Widowed', 'Widowed'),
        ('Separated', 'Separated'),
        ('Divorced', 'Divorced'),
    ]
    
    STATUS_CHOICES = [
        ('pending', 'Pending'),
        ('approved', 'Approved'),
        ('rejected', 'Rejected'),
    ]
    
    first_name = models.CharField(max_length=100)
    middle_name = models.CharField(max_length=100, blank=True, null=True)
    last_name = models.CharField(max_length=100)
    birth_date = models.DateField()
    birth_place = models.CharField(max_length=255, blank=True, null=True)
    age = models.IntegerField()
    civil_status = models.CharField(max_length=50, choices=CIVIL_STATUS_CHOICES)
    gender = models.CharField(max_length=20)
    contact_number = models.CharField(max_length=20, blank=True, null=True)
    email = models.EmailField(max_length=255, blank=True, null=True)
    house_number = models.CharField(max_length=20, blank=True, null=True)
    address = models.TextField(blank=True, null=True)
    pangkabuhayan = models.CharField(max_length=100, blank=True, null=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    land_ownership = models.CharField(max_length=100, blank=True, null=True)
    land_ownership_other = models.CharField(max_length=255, blank=True, null=True)
    house_ownership = models.CharField(max_length=100, blank=True, null=True)
    house_ownership_other = models.CharField(max_length=255, blank=True, null=True)
    farmland = models.CharField(max_length=100, blank=True, null=True)
    cooking_energy = models.CharField(max_length=100, blank=True, null=True)
    cooking_energy_other = models.CharField(max_length=255, blank=True, null=True)
    toilet_type = models.CharField(max_length=100, blank=True, null=True)
    toilet_type_other = models.CharField(max_length=255, blank=True, null=True)
    electricity_source = models.CharField(max_length=100, blank=True, null=True)
    electricity_source_other = models.CharField(max_length=255, blank=True, null=True)
    water_source = models.CharField(max_length=100, blank=True, null=True)
    water_source_other = models.CharField(max_length=255, blank=True, null=True)
    waste_disposal = models.CharField(max_length=100, blank=True, null=True)
    waste_disposal_other = models.CharField(max_length=255, blank=True, null=True)
    appliances = models.TextField(blank=True, null=True)
    transportation = models.TextField(blank=True, null=True)
    transportation_other = models.CharField(max_length=255, blank=True, null=True)
    business = models.TextField(blank=True, null=True)
    business_other = models.CharField(max_length=255, blank=True, null=True)
    contraceptive = models.TextField(blank=True, null=True)
    interviewer = models.CharField(max_length=255, blank=True, null=True)
    interviewer_title = models.CharField(max_length=255, blank=True, null=True)
    resident_disability = models.CharField(max_length=255, blank=True, null=True)
    resident_organization = models.CharField(max_length=255, blank=True, null=True)
    submitted_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'resident_registrations'

    def __str__(self):
        return f"{self.first_name} {self.last_name}"


class FamilyMember(models.Model):
    resident = models.ForeignKey(Resident, on_delete=models.CASCADE, related_name='family_members')
    first_name = models.CharField(max_length=100)
    middle_name = models.CharField(max_length=100, blank=True, null=True)
    last_name = models.CharField(max_length=100)
    birth_date = models.DateField()
    gender = models.CharField(max_length=20)
    civil_status = models.CharField(max_length=50)
    relationship = models.CharField(max_length=100)
    occupation = models.CharField(max_length=100, blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'family_members'

    def __str__(self):
        return f"{self.first_name} {self.last_name}"


class FamilyDisability(models.Model):
    family_member = models.ForeignKey(FamilyMember, on_delete=models.CASCADE, related_name='disabilities')
    disability_type = models.CharField(max_length=100)
    description = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'family_disabilities'

    def __str__(self):
        return f"{self.family_member} - {self.disability_type}"


class FamilyOrganization(models.Model):
    family_member = models.ForeignKey(FamilyMember, on_delete=models.CASCADE, related_name='organizations')
    organization_name = models.CharField(max_length=200)
    position = models.CharField(max_length=100, blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'family_organizations'

    def __str__(self):
        return f"{self.family_member} - {self.organization_name}"


class ResidentStatus(models.Model):
    RECORD_STATUS_CHOICES = [
        ('good', 'Good'),
        ('minor_issues', 'Minor Issues'),
        ('major_issues', 'Major Issues'),
        ('critical', 'Critical'),
    ]
    
    resident = models.ForeignKey(Resident, on_delete=models.CASCADE, related_name='status_records')
    resident_name = models.CharField(max_length=255)
    record_status = models.CharField(max_length=20, choices=RECORD_STATUS_CHOICES, default='good')
    total_complaints = models.IntegerField(default=0)
    total_incidents = models.IntegerField(default=0)
    pending_cases = models.IntegerField(default=0)
    resolved_cases = models.IntegerField(default=0)
    requires_captain_clearance = models.BooleanField(default=False)
    captain_clearance_granted = models.BooleanField(default=False)
    captain_clearance_date = models.DateTimeField(blank=True, null=True)
    captain_clearance_reason = models.TextField(blank=True, null=True)
    captain_clearance_expires = models.DateTimeField(blank=True, null=True)
    last_incident_date = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    last_updated = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'resident_status'

    def __str__(self):
        return f"{self.resident_name} - {self.record_status}"
