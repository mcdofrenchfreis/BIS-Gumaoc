from rest_framework import viewsets
from .models import Resident, ResidentRegistration
from .serializers import ResidentSerializer, ResidentRegistrationSerializer


class ResidentViewSet(viewsets.ModelViewSet):
    queryset = Resident.objects.all()
    serializer_class = ResidentSerializer
    filterset_fields = ['status', 'gender', 'civil_status']
    search_fields = ['first_name', 'last_name', 'email']
    ordering_fields = ['created_at', 'last_name']


class ResidentRegistrationViewSet(viewsets.ModelViewSet):
    queryset = ResidentRegistration.objects.all()
    serializer_class = ResidentRegistrationSerializer
    filterset_fields = ['status', 'gender', 'civil_status']
    search_fields = ['first_name', 'last_name', 'email']
    ordering_fields = ['submitted_at']
