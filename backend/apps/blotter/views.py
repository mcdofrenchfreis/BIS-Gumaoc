from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from .models import BarangayBlotter, BlotterAttachment
from .serializers import BarangayBlotterSerializer, BlotterAttachmentSerializer


class BarangayBlotterViewSet(viewsets.ModelViewSet):
    queryset = BarangayBlotter.objects.all()
    serializer_class = BarangayBlotterSerializer
    filterset_fields = ['incident_type', 'classification', 'status']
    search_fields = ['blotter_number', 'complainant_name', 'respondent_name']
    ordering_fields = ['incident_date', 'created_at']

    @action(detail=True, methods=['post'])
    def update_status(self, request, pk=None):
        blotter = self.get_object()
        blotter.status = request.data.get('status', blotter.status)
        blotter.action_taken = request.data.get('action_taken', blotter.action_taken)
        blotter.settlement_details = request.data.get('settlement_details', blotter.settlement_details)
        blotter.save()
        return Response({'message': 'Blotter status updated successfully'})


class BlotterStatsAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request):
        try:
            total = BarangayBlotter.objects.count()
            filed = BarangayBlotter.objects.filter(status='filed').count()
            resolved = BarangayBlotter.objects.filter(status='resolved').count()
            critical = BarangayBlotter.objects.filter(classification='critical').count()

            return Response({
                'total': total,
                'filed': filed,
                'resolved': resolved,
                'critical': critical,
            })
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )


class BlotterAttachmentViewSet(viewsets.ModelViewSet):
    queryset = BlotterAttachment.objects.all()
    serializer_class = BlotterAttachmentSerializer
    filterset_fields = ['blotter']
