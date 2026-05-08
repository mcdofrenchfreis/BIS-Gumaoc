from django.db import models
from apps.residents.models import Resident


class QueueService(models.Model):
    service_name = models.CharField(max_length=100)
    service_code = models.CharField(max_length=10, unique=True)
    description = models.TextField(blank=True, null=True)
    estimated_time = models.IntegerField(default=15, help_text='Estimated time in minutes')
    max_daily_capacity = models.IntegerField(default=50)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'queue_services'

    def __str__(self):
        return self.service_name


class QueueCounter(models.Model):
    counter_number = models.CharField(max_length=10, unique=True)
    counter_name = models.CharField(max_length=50)
    service = models.ForeignKey(QueueService, on_delete=models.SET_NULL, null=True, blank=True, related_name='counters')
    operator_name = models.CharField(max_length=100, blank=True, null=True)
    is_active = models.BooleanField(default=True)
    current_ticket = models.ForeignKey('QueueTicket', on_delete=models.SET_NULL, null=True, blank=True, related_name='current_counter')
    last_called_at = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'queue_counters'

    def __str__(self):
        return f"{self.counter_number} - {self.counter_name}"


class QueueWindow(models.Model):
    window_number = models.CharField(max_length=10, unique=True)
    window_name = models.CharField(max_length=50)
    service = models.ForeignKey(QueueService, on_delete=models.SET_NULL, null=True, blank=True, related_name='windows')
    operator_name = models.CharField(max_length=100, blank=True, null=True)
    is_active = models.BooleanField(default=True)
    current_ticket = models.ForeignKey('QueueTicket', on_delete=models.SET_NULL, null=True, blank=True, related_name='current_window')
    last_called_at = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'queue_windows'

    def __str__(self):
        return f"{self.window_number} - {self.window_name}"


class QueueTicket(models.Model):
    PRIORITY_CHOICES = [
        ('normal', 'Normal'),
        ('priority', 'Priority'),
        ('urgent', 'Urgent'),
    ]
    
    STATUS_CHOICES = [
        ('waiting', 'Waiting'),
        ('serving', 'Serving'),
        ('completed', 'Completed'),
        ('cancelled', 'Cancelled'),
        ('no_show', 'No Show'),
    ]
    
    ticket_number = models.CharField(max_length=20, unique=True)
    service = models.ForeignKey(QueueService, on_delete=models.CASCADE, related_name='tickets')
    customer_name = models.CharField(max_length=100)
    mobile_number = models.CharField(max_length=20, blank=True, null=True)
    user = models.ForeignKey(Resident, on_delete=models.SET_NULL, null=True, blank=True, related_name='queue_tickets')
    purpose = models.TextField(blank=True, null=True)
    priority_level = models.CharField(max_length=20, choices=PRIORITY_CHOICES, default='normal')
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='waiting')
    queue_position = models.IntegerField(blank=True, null=True)
    estimated_time = models.DateTimeField(blank=True, null=True)
    called_at = models.DateTimeField(blank=True, null=True)
    served_at = models.DateTimeField(blank=True, null=True)
    completed_at = models.DateTimeField(blank=True, null=True)
    served_by = models.CharField(max_length=100, blank=True, null=True)
    notes = models.TextField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'queue_tickets'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.ticket_number} - {self.customer_name}"
