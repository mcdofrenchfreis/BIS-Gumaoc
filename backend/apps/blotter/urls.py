from django.urls import path
from .views import BarangayBlotterViewSet, BlotterAttachmentViewSet

urlpatterns = [
    path('', BarangayBlotterViewSet.as_view({'get': 'list', 'post': 'create'}), name='blotter-list'),
    path('<int:pk>/', BarangayBlotterViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='blotter-detail'),
    path('<int:pk>/attachments/', BlotterAttachmentViewSet.as_view({'get': 'list', 'post': 'create'}), name='blotter-attachments'),
]
