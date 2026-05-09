from django.db import models
from apps.residents.models import Resident


class Service(models.Model):
    title = models.CharField(max_length=255)
    description = models.TextField()
    icon = models.CharField(max_length=50)
    button_text = models.CharField(max_length=100)
    button_link = models.CharField(max_length=255)
    is_featured = models.BooleanField(default=False)
    features = models.TextField(blank=True, null=True)
    display_order = models.IntegerField(default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'services'
        ordering = ['display_order']

    def __str__(self):
        return self.title


class Update(models.Model):
    BADGE_TYPE_CHOICES = [
        ('important', 'Important'),
        ('new', 'New'),
        ('community', 'Community'),
        ('info', 'Info'),
    ]
    
    title = models.CharField(max_length=255)
    description = models.TextField()
    badge_text = models.CharField(max_length=50)
    badge_type = models.CharField(max_length=20, choices=BADGE_TYPE_CHOICES, default='info')
    date = models.CharField(max_length=50)
    status = models.CharField(max_length=50)
    is_priority = models.BooleanField(default=False)
    display_order = models.IntegerField(default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'updates'
        ordering = ['display_order']

    def __str__(self):
        return self.title


class UserReport(models.Model):
    PRIORITY_CHOICES = [
        ('low', 'Low'),
        ('medium', 'Medium'),
        ('high', 'High'),
    ]
    
    STATUS_CHOICES = [
        ('pending', 'Pending'),
        ('processing', 'Processing'),
        ('completed', 'Completed'),
        ('rejected', 'Rejected'),
    ]
    
    user = models.ForeignKey('residents.Resident', on_delete=models.CASCADE, related_name='reports')
    incident_type = models.CharField(max_length=100)
    location = models.CharField(max_length=255)
    description = models.TextField()
    priority = models.CharField(max_length=20, choices=PRIORITY_CHOICES, default='medium')
    contact_number = models.CharField(max_length=20)
    proof_image = models.CharField(max_length=255, blank=True, null=True, help_text='Optional proof image filename')
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    admin_notes = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'user_reports'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.incident_type} - {self.user}"


