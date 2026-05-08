from django.urls import path
from .views import BusinessApplicationViewSet

urlpatterns = [
    path('applications/', BusinessApplicationViewSet.as_view({'get': 'list', 'post': 'create'}), name='business-list'),
    path('applications/<int:pk>/', BusinessApplicationViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='business-detail'),
]
