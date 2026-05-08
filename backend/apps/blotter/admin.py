from django.contrib import admin
from .models import BarangayBlotter, BlotterAttachment


@admin.register(BarangayBlotter)
class BarangayBlotterAdmin(admin.ModelAdmin):
    list_display = ['blotter_number', 'incident_type', 'classification', 'status', 'incident_date']
    list_filter = ['incident_type', 'classification', 'status']
    search_fields = ['blotter_number', 'complainant_name', 'respondent_name']


@admin.register(BlotterAttachment)
class BlotterAttachmentAdmin(admin.ModelAdmin):
    list_display = ['blotter', 'file_name', 'file_type', 'uploaded_at']
    list_filter = ['file_type']
