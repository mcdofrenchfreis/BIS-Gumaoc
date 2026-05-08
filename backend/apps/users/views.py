from rest_framework import status
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework_simplejwt.tokens import RefreshToken
from django.contrib.auth import authenticate
from django.contrib.auth import get_user_model
from django.contrib.auth.hashers import check_password
from .models import AdminUser, AdminLog
from .serializers import RegisterSerializer, LoginSerializer, UserSerializer, AdminLoginSerializer

User = get_user_model()


class LoginView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = LoginSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        
        user = authenticate(
            username=serializer.validated_data['username'],
            password=serializer.validated_data['password']
        )
        
        if user:
            refresh = RefreshToken.for_user(user)
            return Response({
                'access': str(refresh.access_token),
                'refresh': str(refresh),
                'user': UserSerializer(user).data
            })
        
        return Response(
            {'error': 'Invalid credentials'},
            status=status.HTTP_401_UNAUTHORIZED
        )


class RegisterView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = RegisterSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        user = serializer.save()
        
        refresh = RefreshToken.for_user(user)
        return Response({
            'access': str(refresh.access_token),
            'refresh': str(refresh),
            'user': UserSerializer(user).data
        }, status=status.HTTP_201_CREATED)


class TokenRefreshView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        refresh_token = request.data.get('refresh')
        
        if not refresh_token:
            return Response(
                {'error': 'Refresh token required'},
                status=status.HTTP_400_BAD_REQUEST
            )
        
        try:
            refresh = RefreshToken(refresh_token)
            return Response({
                'access': str(refresh.access_token),
            })
        except Exception:
            return Response(
                {'error': 'Invalid refresh token'},
                status=status.HTTP_401_UNAUTHORIZED
            )


class LogoutView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request):
        try:
            refresh_token = request.data.get('refresh')
            token = RefreshToken(refresh_token)
            token.blacklist()
            return Response({'message': 'Successfully logged out'})
        except Exception:
            return Response({'error': 'Invalid token'}, status=status.HTTP_400_BAD_REQUEST)


class AdminLoginView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = AdminLoginSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        
        username = serializer.validated_data['username']
        password = serializer.validated_data['password']
        
        try:
            admin_user = AdminUser.objects.get(username=username)
            if check_password(password, admin_user.password):
                # Log successful login
                AdminLog.objects.create(
                    admin=admin_user,
                    action_type='admin_login',
                    target_type='admin',
                    target_id=admin_user.id,
                    description=f'Admin {username} logged in successfully',
                    ip_address=self.get_client_ip(request),
                    user_agent=request.META.get('HTTP_USER_AGENT', '')
                )
                
                # Generate JWT token (using simplejwt)
                refresh = RefreshToken.for_user(admin_user)
                return Response({
                    'access': str(refresh.access_token),
                    'refresh': str(refresh),
                    'username': admin_user.username,
                    'full_name': admin_user.full_name,
                    'role': admin_user.role,
                    'email': admin_user.email
                })
            else:
                # Log failed login
                AdminLog.objects.create(
                    admin=None,
                    action_type='admin_login',
                    target_type='admin',
                    description=f'Failed login attempt for username: {username}',
                    ip_address=self.get_client_ip(request),
                    user_agent=request.META.get('HTTP_USER_AGENT', '')
                )
                return Response(
                    {'error': 'Invalid username or password'},
                    status=status.HTTP_401_UNAUTHORIZED
                )
        except AdminUser.DoesNotExist:
            # Log failed login
            AdminLog.objects.create(
                admin=None,
                action_type='admin_login',
                target_type='admin',
                description=f'Failed login attempt for username: {username}',
                ip_address=self.get_client_ip(request),
                user_agent=request.META.get('HTTP_USER_AGENT', '')
            )
            return Response(
                {'error': 'Invalid username or password'},
                status=status.HTTP_401_UNAUTHORIZED
            )
    
    def get_client_ip(self, request):
        x_forwarded_for = request.META.get('HTTP_X_FORWARDED_FOR')
        if x_forwarded_for:
            ip = x_forwarded_for.split(',')[0]
        else:
            ip = request.META.get('REMOTE_ADDR')
        return ip


class AdminDashboardStatsView(APIView):
    permission_classes = [IsAuthenticated]

    def get(self, request):
        try:
            # Get resident stats
            resident_count = Resident.objects.count()
            pending_resident = ResidentRegistration.objects.filter(status='pending').count()
            
            # Get certificate stats
            certificate_count = CertificateRequest.objects.count()
            pending_certificate = CertificateRequest.objects.filter(status='pending').count()
            
            # Get business stats
            business_count = BusinessApplication.objects.count()
            pending_business = BusinessApplication.objects.filter(status='pending').count()
            
            # Get RFID stats
            rfid_available = ScannedRFIDCode.objects.filter(status='available').count()
            rfid_assigned = ScannedRFIDCode.objects.filter(status='assigned').count()
            
            # Get services and updates counts
            from apps.general.models import Service, Update
            services_count = Service.objects.count()
            updates_count = Update.objects.count()
            
            return Response({
                'residentCount': resident_count,
                'pendingResident': pending_resident,
                'certificateCount': certificate_count,
                'pendingCertificate': pending_certificate,
                'businessCount': business_count,
                'pendingBusiness': pending_business,
                'servicesCount': services_count,
                'updatesCount': updates_count,
                'rfidAvailable': rfid_available,
                'rfidAssigned': rfid_assigned,
            })
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )


class RFIDLoginView(APIView):
    permission_classes = [AllowAny]

    def post(self, request):
        rfid_code = request.data.get('rfid_code', '').strip().upper()
        
        if not rfid_code:
            return Response(
                {'error': 'RFID code is required'},
                status=status.HTTP_400_BAD_REQUEST
            )
        
        try:
            # Try to find resident by rfid_code or rfid field
            resident = Resident.objects.filter(
                Q(rfid_code=rfid_code) | Q(rfid=rfid_code),
                status='active'
            ).first()
            
            if resident:
                refresh = RefreshToken.for_user(resident)
                return Response({
                    'access': str(refresh.access_token),
                    'refresh': str(refresh),
                    'user': UserSerializer(resident).data
                })
            else:
                return Response(
                    {'error': 'Invalid RFID or user not found'},
                    status=status.HTTP_401_UNAUTHORIZED
                )
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )
