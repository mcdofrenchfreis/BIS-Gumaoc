from django.urls import path
from .views import CertificateRequestViewSet, CaptainClearanceViewSet, CertificateRequestAPIView

urlpatterns = [
    path('request/', CertificateRequestAPIView.as_view(), name='certificate_request'),
    path('requests/', CertificateRequestViewSet.as_view({'get': 'list', 'post': 'create'}), name='certificate-list'),
    path('requests/<int:pk>/', CertificateRequestViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='certificate-detail'),
    path('requests/<int:pk>/print/', CertificateRequestViewSet.as_view({'post': 'print'}), name='certificate-print'),
    path('requests/<int:pk>/print-barangay-clearance/', CertificateRequestViewSet.as_view({'get': 'print_barangay_clearance'}), name='print-barangay-clearance'),
    path('requests/<int:pk>/print-business-clearance/', CertificateRequestViewSet.as_view({'get': 'print_business_clearance'}), name='print-business-clearance'),
    path('requests/<int:pk>/print-cedula/', CertificateRequestViewSet.as_view({'get': 'print_cedula'}), name='print-cedula'),
    path('requests/<int:pk>/print-indigency/', CertificateRequestViewSet.as_view({'get': 'print_indigency'}), name='print-indigency'),
    path('requests/<int:pk>/print-residency/', CertificateRequestViewSet.as_view({'get': 'print_residency'}), name='print-residency'),
    path('requests/<int:pk>/print-tricycle-permit/', CertificateRequestViewSet.as_view({'get': 'print_tricycle_permit'}), name='print-tricycle-permit'),
    path('captain-clearances/', CaptainClearanceViewSet.as_view({'get': 'list', 'post': 'create'}), name='clearance-list'),
    path('captain-clearances/<int:pk>/', CaptainClearanceViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='clearance-detail'),
]
