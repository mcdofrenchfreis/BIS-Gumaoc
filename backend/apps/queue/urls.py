from django.urls import path
from .views import QueueServiceViewSet, QueueCounterViewSet, QueueWindowViewSet, QueueTicketViewSet, QueueStatusAPIView, GenerateTicketAPIView

urlpatterns = [
    path('services/', QueueServiceViewSet.as_view({'get': 'list', 'post': 'create'}), name='queue-services'),
    path('services/<int:pk>/', QueueServiceViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='queue-service-detail'),
    path('counters/', QueueCounterViewSet.as_view({'get': 'list', 'post': 'create'}), name='queue-counters'),
    path('windows/', QueueWindowViewSet.as_view({'get': 'list', 'post': 'create'}), name='queue-windows'),
    path('status/', QueueStatusAPIView.as_view(), name='queue-status'),
    path('tickets/', GenerateTicketAPIView.as_view(), name='generate-ticket'),
    path('tickets-list/', QueueTicketViewSet.as_view({'get': 'list', 'post': 'create'}), name='queue-tickets'),
    path('tickets-list/<int:pk>/', QueueTicketViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='queue-ticket-detail'),
    path('tickets-list/<int:pk>/call/', QueueTicketViewSet.as_view({'post': 'call'}), name='queue-ticket-call'),
    path('tickets-list/<int:pk>/complete/', QueueTicketViewSet.as_view({'post': 'complete'}), name='queue-ticket-complete'),
    path('tickets-list/<int:pk>/cancel/', QueueTicketViewSet.as_view({'post': 'cancel'}), name='queue-ticket-cancel'),
]
