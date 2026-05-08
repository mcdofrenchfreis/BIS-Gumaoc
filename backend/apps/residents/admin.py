from django.contrib import admin
from .models import Resident, ResidentRegistration, FamilyMember, FamilyDisability, FamilyOrganization, ResidentStatus


@admin.register(Resident)
class ResidentAdmin(admin.ModelAdmin):
    list_display = ['first_name', 'last_name', 'email', 'status', 'profile_complete', 'created_at']
    list_filter = ['status', 'gender', 'civil_status', 'profile_complete']
    search_fields = ['first_name', 'last_name', 'email', 'rfid_code']


@admin.register(ResidentRegistration)
class ResidentRegistrationAdmin(admin.ModelAdmin):
    list_display = ['first_name', 'last_name', 'status', 'submitted_at']
    list_filter = ['status', 'gender', 'civil_status']
    search_fields = ['first_name', 'last_name', 'email']
