from django.urls import path
from .views import ServiceViewSet, UpdateViewSet, UserReportViewSet, BackupAPIView, BackupDetailAPIView, RestoreBackupAPIView

urlpatterns = [
    path('services/', ServiceViewSet.as_view({'get': 'list', 'post': 'create'}), name='service-list'),
    path('services/<int:pk>/', ServiceViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='service-detail'),
    path('updates/', UpdateViewSet.as_view({'get': 'list', 'post': 'create'}), name='update-list'),
    path('updates/<int:pk>/', UpdateViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='update-detail'),
    path('reports/', UserReportViewSet.as_view({'get': 'list', 'post': 'create'}), name='report-list'),
    path('reports/<int:pk>/', UserReportViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='report-detail'),
    path('backup/', BackupAPIView.as_view(), name='backup'),
    path('backup/<str:filename>/', BackupDetailAPIView.as_view(), name='backup-detail'),
    path('backup/<str:filename>/restore/', RestoreBackupAPIView.as_view(), name='backup-restore'),
]
