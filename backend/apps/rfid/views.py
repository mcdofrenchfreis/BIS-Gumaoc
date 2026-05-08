from rest_framework import viewsets, status
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework.permissions import AllowAny
from django.utils import timezone
from .models import RFIDUser, RFIDRegistration, RFIDAccessLog, ScannedRFIDCode
from .serializers import RFIDRegistrationSerializer, RFIDAccessLogSerializer, ScannedRFIDCodeSerializer


class RFIDLoginView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        rfid_tag = request.data.get('rfid_tag')
        
        try:
            rfid_user = RFIDUser.objects.get(rfid_tag=rfid_tag, status='active')
            RFIDAccessLog.objects.create(
                rfid_tag=rfid_tag,
                full_name=rfid_user.full_name,
                access_time=timezone.now()
            )
            return Response({
                'success': True,
                'full_name': rfid_user.full_name,
                'rfid_tag': rfid_user.rfid_tag
            })
        except RFIDUser.DoesNotExist:
            return Response(
                {'success': False, 'error': 'RFID tag not found or inactive'},
                status=status.HTTP_404_NOT_FOUND
            )


class RFIDRegistrationViewSet(viewsets.ModelViewSet):
    queryset = RFIDRegistration.objects.all()
    serializer_class = RFIDRegistrationSerializer
    filterset_fields = ['card_type', 'status']
    search_fields = ['rfid_number', 'first_name', 'last_name']
    ordering_fields = ['created_at', 'issued_date']


class RFIDAccessLogViewSet(viewsets.ReadOnlyModelViewSet):
    queryset = RFIDAccessLog.objects.all()
    serializer_class = RFIDAccessLogSerializer
    filterset_fields = ['rfid_tag']
    ordering_fields = ['access_time']
