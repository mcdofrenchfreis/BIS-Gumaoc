from rest_framework import serializers
from .models import BarangayBlotter, BlotterAttachment


class BlotterAttachmentSerializer(serializers.ModelSerializer):
    class Meta:
        model = BlotterAttachment
        fields = '__all__'


class BarangayBlotterSerializer(serializers.ModelSerializer):
    attachments = BlotterAttachmentSerializer(many=True, read_only=True)

    class Meta:
        model = BarangayBlotter
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at', 'reported_date']
