from rest_framework import serializers
from .models import BusinessApplication


class BusinessApplicationSerializer(serializers.ModelSerializer):
    class Meta:
        model = BusinessApplication
        fields = '__all__'
        read_only_fields = ['submitted_at', 'updated_at']
