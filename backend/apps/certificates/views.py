from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from .models import CertificateRequest, CaptainClearance
from .serializers import CertificateRequestSerializer, CaptainClearanceSerializer


class CertificateRequestViewSet(viewsets.ModelViewSet):
    queryset = CertificateRequest.objects.all()
    serializer_class = CertificateRequestSerializer
    filterset_fields = ['certificate_type', 'status']
    search_fields = ['applicant_name', 'certificate_number']
    ordering_fields = ['created_at', 'issued_date']

    @action(detail=True, methods=['post'])
    def print(self, request, pk=None):
        certificate = self.get_object()
        certificate.status = 'completed'
        certificate.save()
        return Response({'message': 'Certificate marked for printing'})


class CaptainClearanceViewSet(viewsets.ModelViewSet):
    queryset = CaptainClearance.objects.all()
    serializer_class = CaptainClearanceSerializer
    filterset_fields = ['issued_date', 'expires_date']
    search_fields = ['clearance_number']
    ordering_fields = ['issued_date']
