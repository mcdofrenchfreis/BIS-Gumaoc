from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import ServiceViewSet, UpdateViewSet, UserReportViewSet, NotificationViewSet, MarkAllReadAPIView

router = DefaultRouter()
router.register(r'services', ServiceViewSet)
router.register(r'updates', UpdateViewSet)
router.register(r'user-reports', UserReportViewSet)
router.register(r'notifications', NotificationViewSet)

urlpatterns = [
    path('', include(router.urls)),
    path('notifications/<int:pk>/mark-read/', NotificationViewSet.as_view({'post': 'mark_read'}), name='notification-mark-read'),
    path('notifications/mark-all-read/', MarkAllReadAPIView.as_view(), name='notifications-mark-all-read'),
]
