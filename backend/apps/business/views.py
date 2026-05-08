from rest_framework import viewsets
from .models import BusinessApplication
from .serializers import BusinessApplicationSerializer


class BusinessApplicationViewSet(viewsets.ModelViewSet):
    queryset = BusinessApplication.objects.all()
    serializer_class = BusinessApplicationSerializer
    filterset_fields = ['status', 'business_type']
    search_fields = ['business_name', 'owner_name', 'reference_no']
    ordering_fields = ['submitted_at', 'application_date']
