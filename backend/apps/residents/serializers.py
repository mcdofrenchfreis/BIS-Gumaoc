from rest_framework import serializers
from .models import Resident, ResidentRegistration, FamilyMember, FamilyDisability, FamilyOrganization, ResidentStatus


class ResidentSerializer(serializers.ModelSerializer):
    class Meta:
        model = Resident
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']


class ResidentRegistrationSerializer(serializers.ModelSerializer):
    class Meta:
        model = ResidentRegistration
        fields = '__all__'
        read_only_fields = ['submitted_at']


class FamilyMemberSerializer(serializers.ModelSerializer):
    class Meta:
        model = FamilyMember
        fields = '__all__'
        read_only_fields = ['created_at']


class FamilyDisabilitySerializer(serializers.ModelSerializer):
    class Meta:
        model = FamilyDisability
        fields = '__all__'


class FamilyOrganizationSerializer(serializers.ModelSerializer):
    class Meta:
        model = FamilyOrganization
        fields = '__all__'


class ResidentStatusSerializer(serializers.ModelSerializer):
    class Meta:
        model = ResidentStatus
        fields = '__all__'
        read_only_fields = ['created_at', 'last_updated']
