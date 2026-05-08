from django.contrib import admin
from .models import CertificateRequest, CaptainClearance


@admin.register(CertificateRequest)
class CertificateRequestAdmin(admin.ModelAdmin):
    list_display = ['certificate_type', 'applicant_name', 'status', 'created_at']
    list_filter = ['certificate_type', 'status']
    search_fields = ['applicant_name', 'certificate_number']


@admin.register(CaptainClearance)
class CaptainClearanceAdmin(admin.ModelAdmin):
    list_display = ['clearance_number', 'resident', 'issued_date', 'expires_date']
    list_filter = ['issued_date', 'expires_date']
    search_fields = ['clearance_number']
