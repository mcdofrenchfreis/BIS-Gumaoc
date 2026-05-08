from django.contrib import admin
from .models import User, AdminUser


@admin.register(User)
class UserAdmin(admin.ModelAdmin):
    list_display = ['username', 'email', 'role', 'profile_complete', 'created_at']
    list_filter = ['role', 'profile_complete']
    search_fields = ['username', 'email', 'first_name', 'last_name']


@admin.register(AdminUser)
class AdminUserAdmin(admin.ModelAdmin):
    list_display = ['username', 'full_name', 'email', 'role', 'created_at']
    list_filter = ['role']
    search_fields = ['username', 'email', 'full_name']
