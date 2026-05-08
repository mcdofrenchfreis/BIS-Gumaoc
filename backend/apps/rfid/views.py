from rest_framework import viewsets, status
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework.permissions import AllowAny, IsAuthenticated
from django.utils import timezone
from .models import RFIDUser, RFIDRegistration, RFIDAccessLog, ScannedRFIDCode
from .serializers import RFIDRegistrationSerializer, RFIDAccessLogSerializer, ScannedRFIDCodeSerializer, RFIDUserSerializer


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


class RFIDRegistrationAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request):
        try:
            rfid_number = request.data.get('rfid_number')
            card_type = request.data.get('card_type', 'resident')
            first_name = request.data.get('first_name')
            middle_name = request.data.get('middle_name')
            last_name = request.data.get('last_name')
            birth_date = request.data.get('birth_date')
            contact_number = request.data.get('contact_number')
            address = request.data.get('address')

            # Check if RFID number already exists
            if RFIDRegistration.objects.filter(rfid_number=rfid_number).exists():
                return Response(
                    {'error': 'RFID number already registered'},
                    status=status.HTTP_400_BAD_REQUEST
                )

            # Create registration
            registration = RFIDRegistration.objects.create(
                rfid_number=rfid_number,
                card_type=card_type,
                first_name=first_name,
                middle_name=middle_name,
                last_name=last_name,
                birth_date=birth_date,
                contact_number=contact_number,
                address=address,
                status='pending'
            )

            return Response({
                'message': 'RFID registration submitted successfully',
                'registration_id': registration.id,
                'status': registration.status
            }, status=status.HTTP_201_CREATED)
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )


class RFIDAccessLogViewSet(viewsets.ReadOnlyModelViewSet):
    queryset = RFIDAccessLog.objects.all()
    serializer_class = RFIDAccessLogSerializer
    filterset_fields = ['rfid_tag']
    ordering_fields = ['access_time']


class RFIDUserViewSet(viewsets.ModelViewSet):
    queryset = RFIDUser.objects.all()
    serializer_class = RFIDUserSerializer
    filterset_fields = ['status']
    search_fields = ['full_name', 'rfid_tag', 'email']
    ordering_fields = ['created_at', 'updated_at']


class ScannedRFIDCodeViewSet(viewsets.ModelViewSet):
    queryset = ScannedRFIDCode.objects.all()
    serializer_class = ScannedRFIDCodeSerializer
    filterset_fields = ['status']
    search_fields = ['rfid_code', 'assigned_to_email']
    ordering_fields = ['scanned_at', 'created_at']
