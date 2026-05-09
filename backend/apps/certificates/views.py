from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from django.http import HttpResponse
from django.template.loader import render_to_string
from .models import CertificateRequest, CaptainClearance
from .serializers import CertificateRequestSerializer, CaptainClearanceSerializer
from apps.residents.models import Resident


class CertificateRequestViewSet(viewsets.ModelViewSet):
    queryset = CertificateRequest.objects.all()
    serializer_class = CertificateRequestSerializer
    filterset_fields = ['certificate_type', 'status']
    search_fields = ['applicant_name', 'certificate_number']
    ordering_fields = ['created_at', 'issued_date']

    @action(detail=True, methods=['post'])
    def print(self, request, pk=None):
        certificate = self.get_object()
        certificate.status = 'released'
        certificate.save(update_fields=['status'])
        return Response({'message': 'Certificate marked as released'})

    @action(detail=True, methods=['get'])
    def print_barangay_clearance(self, request, pk=None):
        certificate = self.get_object()
        if certificate.certificate_type != 'BRGY. CLEARANCE':
            return Response({'error': 'Not a barangay clearance certificate'}, status=400)

        html_content = render_to_string('certificates/print_barangay_clearance.html', {
            'certificate': certificate,
            'date_issued': certificate.updated_at.strftime('%B %d, %Y'),
        })
        return HttpResponse(html_content, content_type='text/html')

    @action(detail=True, methods=['get'])
    def print_business_clearance(self, request, pk=None):
        certificate = self.get_object()
        if certificate.certificate_type != 'BRGY. CLEARANCE':
            return Response({'error': 'Not a business clearance certificate'}, status=400)

        html_content = render_to_string('certificates/print_business_clearance.html', {
            'certificate': certificate,
            'date_issued': certificate.updated_at.strftime('%B %d, %Y'),
        })
        return HttpResponse(html_content, content_type='text/html')

    @action(detail=True, methods=['get'])
    def print_cedula(self, request, pk=None):
        certificate = self.get_object()
        if certificate.certificate_type not in ['CEDULA', 'CEDULA/CTC']:
            return Response({'error': 'Not a cedula certificate'}, status=400)

        import json
        additional_data = json.loads(certificate.additional_data or '{}')

        html_content = render_to_string('certificates/print_cedula.html', {
            'certificate': certificate,
            'additional_data': additional_data,
            'date_issued': certificate.updated_at.strftime('%B %d, %Y'),
        })
        return HttpResponse(html_content, content_type='text/html')

    @action(detail=True, methods=['get'])
    def print_indigency(self, request, pk=None):
        certificate = self.get_object()
        if certificate.certificate_type != 'BRGY. INDIGENCY':
            return Response({'error': 'Not an indigency certificate'}, status=400)

        html_content = render_to_string('certificates/print_indigency.html', {
            'certificate': certificate,
            'date_issued': certificate.updated_at.strftime('%B %d, %Y'),
        })
        return HttpResponse(html_content, content_type='text/html')

    @action(detail=True, methods=['get'])
    def print_residency(self, request, pk=None):
        certificate = self.get_object()
        if certificate.certificate_type != 'PROOF OF RESIDENCY':
            return Response({'error': 'Not a residency certificate'}, status=400)

        html_content = render_to_string('certificates/print_residency.html', {
            'certificate': certificate,
            'date_issued': certificate.updated_at.strftime('%B %d, %Y'),
        })
        return HttpResponse(html_content, content_type='text/html')

    @action(detail=True, methods=['get'])
    def print_tricycle_permit(self, request, pk=None):
        certificate = self.get_object()
        if certificate.certificate_type != 'TRICYCLE PERMIT':
            return Response({'error': 'Not a tricycle permit certificate'}, status=400)

        html_content = render_to_string('certificates/print_tricycle_permit.html', {
            'certificate': certificate,
            'date_issued': certificate.updated_at.strftime('%B %d, %Y'),
        })
        return HttpResponse(html_content, content_type='text/html')


