from django.urls import path
from .views import BusinessApplicationViewSet, BusinessApplicationAPIView

urlpatterns = [
    path('applications/', BusinessApplicationAPIView.as_view(), name='business_application'),
    path('applications-list/', BusinessApplicationViewSet.as_view({'get': 'list', 'post': 'create'}), name='business-list'),
    path('applications-list/<int:pk>/', BusinessApplicationViewSet.as_view({'get': 'retrieve', 'put': 'update', 'delete': 'destroy'}), name='business-detail'),
    path('applications-list/<int:pk>/approve/', BusinessApplicationViewSet.as_view({'post': 'approve'}), name='business-approve'),
    path('applications-list/<int:pk>/reject/', BusinessApplicationViewSet.as_view({'post': 'reject'}), name='business-reject'),
]
