from django.urls import path
from .views import CertificateRequestViewSet, CaptainClearanceViewSet, CertificateRequestAPIView

urlpatterns = [
    path('request/', CertificateRequestAPIView.as_view(), name='certificate_request'),
    path('requests/', CertificateRequestViewSet.as_view({'get': 'list', 'post': 'create'}), name='certificate-list'),
    path('requests/<int:pk>/', CertificateRequestViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='certificate-detail'),
    path('requests/<int:pk>/print/', CertificateRequestViewSet.as_view({'post': 'print'}), name='certificate-print'),
    path('captain-clearances/', CaptainClearanceViewSet.as_view({'get': 'list', 'post': 'create'}), name='clearance-list'),
    path('captain-clearances/<int:pk>/', CaptainClearanceViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='clearance-detail'),
]
