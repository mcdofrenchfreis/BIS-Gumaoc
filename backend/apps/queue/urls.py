from django.urls import path
from .views import QueueServiceViewSet, QueueCounterViewSet, QueueWindowViewSet, QueueTicketViewSet, QueueManagementAPIView, GenerateTicketAPIView

urlpatterns = [
    path('services/', QueueServiceViewSet.as_view({'get': 'list', 'post': 'create'}), name='queue-service-list'),
    path('services/<int:pk>/', QueueServiceViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='queue-service-detail'),
    path('counters/', QueueCounterViewSet.as_view({'get': 'list', 'post': 'create'}), name='queue-counter-list'),
    path('counters/<int:pk>/', QueueCounterViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='queue-counter-detail'),
    path('counters/<int:pk>/call-next/', QueueCounterViewSet.as_view({'post': 'call_next'}), name='queue-counter-call-next'),
    path('counters/<int:pk>/complete-ticket/', QueueCounterViewSet.as_view({'post': 'complete_ticket'}), name='queue-counter-complete'),
    path('windows/', QueueWindowViewSet.as_view({'get': 'list', 'post': 'create'}), name='queue-window-list'),
    path('windows/<int:pk>/', QueueWindowViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='queue-window-detail'),
    path('tickets/', QueueTicketViewSet.as_view({'get': 'list', 'post': 'create'}), name='queue-ticket-list'),
    path('tickets/<int:pk>/', QueueTicketViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='queue-ticket-detail'),
    path('tickets/<int:pk>/call/', QueueTicketViewSet.as_view({'post': 'call'}), name='queue-ticket-call'),
    path('tickets/<int:pk>/complete/', QueueTicketViewSet.as_view({'post': 'complete'}), name='queue-ticket-complete'),
    path('tickets/<int:pk>/cancel/', QueueTicketViewSet.as_view({'post': 'cancel'}), name='queue-ticket-cancel'),
    path('tickets/queue-status/', QueueTicketViewSet.as_view({'get': 'queue_status'}), name='queue-status'),
    path('tickets/currently-serving/', QueueTicketViewSet.as_view({'get': 'currently_serving'}), name='queue-currently-serving'),
    path('tickets/next-in-queue/', QueueTicketViewSet.as_view({'get': 'next_in_queue'}), name='queue-next'),
    path('tickets/generate/', GenerateTicketAPIView.as_view(), name='queue-generate-ticket'),
    path('management/', QueueManagementAPIView.as_view(), name='queue-management'),
]
