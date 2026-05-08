from django.contrib import admin
from .models import RFIDUser, RFIDRegistration, RFIDAccessLog, ScannedRFIDCode


@admin.register(RFIDUser)
class RFIDUserAdmin(admin.ModelAdmin):
    list_display = ['rfid_tag', 'full_name', 'status', 'created_at']
    list_filter = ['status', 'id_type']
    search_fields = ['rfid_tag', 'full_name']


@admin.register(RFIDRegistration)
class RFIDRegistrationAdmin(admin.ModelAdmin):
    list_display = ['rfid_number', 'first_name', 'last_name', 'card_type', 'status', 'created_at']
    list_filter = ['card_type', 'status']
    search_fields = ['rfid_number', 'first_name', 'last_name']


@admin.register(RFIDAccessLog)
class RFIDAccessLogAdmin(admin.ModelAdmin):
    list_display = ['rfid_tag', 'full_name', 'access_time']
    list_filter = ['access_time']
    search_fields = ['rfid_tag', 'full_name']


@admin.register(ScannedRFIDCode)
class ScannedRFIDCodeAdmin(admin.ModelAdmin):
    list_display = ['rfid_code', 'status', 'scanned_at', 'assigned_at']
    list_filter = ['status']
    search_fields = ['rfid_code']
