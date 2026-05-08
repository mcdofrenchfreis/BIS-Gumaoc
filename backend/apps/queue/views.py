from rest_framework import viewsets
from .models import QueueTicket, QueueService
from .serializers import QueueTicketSerializer, QueueServiceSerializer


class QueueTicketViewSet(viewsets.ModelViewSet):
    queryset = QueueTicket.objects.all()
    serializer_class = QueueTicketSerializer
    filterset_fields = ['status', 'service', 'window']
    search_fields = ['ticket_number', 'name']
    ordering_fields = ['created_at', 'ticket_number']


class QueueServiceViewSet(viewsets.ModelViewSet):
    queryset = QueueService.objects.all()
    serializer_class = QueueServiceSerializer
    filterset_fields = ['is_active']
    ordering_fields = ['display_order']
