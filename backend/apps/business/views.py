from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from .models import BusinessApplication
from .serializers import BusinessApplicationSerializer
from apps.residents.models import Resident


class BusinessApplicationViewSet(viewsets.ModelViewSet):
    queryset = BusinessApplication.objects.all()
    serializer_class = BusinessApplicationSerializer
    filterset_fields = ['status', 'business_type']
    search_fields = ['business_name', 'reference_no']
    ordering_fields = ['submitted_at', 'application_date']

    @action(detail=True, methods=['post'])
    def approve(self, request, pk=None):
        application = self.get_object()
        application.status = 'approved'
        application.save()
        return Response({'message': 'Business application approved'})

    @action(detail=True, methods=['post'])
    def reject(self, request, pk=None):
        application = self.get_object()
        application.status = 'rejected'
        application.save()
        return Response({'message': 'Business application rejected'})


class BusinessApplicationAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request):
        try:
            user = request.user
            if not hasattr(user, 'resident'):
                return Response(
                    {'error': 'User must be a resident'},
                    status=status.HTTP_400_BAD_REQUEST
                )
            
            # Extract form data
            reference_no = request.data.get('reference_no')
            application_date = request.data.get('application_date')
            first_name = request.data.get('first_name')
            middle_name = request.data.get('middle_name')
            last_name = request.data.get('last_name')
            owner_address = request.data.get('owner_address')
            mobile_number = request.data.get('mobile_number')
            business_name = request.data.get('business_name')
            business_type = request.data.get('business_type')
            years_operation = request.data.get('years_operation')
            investment_capital = request.data.get('investment_capital')
            business_address = request.data.get('business_address')
            or_number = request.data.get('or_number')
            ctc_number = request.data.get('ctc_number')
            proof_image = request.FILES.get('proof_image')
            
            # Combine names
            owner_name = f"{first_name} {middle_name + ' ' if middle_name else ''}{last_name}".strip()
            
            # Create business application
            application = BusinessApplication.objects.create(
                user=user.resident,
                reference_no=reference_no,
                application_date=application_date,
                first_name=first_name,
                middle_name=middle_name,
                last_name=last_name,
                business_name=business_name,
                business_type=business_type,
                business_address=business_address,
                business_location=business_address,
                owner_name=owner_name,
                owner_address=owner_address,
                contact_number=mobile_number,
                or_number=or_number,
                ctc_number=ctc_number,
                years_operation=int(years_operation) if years_operation else 1,
                investment_capital=float(investment_capital) if investment_capital else 0.00,
                status='pending'
            )
            
            # Handle file upload
            if proof_image:
                import os
                import uuid
                from django.conf import settings
                
                upload_dir = os.path.join(settings.MEDIA_ROOT, 'business_proof')
                os.makedirs(upload_dir, exist_ok=True)
                
                ext = proof_image.name.split('.')[-1]
                filename = f"business_{uuid.uuid4()}.{ext}"
                filepath = os.path.join(upload_dir, filename)
                
                with open(filepath, 'wb+') as destination:
                    for chunk in proof_image.chunks():
                        destination.write(chunk)
                
                application.proof_image = f"business_proof/{filename}"
                application.save()
            
            return Response({
                'message': 'Business permit application submitted successfully',
                'reference_no': reference_no,
                'status': application.status
            }, status=status.HTTP_201_CREATED)
            
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )
