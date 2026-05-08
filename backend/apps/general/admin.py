from django.contrib import admin
from .models import Service, Update, UserReport


@admin.register(Service)
class ServiceAdmin(admin.ModelAdmin):
    list_display = ['title', 'is_featured', 'display_order']
    list_filter = ['is_featured']
    search_fields = ['title']


@admin.register(Update)
class UpdateAdmin(admin.ModelAdmin):
    list_display = ['title', 'badge_type', 'date', 'is_priority', 'display_order']
    list_filter = ['badge_type', 'is_priority']
    search_fields = ['title']


@admin.register(UserReport)
class UserReportAdmin(admin.ModelAdmin):
    list_display = ['incident_type', 'user', 'priority', 'status', 'created_at']
    list_filter = ['priority', 'status']
    search_fields = ['incident_type', 'location']
