from django.contrib import admin
from .models import CertificateRequest, CaptainClearance


@admin.register(CertificateRequest)
class CertificateRequestAdmin(admin.ModelAdmin):
    list_display = ['certificate_type', 'full_name', 'status', 'created_at']
    list_filter = ['certificate_type', 'status']
    search_fields = ['full_name']


@admin.register(CaptainClearance)
class CaptainClearanceAdmin(admin.ModelAdmin):
    list_display = ['clearance_type', 'resident', 'granted_date', 'expires_at', 'status']
    list_filter = ['clearance_type', 'status']
    search_fields = ['resident__first_name', 'resident__last_name']
