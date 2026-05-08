from django.urls import path
from .views import LoginView, RegisterView, TokenRefreshView, LogoutView, AdminLoginView, AdminDashboardStatsView, RFIDLoginView

urlpatterns = [
    path('login/', LoginView.as_view(), name='login'),
    path('register/', RegisterView.as_view(), name='register'),
    path('token/refresh/', TokenRefreshView.as_view(), name='token_refresh'),
    path('logout/', LogoutView.as_view(), name='logout'),
    path('admin/login/', AdminLoginView.as_view(), name='admin_login'),
    path('admin/dashboard/stats/', AdminDashboardStatsView.as_view(), name='admin_dashboard_stats'),
    path('rfid-login/', RFIDLoginView.as_view(), name='rfid_login'),
]
