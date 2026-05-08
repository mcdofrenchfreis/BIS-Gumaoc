from django.contrib import admin
from .models import BusinessApplication


@admin.register(BusinessApplication)
class BusinessApplicationAdmin(admin.ModelAdmin):
    list_display = ['business_name', 'business_type', 'owner_name', 'status', 'submitted_at']
    list_filter = ['status', 'business_type']
    search_fields = ['business_name', 'owner_name', 'reference_no']
