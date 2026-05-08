from django.urls import path
from .views import RFIDLoginView, RFIDRegistrationViewSet, RFIDAccessLogViewSet

urlpatterns = [
    path('login/', RFIDLoginView.as_view(), name='rfid-login'),
    path('registrations/', RFIDRegistrationViewSet.as_view({'get': 'list', 'post': 'create'}), name='rfid-registration-list'),
    path('registrations/<int:pk>/', RFIDRegistrationViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='rfid-registration-detail'),
    path('logs/', RFIDAccessLogViewSet.as_view({'get': 'list'}), name='rfid-logs'),
]
