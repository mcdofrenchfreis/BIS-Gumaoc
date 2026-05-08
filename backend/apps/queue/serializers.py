from rest_framework import serializers
from .models import QueueTicket, QueueService, QueueCounter, QueueWindow


class QueueServiceSerializer(serializers.ModelSerializer):
    class Meta:
        model = QueueService
        fields = '__all__'


class QueueCounterSerializer(serializers.ModelSerializer):
    class Meta:
        model = QueueCounter
        fields = '__all__'


class QueueWindowSerializer(serializers.ModelSerializer):
    services = QueueServiceSerializer(many=True, read_only=True)

    class Meta:
        model = QueueWindow
        fields = '__all__'


class QueueTicketSerializer(serializers.ModelSerializer):
    service = QueueServiceSerializer(read_only=True)
    window = QueueWindowSerializer(read_only=True)

    class Meta:
        model = QueueTicket
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']
