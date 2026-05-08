from django.contrib import admin
from .models import QueueTicket, QueueService, QueueCounter, QueueWindow


@admin.register(QueueService)
class QueueServiceAdmin(admin.ModelAdmin):
    list_display = ['name', 'prefix', 'is_active', 'display_order']
    list_filter = ['is_active']


@admin.register(QueueTicket)
class QueueTicketAdmin(admin.ModelAdmin):
    list_display = ['ticket_number', 'name', 'service', 'status', 'created_at']
    list_filter = ['status', 'service', 'window']
    search_fields = ['ticket_number', 'name']
