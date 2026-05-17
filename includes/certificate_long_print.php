<?php
/**
 * Shared helpers for A4 portrait barangay certificate print pages.
 * Asset paths assume scripts under /admin/ or /pages/ (../assets).
 */

declare(strict_types=1);

/** Web path prefix from pages/print-*.php */
function clp_asset_base(): string
{
    return '../assets';
}

function clp_stylesheet_link(): string
{
    $base = htmlspecialchars(clp_asset_base(), ENT_QUOTES, 'UTF-8');
    return '<link rel="stylesheet" href="' . $base . '/css/certificate-long-print.css">';
}

/**
 * @param string $browserTitle <title> text
 */
function clp_html_head(string $browserTitle): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($browserTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php echo clp_stylesheet_link(); ?>
</head>
<body>
    <?php
}

function clp_print_controls(string $backHref, string $metaHtml = '', bool $embed = false): void
{
    if ($embed) {
        return;
    }
    ?>
    <div class="no-print print-controls">
        <button type="button" class="print-btn" onclick="window.print()">Print</button>
        <a href="<?php echo htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8'); ?>" class="back-btn">← Back</a>
        <?php if ($metaHtml !== '') { ?>
            <p style="margin-top:10px;color:#666;font-size:13px;"><?php echo $metaHtml; ?></p>
        <?php } ?>
    </div>
    <?php
}

function clp_open_page(): void
{
    ?>
    <div class="cert-page">
        <div class="cert-inner">
    <?php
}

/** Letterhead (seals, waves, watermark) comes from assets/images/forms/barangay-letterhead-bg.png — no duplicate HTML header. */
function clp_header(): void
{
}

/**
 * @param string|string[] $title Single line or two lines for tricycle-style title
 */
function clp_doc_title($title): void
{
    if (is_array($title)) {
        $a = htmlspecialchars($title[0] ?? '', ENT_QUOTES, 'UTF-8');
        $b = htmlspecialchars($title[1] ?? '', ENT_QUOTES, 'UTF-8');
        echo '<h1 class="cert-doc-title cert-doc-title--twoline"><span>' . $a . '</span><span>' . $b . '</span></h1>';
        return;
    }
    echo '<h1 class="cert-doc-title">' . htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') . '</h1>';
}

function clp_open_body(): void
{
    echo '<div class="cert-body">';
}

function clp_close_body(): void
{
    echo '</div>';
}

function clp_close_page(): void
{
    ?>
        </div>
    </div>
    <?php
}

function clp_html_foot(bool $autoPrint = false): void
{
    if ($autoPrint) {
        ?>
<script>
(function () {
  var params = new URLSearchParams(window.location.search);
  if (params.get('auto') === '1') {
    setTimeout(function () {
      window.print();
      window.addEventListener('afterprint', function () { window.close(); });
    }, 120);
  }
})();
</script>
        <?php
    }
    ?>
</body>
</html>
    <?php
}

/**
 * Standard CTC / OR / Prepared by block (left column).
 * "NOT VALID WITHOUT DRY SEAL" is shown once per page (centered), not beside ISSUED ON.
 */
function clp_admin_block(): void
{
    $issued = strtoupper(date('d F Y'));
    $at = 'BRGY. GUMAOC EAST, CSJDM, BULACAN';
    ?>
    <div class="cert-admin-lines">
        <div class="cert-admin-row">
            <span class="k">CTC NO</span>
            <span class="colon">:</span>
            <span class="v line">&nbsp;</span>
        </div>
        <div class="cert-admin-row">
            <span class="k">ISSUED AT</span>
            <span class="colon">:</span>
            <span class="v"><?php echo htmlspecialchars($at, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="cert-admin-row cert-admin-row--issued">
            <span class="k">ISSUED ON</span>
            <span class="colon">:</span>
            <span class="v"><?php echo htmlspecialchars($issued, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="cert-admin-row">
            <span class="k">O.R. NO.</span>
            <span class="colon">:</span>
            <span class="v line">&nbsp;</span>
        </div>
        <div class="cert-admin-row">
            <span class="k">PREPARED BY</span>
            <span class="colon">:</span>
            <span class="v line">&nbsp;</span>
        </div>
    </div>
    <?php
}

function clp_verified_officer_block(string $officerName = 'HON. MARITESS O. SY'): void
{
    ?>
    <div class="cert-verified">
        <div class="lbl">Verified by</div>
        <div class="name"><?php echo htmlspecialchars($officerName, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="role">Officer of the Day</div>
    </div>
    <?php
}

function clp_dates_block(string $givenLabel = 'Given this:', string $validLabel = 'Valid until:', ?string $givenDate = null, ?string $validDate = null): void
{
    $g = $givenDate ?? strtoupper(date('d F Y'));
    $v = $validDate ?? strtoupper(date('d F Y', strtotime('+1 year')));
    ?>
    <div class="cert-dates">
        <div><strong><?php echo htmlspecialchars($givenLabel, ENT_QUOTES, 'UTF-8'); ?></strong> <?php echo htmlspecialchars($g, ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong><?php echo htmlspecialchars($validLabel, ENT_QUOTES, 'UTF-8'); ?></strong> <?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <?php
}

function clp_applicant_signature_block(): void
{
    ?>
    <div class="cert-sig-applicant">
        <div class="cert-sig-line"></div>
        <div class="cert-sig-label">Signature of Applicant</div>
    </div>
    <?php
}

function clp_thumb_marks_block(): void
{
    ?>
    <div class="cert-thumbs">
        <div class="cert-thumb-cell">
            <div class="cert-thumb-box">LEFT<br>THUMB<br>MARK</div>
        </div>
        <div class="cert-thumb-cell">
            <div class="cert-thumb-box">RIGHT<br>THUMB<br>MARK</div>
        </div>
    </div>
    <?php
}

function clp_punong_barangay_block(string $name = 'HON. ROMMEL B. PITALBO'): void
{
    ?>
    <div class="cert-sig-official">
        <div class="name"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="cert-sig-line" style="width:2.6in;"></div>
        <div class="title">Punong Barangay</div>
    </div>
    <?php
}
