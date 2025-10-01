<?php
session_start();
$base_path = '../';
$page_title = 'Assistance Applications - Barangay Gumaoc East';

// Optional: fetch logged-in user for auto-population (mirrors certificate-request pattern)
$current_user = null;
try {
  if (isset($_SESSION['rfid_authenticated']) && $_SESSION['rfid_authenticated'] === true && isset($_SESSION['user_id'])) {
    include_once '../includes/db_connect.php';
    $stmt = $pdo->prepare('SELECT * FROM residents WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
  }
} catch (Throwable $e) { /* fail silently for UI */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>css/styles.css">
  <style>
    body { padding-top: 64px; }
    /* Background and overlay similar to certificate-request */
    body {
      background: url('<?php echo $base_path; ?>assets/images/background.jpg') center/cover no-repeat fixed;
      background-color: #2d5a27;
      min-height: 100vh;
      position: relative;
    }
    body::before {
      content: '';
      position: fixed; inset: 0;
      background: linear-gradient(135deg,
        rgba(45, 90, 39, 0.7) 0%,
        rgba(74, 124, 89, 0.6) 25%,
        rgba(53, 122, 60, 0.65) 50%,
        rgba(45, 90, 39, 0.7) 75%,
        rgba(30, 58, 26, 0.8) 100%);
      z-index: 0;
      pointer-events: none;
    }

    .assist-page {
      min-height: 100vh;
      position: relative;
    }
    .assist-container { max-width: 1600px; margin: 0 auto; padding: 20px; position: relative; z-index: 1; }
    
    /* Subtle entrance animation */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px);} to { opacity: 1; transform: translateY(0);} }
    .assist-card, .assist-form { animation: fadeInUp .5s ease forwards; }
    .assist-card:nth-child(1){ animation-delay:.05s }
    .assist-card:nth-child(2){ animation-delay:.1s }
    .assist-card:nth-child(3){ animation-delay:.15s }

    /* Two-column layout: form left, cards right */
    .assist-layout { display:grid; grid-template-columns: minmax(0, 1.6fr) 360px; gap: 24px; align-items: start; }
    @media (max-width: 1200px){ .assist-layout { grid-template-columns: minmax(0, 1.2fr) 340px; } }
    @media (max-width: 992px){ .assist-layout { grid-template-columns: 1fr; } }

    .assist-grid { display: grid; grid-template-columns: 1fr; gap: 14px; }
    .assist-card {
      background: rgba(255,255,255,.88);
      border: 1px solid rgba(27,94,32,.12);
      border-radius: 18px;
      padding: 22px;
      box-shadow: 0 12px 30px rgba(27,94,32,.10);
      transition: transform .25s ease, box-shadow .25s ease, border-color .2s ease;
      cursor: pointer;
    }
    .assist-card:hover { transform: translateY(-6px); box-shadow: 0 20px 46px rgba(27,94,32,.14); border-color:#4caf50; }
    .assist-card[aria-selected="true"] { border-color:#1b5e20; box-shadow: 0 16px 44px rgba(27,94,32,.18); }
    .assist-card h3 { color:#1b5e20; margin: 10px 0 8px; }
    .assist-card p { margin: 0; color:#333; font-size: .98rem; }
    .assist-card .icon-badge { width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; background: #e8f5e9; color:#1b5e20; font-size:24px; box-shadow: inset 0 0 0 2px rgba(27,94,32,.15); }

    .assist-form {
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(27,94,32,.12);
      border-radius: 18px;
      padding: 24px;
      box-shadow: 0 12px 30px rgba(27,94,32,.10);
    }
    .assist-form h2 { color:#1b5e20; margin:0 0 6px; }
    .form-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(300px,1fr)); gap:12px; }
    .form-grid-two { display:grid; grid-template-columns: repeat(auto-fit, minmax(300px,1fr)); gap:12px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group label { color:#1b5e20; font-weight:600; }
    .form-group input, .form-group select, .form-group textarea {
      padding: 12px 12px; border-radius: 10px; border:1px solid rgba(27,94,32,.25);
      outline: none; font: inherit; background: #fff;
    }
    .form-group textarea { min-height: 120px; resize: vertical; }
    .assist-btn {
      display: inline-block; margin-top: 10px; padding: 12px 18px; border-radius: 22px; 
      border: 2px solid #1b5e20; color:#1b5e20; background: rgba(27,94,32,.06);
      font-weight: 700; text-decoration: none; cursor: pointer; transition: all .2s ease;
    }
    .assist-btn:hover { background: rgba(27,94,32,.16); transform: translateY(-1px); }
    .note { font-size: .92rem; color:#2f2f2f; opacity:.9; }

    .toast { position: fixed; right: 16px; bottom: 16px; background: #e8f5e9; border:1px solid #4caf50; padding:12px 14px; border-radius: 12px; box-shadow: 0 8px 26px rgba(76,175,80,.28); display:none; z-index: 10; }
    .sidebar-title { color:#ffffff; font-weight: 800; margin: 0 0 10px; }
    .sidebar { position: sticky; top: 88px; }
    @media (max-width: 480px){ .assist-hero h1 { font-size: 1.6rem; } }
  </style>
</head>
<body>
  <?php include '../includes/mini_nav.php'; ?>

  <div class="assist-page">
    <div class="assist-container">
      <div class="assist-layout">
        <!-- Main form (left) -->
        <div>
          <div class="assist-form">
            <h2>Assistance Request</h2>
            <p class="note">This is a preliminary request. You may be asked to provide additional documents for verification.</p>
            <form id="assistForm" onsubmit="return submitAssistForm(event)" enctype="multipart/form-data">
              <fieldset>
                <legend>Personal Information</legend>

                <div class="form-group">
                  <label for="requestDate">Date *</label>
                  <input type="date" id="requestDate" name="requestDate" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-grid">
                  <div class="form-group">
                    <label for="firstName">First Name *</label>
                    <input type="text" id="firstName" name="firstName" required placeholder="Enter first name" value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" placeholder="Enter middle name" value="<?php echo htmlspecialchars($current_user['middle_name'] ?? ''); ?>">
                  </div>
                  <div class="form-group">
                    <label for="lastName">Last Name *</label>
                    <input type="text" id="lastName" name="lastName" required placeholder="Enter last name" value="<?php echo htmlspecialchars($current_user['last_name'] ?? ''); ?>">
                  </div>
                </div>

                <div class="form-grid-two">
                  <div class="form-group">
                    <label for="address1">Address Line 1 *</label>
                    <input type="text" id="address1" name="address1" required placeholder="House/Lot/Block No., Street, Subdivision" value="<?php echo htmlspecialchars($current_user['address'] ?? ''); ?>">
                    <small class="input-help">Enter your specific house address details</small>
                  </div>
                  <div class="form-group">
                    <label for="address2">Address Line 2</label>
                    <input type="text" id="address2" name="address2" placeholder="Purok/Zone/Sitio (optional)" value="Barangay Gumaoc East, San Jose Del Monte, Bulacan">
                    <small class="input-help">Additional address details (Barangay is pre-filled)</small>
                  </div>
                </div>

                <div class="form-grid">
                  <div class="form-group">
                    <label for="mobileNumber">Mobile Number</label>
                    <div class="mobile-input-container" style="display:flex; align-items:center; gap:8px;">
                      <div class="country-code" style="display:flex; align-items:center; gap:6px; padding:10px 10px; border:1px solid rgba(27,94,32,.25); border-radius:10px; background:#fff;">
                        <span class="ph-flag">🇵🇭</span>
                        <span class="code">+63</span>
                      </div>
                      <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="9XX XXX XXXX" pattern="9[0-9]{9}" maxlength="10" title="Enter PH mobile number without +63 (10 digits starting with 9)" value="<?php echo isset($current_user['phone']) ? preg_replace('/^(\+?63)/','',$current_user['phone']) : ''; ?>">
                    </div>
                    <small class="input-help">Enter your mobile number without +63 (e.g., 9171234567)</small>
                  </div>
                  <div class="form-group">
                    <label for="civilStatus">Civil Status *</label>
                    <select id="civilStatus" name="civilStatus" required>
                      <?php $cs = $current_user['civil_status'] ?? ''; ?>
                      <option value="">Select Civil Status</option>
                      <option value="Single" <?php echo ($cs==='Single')?'selected':''; ?>>Single</option>
                      <option value="Married" <?php echo ($cs==='Married')?'selected':''; ?>>Married</option>
                      <option value="Divorced" <?php echo ($cs==='Divorced')?'selected':''; ?>>Divorced</option>
                      <option value="Widowed" <?php echo ($cs==='Widowed')?'selected':''; ?>>Widowed</option>
                      <option value="Separated" <?php echo ($cs==='Separated')?'selected':''; ?>>Separated</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="gender">Gender *</label>
                    <?php $g = $current_user['gender'] ?? ''; ?>
                    <select id="gender" name="gender" required>
                      <option value="">Select Gender</option>
                      <option value="Male" <?php echo ($g==='Male')?'selected':''; ?>>Male</option>
                      <option value="Female" <?php echo ($g==='Female')?'selected':''; ?>>Female</option>
                    </select>
                  </div>
                </div>

                <div class="form-grid">
                  <div class="form-group">
                    <label for="birthdate">Birthdate *</label>
                    <input type="date" id="birthdate" name="birthdate" required value="<?php echo htmlspecialchars($current_user['birthdate'] ?? ''); ?>" onchange="calculateAge()">
                  </div>
                  <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" placeholder="" readonly>
                  </div>
                </div>
              </fieldset>

              <fieldset>
                <legend>Assistance Details</legend>
                <div class="form-grid">
                  <div class="form-group">
                    <label for="assistanceType">Assistance Type *</label>
                    <select id="assistanceType" name="assistanceType" required>
                      <option value="">Select type</option>
                      <option value="Financial Aid">Financial Aid</option>
                      <option value="Medical Assistance">Medical Assistance</option>
                      <option value="Documentation">Documentation</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="supportingFile">Supporting File (optional)</label>
                    <input type="file" id="supportingFile" name="supportingFile" accept="image/*,application/pdf">
                  </div>
                </div>

                <div class="form-group">
                  <label for="details">Details / Reason *</label>
                  <textarea id="details" name="details" placeholder="Briefly describe your request" required></textarea>
                </div>
              </fieldset>

              <button type="submit" class="assist-btn">Submit Request</button>
            </form>
          </div>
        </div>

        <!-- Sidebar (right): Assistance cards) -->
        <aside class="sidebar">
          <h3 class="sidebar-title">Available Assistance</h3>
          <div class="assist-grid">
            <div class="assist-card" data-type="Financial Aid" role="button" tabindex="0" aria-selected="false">
              <div class="icon-badge">💸</div>
              <h3>Financial Aid</h3>
              <p>Temporary support for qualified residents experiencing hardship.</p>
            </div>
            <div class="assist-card" data-type="Medical Assistance" role="button" tabindex="0" aria-selected="false">
              <div class="icon-badge">🏥</div>
              <h3>Medical Assistance</h3>
              <p>Help with medicine, diagnostics, or emergency care coordination.</p>
            </div>
            <div class="assist-card" data-type="Documentation" role="button" tabindex="0" aria-selected="false">
              <div class="icon-badge">📄</div>
              <h3>Documentation</h3>
              <p>Referral letters or endorsements required by partner agencies.</p>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <div id="assistToast" class="toast">✅ Assistance request submitted. A representative will contact you shortly. (Demo)</div>

  <script>
    function submitAssistForm(e){
      e.preventDefault();
      const toast = document.getElementById('assistToast');
      toast.style.display = 'block';
      setTimeout(()=> toast.style.display='none', 3500);
      // Clear form (demo-only)
      document.getElementById('assistForm').reset();
      return false;
    }

    // Make cards selectable to prefill type and scroll to form
    document.addEventListener('DOMContentLoaded', function(){
      const cards = document.querySelectorAll('.assist-card[data-type]');
      const select = document.getElementById('assistanceType');
      const form = document.getElementById('assistForm');
      cards.forEach(card => {
        const activate = () => {
          cards.forEach(c => c.setAttribute('aria-selected','false'));
          card.setAttribute('aria-selected','true');
          if (select) { select.value = card.getAttribute('data-type'); }
          if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };
        card.addEventListener('click', activate);
        card.addEventListener('keydown', (ev)=>{ if(ev.key==='Enter' || ev.key===' '){ ev.preventDefault(); activate(); }});
      });

      // Initialize age if birthdate present
      calculateAge();
    });

    function calculateAge(){
      const b = document.getElementById('birthdate');
      const a = document.getElementById('age');
      if (!b || !a || !b.value) { return; }
      const birth = new Date(b.value);
      if (isNaN(birth)) { a.value=''; return; }
      const today = new Date();
      let age = today.getFullYear() - birth.getFullYear();
      const m = today.getMonth() - birth.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) { age--; }
      a.value = age;
    }
  </script>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
