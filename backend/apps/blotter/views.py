from rest_framework import viewsets
from .models import BarangayBlotter, BlotterAttachment
from .serializers import BarangayBlotterSerializer, BlotterAttachmentSerializer


class BarangayBlotterViewSet(viewsets.ModelViewSet):
    queryset = BarangayBlotter.objects.all()
    serializer_class = BarangayBlotterSerializer
    filterset_fields = ['incident_type', 'classification', 'status']
    search_fields = ['blotter_number', 'complainant_name', 'respondent_name']
    ordering_fields = ['incident_date', 'reported_date', 'created_at']


class BlotterAttachmentViewSet(viewsets.ModelViewSet):
    queryset = BlotterAttachment.objects.all()
    serializer_class = BlotterAttachmentSerializer
    filterset_fields = ['blotter']
