from django.contrib.auth.models import AbstractUser
from django.db import models


class User(AbstractUser):
    ROLE_CHOICES = [
        ('resident', 'Resident'),
        ('admin', 'Admin'),
        ('super_admin', 'Super Admin'),
    ]
    
    role = models.CharField(max_length=20, choices=ROLE_CHOICES, default='resident')
    phone = models.CharField(max_length=20, blank=True, null=True)
    profile_complete = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'users'

    def __str__(self):
        return f"{self.first_name} {self.last_name}"


class AdminUser(models.Model):
    ROLE_CHOICES = [
        ('super_admin', 'Super Admin'),
        ('admin', 'Admin'),
    ]
    
    username = models.CharField(max_length=50, unique=True)
    password = models.CharField(max_length=255)
    full_name = models.CharField(max_length=100)
    email = models.EmailField(max_length=100)
    role = models.CharField(max_length=20, choices=ROLE_CHOICES, default='admin')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'admin_users'

    def __str__(self):
        return self.username


class AccessLog(models.Model):
    resident = models.ForeignKey('residents.Resident', on_delete=models.SET_NULL, null=True, blank=True, related_name='access_logs')
    form_type = models.CharField(max_length=100)
    access_granted = models.BooleanField()
    reason = models.CharField(max_length=255)
    attempted_at = models.DateTimeField(auto_now_add=True)
    ip_address = models.CharField(max_length=45, blank=True, null=True)
    user_agent = models.TextField(blank=True, null=True)

    class Meta:
        db_table = 'access_logs'
        ordering = ['-attempted_at']

    def __str__(self):
        return f"{self.form_type} - {self.access_granted}"


class AdminLog(models.Model):
    ACTION_TYPES = [
        ('admin_login', 'Admin Login'),
        ('admin_logout', 'Admin Logout'),
        ('page_view', 'Page View'),
        ('form_view', 'Form View'),
        ('status_update', 'Status Update'),
        ('print_action', 'Print Action'),
    ]
    
    admin_id = models.CharField(max_length=100, default='system')
    action_type = models.CharField(max_length=50, choices=ACTION_TYPES)
    target_type = models.CharField(max_length=50)
    target_id = models.IntegerField(blank=True, null=True)
    description = models.TextField()
    details = models.JSONField(blank=True, null=True)
    ip_address = models.CharField(max_length=45, blank=True, null=True)
    user_agent = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'admin_logs'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.action_type} - {self.target_type}"
