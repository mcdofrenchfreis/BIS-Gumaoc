<?php
// admin/update_resident_summary_ui.php
// Patcher to replicate UI from get-certificate-summary.php to resident-summary.php

$file = __DIR__ . '/resident-summary.php';
$backup = __DIR__ . '/resident-summary.php.bak';

function out($msg){ echo $msg, "\n"; }

if (!file_exists($file)) {
    http_response_code(404);
    out('ERROR: resident-summary.php not found.');
    exit(1);
}

$orig = file_get_contents($file);
if ($orig === false) { out('ERROR: Unable to read file.'); exit(1); }

// Backup once per run (overwrite existing .bak)
if (!copy($file, $backup)) { out('WARNING: Failed to create backup. Continuing...'); } else { out('Backup created: ' . basename($backup)); }

$updated = $orig;
$changes = [];

function replace_block(&$content, $label, $pattern, $replacement) {
    $count = 0;
    $content = preg_replace($pattern, $replacement, $content, 1, $count);
    return [$label, $count];
}

// 1) .summary-header CSS
list($lbl, $cnt) = replace_block(
    $updated,
    '.summary-header CSS',
    '~(^\s*\.summary-header\s*\{).*?(^\s*\})~ms',
    "        .summary-header {\n            background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);\n            color: #fff;\n            padding: 1.5rem 2rem;\n            border-radius: 12px;\n            margin-bottom: 2rem;\n            display: grid;\n            grid-template-columns: auto 1fr auto;\n            align-items: center;\n            gap: 1rem 1.25rem;\n            box-shadow: 0 8px 24px rgba(0,0,0,0.12);\n        }"
);
$changes[] = [$lbl, $cnt];

// 2) .info-grid CSS
$changes[] = replace_block(
    $updated,
    '.info-grid CSS',
    '~(^\s*\.info-grid\s*\{).*?(^\s*\})~ms',
    "        .info-grid {\n            display: grid;\n            grid-template-columns: 1fr;\n            column-gap: 1rem;\n            row-gap: 0.45rem;\n            grid-auto-flow: row dense;\n            margin-bottom: 0.5rem;\n        }"
);

// 3) .info-item CSS
$changes[] = replace_block(
    $updated,
    '.info-item CSS',
    '~(^\s*\.info-item\s*\{).*?(^\s*\})~ms',
    "        .info-item { margin: 0; }\n        .info-grid > .info-item:not(:first-child) { margin-top: 0.15rem; }\n        .info-item.full-row { grid-column: 1 / -1; display: block; }"
);

// 4) .info-label CSS
$changes[] = replace_block(
    $updated,
    '.info-label CSS',
    '~(^\s*\.info-label\s*\{).*?(^\s*\})~ms',
    "        .info-label {\n            font-weight: 600;\n            color: #495057;\n            margin: 0;\n            font-size: 0.9rem;\n        }"
);

// 5) .info-value CSS
$changes[] = replace_block(
    $updated,
    '.info-value CSS',
    '~(^\s*\.info-value\s*\{).*?(^\s*\})~ms',
    "        .info-value {\n            font-size: 1rem;\n            color: #212529;\n            padding: 0.35rem 0.5rem;\n            background: #f8f9fa;\n            border-radius: 6px;\n            border-left: 3px solid #4CAF50;\n            width: 100%;\n            box-sizing: border-box;\n            display: block;\n            margin: 0;\n        }\n        .info-item.full-row .info-label { margin: 0 0 2px; }\n        .info-item.full-row .info-value { width: 100%; margin-top: 0; }"
);

// 6) .print-btn CSS
$changes[] = replace_block(
    $updated,
    '.print-btn CSS',
    '~(^\s*\.print-btn\s*\{).*?(^\s*\})~ms',
    "        .print-btn { background: linear-gradient(135deg, #4CAF50, #45a049); color:#fff; border:none; padding:0.6rem 1rem; border-radius:8px; cursor:pointer; font-weight:600; }"
);

