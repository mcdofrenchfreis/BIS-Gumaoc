from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from django.utils import timezone
from .models import Service, Update, UserReport
from .serializers import ServiceSerializer, UpdateSerializer, UserReportSerializer
from .backup_util import BackupUtil


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
    search_fields = ['incident_type', 'location']
    ordering_fields = ['created_at']


class BackupAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request):
        backups = BackupUtil.list_backups()
        return Response({
            'backups': backups,
            'count': len(backups)
        })

    def post(self, request):
        result = BackupUtil.export_database()
        if result['success']:
            return Response(result, status=status.HTTP_201_CREATED)
        return Response(result, status=status.HTTP_500_INTERNAL_SERVER_ERROR)


class BackupDetailAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def delete(self, request, filename):
        success = BackupUtil.delete_backup(filename)
        if success:
            return Response({'message': 'Backup deleted successfully'})
        return Response({'error': 'Failed to delete backup'}, status=status.HTTP_400_BAD_REQUEST)


class RestoreBackupAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request, filename):
        result = BackupUtil.import_database(filename)
        if result['success']:
            return Response(result)
        return Response(result, status=status.HTTP_500_INTERNAL_SERVER_ERROR)


