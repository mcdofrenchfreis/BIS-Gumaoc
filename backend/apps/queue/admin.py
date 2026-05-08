from django.contrib import admin
from .models import QueueTicket, QueueService, QueueCounter, QueueWindow


@admin.register(QueueService)
class QueueServiceAdmin(admin.ModelAdmin):
    list_display = ['service_name', 'service_code', 'is_active', 'created_at']
    list_filter = ['is_active']


@admin.register(QueueTicket)
class QueueTicketAdmin(admin.ModelAdmin):
    list_display = ['ticket_number', 'customer_name', 'service', 'status', 'created_at']
    list_filter = ['status', 'service']
    search_fields = ['ticket_number', 'customer_name']
