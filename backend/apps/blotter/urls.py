from django.urls import path
from .views import BarangayBlotterViewSet, BlotterStatsAPIView, BlotterAttachmentViewSet

urlpatterns = [
    path('stats/', BlotterStatsAPIView.as_view(), name='blotter_stats'),
    path('records/', BarangayBlotterViewSet.as_view({'get': 'list', 'post': 'create'}), name='blotter-list'),
    path('records/<int:pk>/', BarangayBlotterViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='blotter-detail'),
    path('records/<int:pk>/update_status/', BarangayBlotterViewSet.as_view({'post': 'update_status'}), name='blotter-update-status'),
    path('records/<int:pk>/attachments/', BlotterAttachmentViewSet.as_view({'get': 'list', 'post': 'create'}), name='blotter-attachments'),
]