// 7) .back-btn + :hover CSS (replace both together if present)
$updated = preg_replace(
    '~(^\s*\.back-btn\s*\{).*?(^\s*\})\s*(^\s*\.back-btn:hover\s*\{).*?(^\s*\})~ms',
    "        .back-btn {\n            background: #ffffff;\n            color: #2e7d32;\n            text-decoration: none;\n            padding: 0.45rem 0.85rem;\n            border-radius: 8px;\n            font-weight: 700;\n            display: inline-flex;\n            align-items: center;\n            gap: 0.4rem;\n            box-shadow: 0 2px 10px rgba(0,0,0,0.12);\n            transition: transform 0.08s ease, box-shadow 0.2s ease;\n        }\n        .back-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,0.16); }",
    $updated,
    -1,
    $countBack
);
$changes[] = ['.back-btn block', $countBack];

// 8) Ensure title-block/requester CSS exists
if (strpos($updated, '.title-block h1') === false) {
    $insertion = "\n        .title-block h1 {\n            margin: 0 0 0.25rem 0;\n            font-size: 1.4rem;\n            line-height: 1.2;\n            display: flex;\n            align-items: center;\n            gap: 0.5rem;\n            text-shadow: 0 1px 1px rgba(0,0,0,0.2);\n        }\n        .title-block p { margin: 0; opacity: 0.95; font-size: 0.95rem; }\n        .requester { display:flex; align-items:center; gap:0.5rem; white-space:nowrap; }\n        .requester h2 { margin:0; font-size:1.15rem; font-weight:700; text-shadow:0 1px 1px rgba(0,0,0,0.2); }\n";
    $updated = preg_replace('~</style>~i', $insertion . "    </style>", $updated, 1, $insCount);
    $changes[] = ['insert title-block/requester CSS', $insCount];
}

// 9) Mobile header tweak: ensure grid-template-columns inside mobile .summary-header
$updated = preg_replace(
    '~(@media\s*\(max-width:\s*768px\)\s*\{[\s\S]*?\.summary-header\s*\{[\s\S]*?padding:\s*1rem;)(?![\s\S]*?grid-template-columns)~i',
    "$1\n                grid-template-columns: 1fr;",
    $updated,
    1,
    $mobCount
);
$changes[] = ['mobile header grid tweak', $mobCount];

// 10) Header HTML replacement
$oldHeaderPattern = '~<div class=\"summary-header\">\s*<div>.*?</div>\s*<h2>.*?</h2>\s*</div>~s';
$newHeader = <<<HTML
        <div class="summary-header">
            <a href="view-resident-registrations.php" class="back-btn" onclick="if (window.history.length > 1) { history.back(); return false; } return true;">← Back</a>
            <div class="title-block">
                <h1>📋 Registration Summary</h1>
                <p>
                    <strong>ID:</strong> #<?php echo \$registration_data['id']; ?>
                    &nbsp;|&nbsp;
                    <strong>Submitted:</strong> <?php echo date('F j, Y \\a\\t g:i A', strtotime(\$registration_data['submitted_at'])); ?>
                </p>
            </div>
            <div class="requester">
                <h2><?php echo htmlspecialchars(\$registration_data['first_name'] . ' ' . \$registration_data['last_name']); ?></h2>
                <span class="badge status-<?php echo \$registration_data['status']; ?>"><?php echo ucfirst(\$registration_data['status']); ?></span>
            </div>
        </div>
HTML;
$updated = preg_replace($oldHeaderPattern, $newHeader, $updated, 1, $hdrCount);
$changes[] = ['header HTML replaced', $hdrCount];

if ($updated !== $orig) {
    file_put_contents($file, $updated);
    out('resident-summary.php updated.');
} else {
    out('No changes applied (content already up to date or patterns not found).');
}

// Report
foreach ($changes as $c) {
    out(sprintf('%s: %d', $c[0], (int)$c[1]));
}

?>
