from django.urls import path
from .views import QueueTicketViewSet, QueueServiceViewSet

urlpatterns = [
    path('tickets/', QueueTicketViewSet.as_view({'get': 'list', 'post': 'create'}), name='ticket-list'),
    path('tickets/<int:pk>/', QueueTicketViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='ticket-detail'),
    path('services/', QueueServiceViewSet.as_view({'get': 'list'}), name='service-list'),
]
