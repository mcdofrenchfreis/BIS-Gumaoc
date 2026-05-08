from django.db import models
from apps.residents.models import Resident


class QueueService(models.Model):
    name = models.CharField(max_length=200)
    description = models.TextField(blank=True, null=True)
    prefix = models.CharField(max_length=5)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'queue_services'

    def __str__(self):
        return self.name


class QueueCounter(models.Model):
    name = models.CharField(max_length=100)
    description = models.TextField(blank=True, null=True)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'queue_counters'

    def __str__(self):
        return self.name


class QueueWindow(models.Model):
    name = models.CharField(max_length=100)
    counter = models.ForeignKey(QueueCounter, on_delete=models.CASCADE, related_name='windows')
    services = models.ManyToManyField(QueueService, related_name='windows')
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'queue_windows'

    def __str__(self):
        return f"{self.name} - {self.counter}"


class QueueTicket(models.Model):
    STATUS_CHOICES = [
        ('waiting', 'Waiting'),
        ('serving', 'Serving'),
        ('completed', 'Completed'),
        ('skipped', 'Skipped'),
        ('cancelled', 'Cancelled'),
    ]
    
    service = models.ForeignKey(QueueService, on_delete=models.CASCADE, related_name='tickets')
    ticket_number = models.CharField(max_length=20)
    resident = models.ForeignKey(Resident, on_delete=models.SET_NULL, null=True, blank=True, related_name='queue_tickets')
    name = models.CharField(max_length=255)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='waiting')
    window = models.ForeignKey(QueueWindow, on_delete=models.SET_NULL, null=True, blank=True, related_name='tickets')
    called_at = models.DateTimeField(blank=True, null=True)
    completed_at = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'queue_tickets'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.ticket_number} - {self.name}"
