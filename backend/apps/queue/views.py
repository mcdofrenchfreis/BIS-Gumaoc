from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from .models import QueueService, QueueCounter, QueueWindow, QueueTicket
from .serializers import QueueServiceSerializer, QueueCounterSerializer, QueueWindowSerializer, QueueTicketSerializer
from apps.residents.models import Resident
from django.db.models import Count
import uuid
from datetime import datetime, timedelta


class QueueServiceViewSet(viewsets.ModelViewSet):
    queryset = QueueService.objects.filter(is_active=True)
    serializer_class = QueueServiceSerializer
    ordering_fields = ['service_name', 'estimated_time']


class QueueCounterViewSet(viewsets.ModelViewSet):
    queryset = QueueCounter.objects.filter(is_active=True)
    serializer_class = QueueCounterSerializer
    filterset_fields = ['service']


class QueueWindowViewSet(viewsets.ModelViewSet):
    queryset = QueueWindow.objects.filter(is_active=True)
    serializer_class = QueueWindowSerializer
    filterset_fields = ['service']


class QueueTicketViewSet(viewsets.ModelViewSet):
    queryset = QueueTicket.objects.all()
    serializer_class = QueueTicketSerializer
    filterset_fields = ['service', 'status', 'priority_level']
    search_fields = ['ticket_number', 'customer_name']
    ordering_fields = ['created_at', 'queue_position']

    @action(detail=True, methods=['post'])
    def call(self, request, pk=None):
        ticket = self.get_object()
        ticket.status = 'serving'
        ticket.called_at = datetime.now()
        ticket.save()
        return Response({'message': 'Ticket called successfully'})

    @action(detail=True, methods=['post'])
    def complete(self, request, pk=None):
        ticket = self.get_object()
        ticket.status = 'completed'
        ticket.completed_at = datetime.now()
        ticket.notes = request.data.get('notes', '')
        ticket.save()
        return Response({'message': 'Ticket completed successfully'})

    @action(detail=True, methods=['post'])
    def cancel(self, request, pk=None):
        ticket = self.get_object()
        ticket.status = 'cancelled'
        ticket.save()
        return Response({'message': 'Ticket cancelled successfully'})


class QueueStatusAPIView(APIView):
    def get(self, request):
        try:
            services = QueueService.objects.filter(is_active=True)
            status_data = []
            
            for service in services:
                waiting = QueueTicket.objects.filter(
                    service=service,
                    status='waiting',
                    created_at__date=datetime.now().date()
                ).count()
                
                serving = QueueTicket.objects.filter(
                    service=service,
                    status='serving',
                    created_at__date=datetime.now().date()
                ).count()
                
                status_data.append({
                    'service_name': service.service_name,
                    'waiting_count': waiting,
                    'serving_count': serving,
                    'avg_wait_time': service.estimated_time,
                })
            
            return Response(status_data)
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )


class GenerateTicketAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request):
        try:
            user = request.user
            if not hasattr(user, 'resident'):
                return Response(
                    {'error': 'User must be a resident'},
                    status=status.HTTP_400_BAD_REQUEST
                )
            
            service_id = request.data.get('service_id')
            full_name = request.data.get('full_name')
            contact_number = request.data.get('contact_number')
            purpose = request.data.get('purpose')
            priority_level = request.data.get('priority_level', 'normal')
            
            if not service_id or not full_name:
                return Response(
                    {'error': 'Service and name are required'},
                    status=status.HTTP_400_BAD_REQUEST
                )
            
            service = QueueService.objects.get(id=service_id)
            
            # Generate ticket number
            year = datetime.now().year
            prefix = service.service_code
            count = QueueTicket.objects.filter(
                service=service,
                created_at__year=year
            ).count() + 1
            ticket_number = f"{prefix}-{year}-{str(count).zfill(4)}"
            
            # Calculate queue position
            queue_position = QueueTicket.objects.filter(
                service=service,
                status='waiting',
                created_at__date=datetime.now().date()
            ).count() + 1
            
            # Calculate estimated time
            estimated_time = datetime.now() + timedelta(minutes=service.estimated_time * queue_position)
            
            # Create ticket
            ticket = QueueTicket.objects.create(
                ticket_number=ticket_number,
                service=service,
                customer_name=full_name,
                mobile_number=contact_number,
                user=user.resident,
                purpose=purpose,
                priority_level=priority_level,
                status='waiting',
                queue_position=queue_position,
                estimated_time=estimated_time,
            )
            
            return Response({
                'ticket_number': ticket.ticket_number,
                'queue_position': ticket.queue_position,
                'estimated_time': estimated_time.strftime('%I:%M %p'),
                'service_name': service.service_name,
                'status': ticket.status,
            })
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )
