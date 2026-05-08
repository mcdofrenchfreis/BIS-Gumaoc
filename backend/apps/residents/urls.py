from django.urls import path
from .views import ResidentViewSet, ResidentRegistrationViewSet

urlpatterns = [
    path('', ResidentViewSet.as_view({'get': 'list', 'post': 'create'}), name='resident-list'),
    path('<int:pk>/', ResidentViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='resident-detail'),
    path('registrations/', ResidentRegistrationViewSet.as_view({'get': 'list', 'post': 'create'}), name='registration-list'),
    path('registrations/<int:pk>/', ResidentRegistrationViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='registration-detail'),
]
