from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from django.utils import timezone
from .models import QueueService, QueueCounter, QueueWindow, QueueTicket
from .serializers import QueueServiceSerializer, QueueCounterSerializer, QueueWindowSerializer, QueueTicketSerializer
from apps.residents.models import Resident
from datetime import datetime, timedelta


class QueueServiceViewSet(viewsets.ModelViewSet):
    queryset = QueueService.objects.filter(is_active=True)
    serializer_class = QueueServiceSerializer
    ordering_fields = ['service_name', 'estimated_time']


class QueueCounterViewSet(viewsets.ModelViewSet):
    queryset = QueueCounter.objects.filter(is_active=True)
    serializer_class = QueueCounterSerializer
    filterset_fields = ['service']

    @action(detail=True, methods=['post'])
    def call_next(self, request, pk=None):
        counter = self.get_object()
        service = counter.service

        priority_order = {'urgent': 1, 'priority': 2, 'normal': 3}
        waiting_tickets = QueueTicket.objects.filter(
            service=service,
            status='waiting',
            created_at__date=timezone.now().date(),
        )
        waiting_tickets = sorted(
            waiting_tickets,
            key=lambda x: (priority_order.get(x.priority_level, 3), x.created_at),
        )

        if not waiting_tickets:
            return Response({'message': 'No waiting tickets'}, status=status.HTTP_404_NOT_FOUND)

        ticket = waiting_tickets[0]
        ticket.status = 'serving'
        ticket.called_at = timezone.now()
        ticket.served_at = timezone.now()
        ticket.served_by = getattr(request.user, 'username', None) or 'admin'
        ticket.save()

        counter.current_ticket = ticket
        counter.last_called_at = timezone.now()
        counter.save()

        return Response({
            'ticket': QueueTicketSerializer(ticket).data,
            'message': f'Calling ticket {ticket.ticket_number}',
        })

    @action(detail=True, methods=['post'])
    def complete_ticket(self, request, pk=None):
        counter = self.get_object()

        if not counter.current_ticket:
            return Response({'error': 'No current ticket'}, status=status.HTTP_400_BAD_REQUEST)

        ticket = counter.current_ticket
        ticket.status = 'completed'
        ticket.completed_at = timezone.now()
        ticket.notes = request.data.get('notes', '')
        ticket.save()

        counter.current_ticket = None
        counter.save()

        return Response({'message': 'Ticket completed successfully'})


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

    @action(detail=False, methods=['get'])
    def queue_status(self, request):
        today = timezone.now().date()
        services = QueueService.objects.filter(is_active=True)
        status_data = []

        for service in services:
            tickets = QueueTicket.objects.filter(service=service, created_at__date=today)
            status_data.append({
                'service_id': service.id,
                'service_name': service.service_name,
                'service_code': service.service_code,
                'estimated_time': service.estimated_time,
                'waiting_count': tickets.filter(status='waiting').count(),
                'serving_count': tickets.filter(status='serving').count(),
                'completed_count': tickets.filter(status='completed').count(),
            })

        return Response({'services': status_data})

    @action(detail=False, methods=['get'])
    def currently_serving(self, request):
        today = timezone.now().date()
        serving_tickets = QueueTicket.objects.filter(status='serving', created_at__date=today).select_related('service')
        data = []

        for ticket in serving_tickets:
            counter = QueueCounter.objects.filter(current_ticket=ticket).first()
            data.append({
                'ticket_number': ticket.ticket_number,
                'customer_name': ticket.customer_name,
                'service_name': ticket.service.service_name,
                'service_code': ticket.service.service_code,
                'counter_name': counter.counter_name if counter else None,
                'served_at': ticket.served_at,
            })

        return Response({'serving': data})

    @action(detail=False, methods=['get'])
    def next_in_queue(self, request):
        limit = int(request.query_params.get('limit', 5))
        today = timezone.now().date()
        priority_order = {'urgent': 1, 'priority': 2, 'normal': 3}

        waiting_tickets = QueueTicket.objects.filter(status='waiting', created_at__date=today).select_related('service')
        waiting_tickets = sorted(waiting_tickets, key=lambda x: (priority_order.get(x.priority_level, 3), x.created_at))

        data = []
        for ticket in waiting_tickets[:limit]:
            data.append({
                'ticket_number': ticket.ticket_number,
                'customer_name': ticket.customer_name,
                'service_name': ticket.service.service_name,
                'service_code': ticket.service.service_code,
                'queue_position': ticket.queue_position,
                'priority_level': ticket.priority_level,
                'estimated_time': ticket.estimated_time,
            })

        return Response({'next': data})

    @action(detail=True, methods=['post'])
    def call(self, request, pk=None):
        ticket = self.get_object()
        ticket.status = 'serving'
        ticket.called_at = timezone.now()
        ticket.save()
        return Response({'message': 'Ticket called successfully'})

    @action(detail=True, methods=['post'])
    def complete(self, request, pk=None):
        ticket = self.get_object()
        ticket.status = 'completed'
        ticket.completed_at = timezone.now()
        ticket.notes = request.data.get('notes', '')
        ticket.save()
        return Response({'message': 'Ticket completed successfully'})

    @action(detail=True, methods=['post'])
    def cancel(self, request, pk=None):
        ticket = self.get_object()
        ticket.status = 'cancelled'
        ticket.save()
        return Response({'message': 'Ticket cancelled successfully'})


class QueueManagementAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request):
        today = timezone.now().date()

        total_tickets = QueueTicket.objects.filter(created_at__date=today).count()
        waiting_tickets = QueueTicket.objects.filter(status='waiting', created_at__date=today).count()
        serving_tickets = QueueTicket.objects.filter(status='serving', created_at__date=today).count()
        completed_tickets = QueueTicket.objects.filter(status='completed', created_at__date=today).count()

        services = QueueService.objects.filter(is_active=True)
        service_stats = []
        for service in services:
            service_tickets = QueueTicket.objects.filter(service=service, created_at__date=today)
            service_stats.append({
                'service_name': service.service_name,
                'total': service_tickets.count(),
                'waiting': service_tickets.filter(status='waiting').count(),
                'serving': service_tickets.filter(status='serving').count(),
                'completed': service_tickets.filter(status='completed').count(),
            })

        return Response({
            'total_tickets': total_tickets,
            'waiting_tickets': waiting_tickets,
            'serving_tickets': serving_tickets,
            'completed_tickets': completed_tickets,
            'services': service_stats,
        })


class GenerateTicketAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request):
        try:
            user = request.user
            if not hasattr(user, 'resident'):
                return Response({'error': 'User must be a resident'}, status=status.HTTP_400_BAD_REQUEST)

            service_id = request.data.get('service_id')
            full_name = request.data.get('full_name')
            contact_number = request.data.get('contact_number')
            purpose = request.data.get('purpose')
            priority_level = request.data.get('priority_level', 'normal')

            if not service_id or not full_name:
                return Response({'error': 'Service and name are required'}, status=status.HTTP_400_BAD_REQUEST)

            service = QueueService.objects.get(id=service_id)

            year = timezone.now().year
            prefix = service.service_code
            count = QueueTicket.objects.filter(service=service, created_at__year=year).count() + 1
            ticket_number = f"{prefix}-{year}-{str(count).zfill(4)}"

            queue_position = QueueTicket.objects.filter(
                service=service,
                status='waiting',
                created_at__date=timezone.now().date(),
            ).count() + 1

            estimated_time = timezone.now() + timedelta(minutes=service.estimated_time * queue_position)

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
        except QueueService.DoesNotExist:
            return Response({'error': 'Invalid service'}, status=status.HTTP_400_BAD_REQUEST)
        except Exception as e:
            return Response({'error': str(e)}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)