from django.urls import path
from .views import RFIDLoginView, RFIDRegistrationViewSet, RFIDRegistrationAPIView, RFIDAccessLogViewSet, RFIDUserViewSet, ScannedRFIDCodeViewSet

urlpatterns = [
    path('login/', RFIDLoginView.as_view(), name='rfid-login'),
    path('registrations/', RFIDRegistrationAPIView.as_view(), name='rfid-registration'),
    path('registrations-list/', RFIDRegistrationViewSet.as_view({'get': 'list', 'post': 'create'}), name='rfid-registrations'),
    path('registrations-list/<int:pk>/', RFIDRegistrationViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='rfid-registration-detail'),
    path('registrations-list/<int:pk>/approve/', RFIDRegistrationViewSet.as_view({'post': 'approve'}), name='rfid-registration-approve'),
    path('registrations-list/<int:pk>/reject/', RFIDRegistrationViewSet.as_view({'post': 'reject'}), name='rfid-registration-reject'),
    path('users/', RFIDUserViewSet.as_view({'get': 'list', 'post': 'create'}), name='rfid-users'),
    path('users/<int:pk>/', RFIDUserViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='rfid-user-detail'),
    path('logs/', RFIDAccessLogViewSet.as_view({'get': 'list', 'post': 'create'}), name='rfid-logs'),
    path('scanned-codes/', ScannedRFIDCodeViewSet.as_view({'get': 'list', 'post': 'create'}), name='scanned-codes'),
]