class CertificateRequestAPIView(APIView):
    permission_classes = [IsAuthenticated]

    def post(self, request):
        try:
            user = request.user
            if not hasattr(user, 'resident'):
                return Response(
                    {'error': 'User must be a resident'},
                    status=status.HTTP_400_BAD_REQUEST
                )
            
            certificate_type = request.data.get('certificate_type')
            full_name = request.data.get('full_name')
            address = request.data.get('address')
            mobile_number = request.data.get('mobile_number')
            civil_status = request.data.get('civil_status')
            gender = request.data.get('gender')
            birth_date = request.data.get('birth_date')
            birth_place = request.data.get('birth_place')
            citizenship = request.data.get('citizenship', 'Filipino')
            years_of_residence = request.data.get('years_of_residence')
            purpose = request.data.get('purpose')
            photo_image = request.FILES.get('photo_image')
            tricycle_image = request.FILES.get('tricycle_image')
            proof_image = request.FILES.get('proof_image')
            
            # Create certificate request
            certificate_request = CertificateRequest.objects.create(
                user=user.resident,
                certificate_type=certificate_type,
                full_name=full_name,
                address=address,
                mobile_number=mobile_number,
                civil_status=civil_status,
                gender=gender,
                birth_date=birth_date,
                birth_place=birth_place,
                citizenship=citizenship,
                years_of_residence=years_of_residence,
                purpose=purpose,
                status='pending'
            )
            
            # Handle file uploads
            if photo_image:
                import os
                import uuid
                from django.conf import settings
                
                upload_dir = os.path.join(settings.MEDIA_ROOT, 'user_photos')
                os.makedirs(upload_dir, exist_ok=True)
                
                ext = photo_image.name.split('.')[-1]
                filename = f"photo_{uuid.uuid4()}.{ext}"
                filepath = os.path.join(upload_dir, filename)
                
                with open(filepath, 'wb+') as destination:
                    for chunk in photo_image.chunks():
                        destination.write(chunk)
                
                certificate_request.photo_2x2 = f"user_photos/{filename}"
            
            if tricycle_image:
                import os
                import uuid
                from django.conf import settings
                
                upload_dir = os.path.join(settings.MEDIA_ROOT, 'tricycle_photos')
                os.makedirs(upload_dir, exist_ok=True)
                
                ext = tricycle_image.name.split('.')[-1]
                filename = f"tricycle_{uuid.uuid4()}.{ext}"
                filepath = os.path.join(upload_dir, filename)
                
                with open(filepath, 'wb+') as destination:
                    for chunk in tricycle_image.chunks():
                        destination.write(chunk)
                
                certificate_request.tricycle_photo = f"tricycle_photos/{filename}"
            
            if proof_image:
                import os
                import uuid
                from django.conf import settings
                
                upload_dir = os.path.join(settings.MEDIA_ROOT, 'certificate_proof')
                os.makedirs(upload_dir, exist_ok=True)
                
                ext = proof_image.name.split('.')[-1]
                filename = f"proof_{uuid.uuid4()}.{ext}"
                filepath = os.path.join(upload_dir, filename)
                
                with open(filepath, 'wb+') as destination:
                    for chunk in proof_image.chunks():
                        destination.write(chunk)
                
                certificate_request.proof_image = f"certificate_proof/{filename}"
            
            # Handle certificate-specific data
            additional_data = {}
            
            if certificate_type == 'TRICYCLE PERMIT':
                additional_data = {
                    'vehicle_make_type': request.data.get('vehicle_make_type'),
                    'motor_no': request.data.get('motor_no'),
                    'chassis_no': request.data.get('chassis_no'),
                    'plate_no': request.data.get('plate_no'),
                    'vehicle_color': request.data.get('vehicle_color'),
                    'year_model': request.data.get('year_model'),
                    'body_no': request.data.get('body_no'),
                    'operator_license': request.data.get('operator_license'),
                }
                
                certificate_request.vehicle_make_type = additional_data['vehicle_make_type']
                certificate_request.motor_no = additional_data['motor_no']
                certificate_request.chassis_no = additional_data['chassis_no']
                certificate_request.plate_no = additional_data['plate_no']
                certificate_request.vehicle_color = additional_data['vehicle_color']
                if additional_data['year_model']:
                    certificate_request.year_model = int(additional_data['year_model'])
                certificate_request.body_no = additional_data['body_no']
                certificate_request.operator_license = additional_data['operator_license']
                
            elif certificate_type == 'CEDULA/CTC':
                additional_data = {
                    'cedula_year': request.data.get('cedula_year'),
                    'place_of_issue': request.data.get('place_of_issue'),
                    'date_issued': request.data.get('date_issued'),
                    'profession_occupation': request.data.get('profession_occupation'),
                    'height': request.data.get('height'),
                    'weight': request.data.get('weight'),
                    'basic_tax_type': request.data.get('basic_tax_type'),
                    'basic_community_tax': request.data.get('basic_community_tax'),
                    'gross_receipts_business': request.data.get('gross_receipts_business'),
                    'salaries_profession': request.data.get('salaries_profession'),
                    'income_real_property': request.data.get('income_real_property'),
                    'total_tax': request.data.get('total_tax'),
                    'interest': request.data.get('interest'),
                    'total_amount_paid': request.data.get('total_amount_paid'),
                }
            
            import json
            certificate_request.additional_data = json.dumps(additional_data)
            certificate_request.save()
            
            return Response({
                'message': 'Certificate request submitted successfully',
                'request_id': certificate_request.id,
                'status': certificate_request.status
            }, status=status.HTTP_201_CREATED)
            
        except Exception as e:
            return Response(
                {'error': str(e)},
                status=status.HTTP_500_INTERNAL_SERVER_ERROR
            )


class CaptainClearanceViewSet(viewsets.ModelViewSet):
    queryset = CaptainClearance.objects.all()
    serializer_class = CaptainClearanceSerializer
    filterset_fields = ['issued_date', 'expires_date']
    search_fields = ['clearance_number']
    ordering_fields = ['issued_date']
