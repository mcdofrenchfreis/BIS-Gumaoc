from django.db import models
from apps.residents.models import Resident


class BusinessApplication(models.Model):
    STATUS_CHOICES = [
        ('pending', 'Pending'),
        ('reviewing', 'Reviewing'),
        ('approved', 'Approved'),
        ('rejected', 'Rejected'),
    ]
    
    user = models.ForeignKey(Resident, on_delete=models.SET_NULL, null=True, blank=True)
    reference_no = models.CharField(max_length=50, blank=True, null=True)
    application_date = models.DateField(blank=True, null=True)
    first_name = models.CharField(max_length=100, blank=True, null=True)
    middle_name = models.CharField(max_length=100, blank=True, null=True)
    last_name = models.CharField(max_length=100, blank=True, null=True)
    business_location = models.TextField(blank=True, null=True)
    or_number = models.CharField(max_length=100, blank=True, null=True)
    ctc_number = models.CharField(max_length=100, blank=True, null=True)
    business_name = models.CharField(max_length=255)
    business_type = models.CharField(max_length=100)
    business_address = models.CharField(max_length=500)
    business_description = models.TextField(blank=True, null=True)
    capital_amount = models.DecimalField(max_digits=15, decimal_places=2, blank=True, null=True)
    owner_name = models.CharField(max_length=255)
    owner_address = models.TextField(blank=True, null=True)
    owner_contact = models.CharField(max_length=20, blank=True, null=True)
    contact_number = models.CharField(max_length=20)
    years_operation = models.IntegerField()
    investment_capital = models.DecimalField(max_digits=15, decimal_places=2)
    proof_image = models.CharField(max_length=255, blank=True, null=True, help_text='Optional proof image filename')
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    submitted_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'business_applications'

    def __str__(self):
        return f"{self.business_name} - {self.status}"
