from rest_framework import serializers
from .models import CertificateRequest, CaptainClearance


class CertificateRequestSerializer(serializers.ModelSerializer):
    class Meta:
        model = CertificateRequest
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']


class CaptainClearanceSerializer(serializers.ModelSerializer):
    class Meta:
        model = CaptainClearance
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']
