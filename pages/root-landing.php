<!-- Landing Selector -->
<div class="container" style="margin-top: 1.5rem;">
  <div class="section section-header-card" style="margin-bottom: 1.5rem;">
    <div class="section-header-content">
      <div class="section-icon">🚦</div>
      <h2>Welcome to Barangay Gumaoc East System</h2>
      <p>Please choose how you want to continue</p>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
    <!-- User Portal -->
    <a href="user/login.php" class="service-btn" style="
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: .5rem; text-decoration: none; padding: 1.25rem; border-radius: 16px;
      background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
      border: 1px solid #bbdefb; box-shadow: 0 6px 18px rgba(25,118,210,.1);
      font-weight: 700; color: #1565c0;">
      <div style="font-size: 2rem;">👤</div>
      <div style="font-size: 1.1rem;">User</div>
      <div style="font-weight: 500; color:#0d47a1; opacity:.85; font-size:.95rem;">Login to your account</div>
    </a>

    <!-- Admin Portal -->
    <a href="admin/login.php" class="service-btn" style="
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: .5rem; text-decoration: none; padding: 1.25rem; border-radius: 16px;
      background: linear-gradient(135deg, #fff3e0 0%, #ffffff 100%);
      border: 1px solid #ffe0b2; box-shadow: 0 6px 18px rgba(255,152,0,.12);
      font-weight: 700; color: #ef6c00;">
      <div style="font-size: 2rem;">🛡️</div>
      <div style="font-size: 1.1rem;">Admin</div>
      <div style="font-weight: 500; color:#e65100; opacity:.85; font-size:.95rem;">Authorized personnel</div>
    </a>
  </div>
</div>

<style>
  /* Minor adjustments so cards feel consistent with theme */
  .section-header-card { overflow: hidden; }
  .service-btn { transition: transform .15s ease, box-shadow .15s ease; }
  .service-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.12); }
</style>
