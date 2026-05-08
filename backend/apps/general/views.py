from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from django.utils import timezone
from .models import Service, Update, UserReport, Notification
from .serializers import ServiceSerializer, UpdateSerializer, UserReportSerializer, NotificationSerializer


class ServiceViewSet(viewsets.ReadOnlyModelViewSet):
    queryset = Service.objects.all()
    serializer_class = ServiceSerializer
    ordering_fields = ['display_order']


class UpdateViewSet(viewsets.ReadOnlyModelViewSet):
    queryset = Update.objects.all()
    serializer_class = UpdateSerializer
    ordering_fields = ['display_order']


class UserReportViewSet(viewsets.ModelViewSet):
    queryset = UserReport.objects.all()
    serializer_class = UserReportSerializer
    filterset_fields = ['status', 'priority']
    ordering_fields = ['created_at']


class NotificationViewSet(viewsets.ModelViewSet):
    queryset = Notification.objects.all()
    serializer_class = NotificationSerializer
    filterset_fields = ['type', 'is_read']
    ordering_fields = ['created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        user = self.request.user
        if hasattr(user, 'resident'):
            queryset = queryset.filter(user=user.resident)
        
        filter_type = self.request.query_params.get('filter', 'all')
        if filter_type == 'unread':
            queryset = queryset.filter(is_read=False)
        elif filter_type == 'read':
            queryset = queryset.filter(is_read=True)
        
        return queryset

    @action(detail=True, methods=['post'])
    def mark_read(self, request, pk=None):
        notification = self.get_object()
        notification.is_read = True
        notification.read_at = timezone.now()
        notification.save()
        return Response({'message': 'Notification marked as read'})


class MarkAllReadAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request):
        try:
            user = request.user
            if hasattr(user, 'resident'):
                Notification.objects.filter(
                    user=user.resident,
                    is_read=False
                ).update(is_read=True, read_at=timezone.now())
                return Response({'message': 'All notifications marked as read'})
            return Response(
                {'error': 'User not found'},
                status=status.HTTP_404_NOT_FOUND
            )
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )
