from rest_framework import serializers
from .models import RFIDUser, RFIDRegistration, RFIDAccessLog, ScannedRFIDCode


class RFIDUserSerializer(serializers.ModelSerializer):
    class Meta:
        model = RFIDUser
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']


class RFIDRegistrationSerializer(serializers.ModelSerializer):
    class Meta:
        model = RFIDRegistration
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']


class RFIDAccessLogSerializer(serializers.ModelSerializer):
    class Meta:
        model = RFIDAccessLog
        fields = '__all__'
        read_only_fields = ['access_time']


class ScannedRFIDCodeSerializer(serializers.ModelSerializer):
    class Meta:
        model = ScannedRFIDCode
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']
