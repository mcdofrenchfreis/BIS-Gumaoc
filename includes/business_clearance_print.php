<?php

declare(strict_types=1);

/**
 * Shared helpers for A4 portrait business clearance print pages.
 */

function bcp_stylesheet_link(): string
{
    $base = htmlspecialchars('../assets', ENT_QUOTES, 'UTF-8');
    return '<link rel="stylesheet" href="' . $base . '/css/business-clearance-print.css">';
}

function bcp_html_head(string $browserTitle): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($browserTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php echo bcp_stylesheet_link(); ?>
</head>
<body>
    <?php
}

function bcp_print_controls(string $backHref, bool $embed = false): void
{
    if ($embed) {
        return;
    }
    ?>
    <div class="no-print bcp-print-controls">
        <button type="button" class="bcp-print-btn" onclick="window.print()">Print</button>
        <a href="<?php echo htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8'); ?>" class="bcp-back-btn">← Back</a>
    </div>
    <?php
}

function bcp_open_page(): void
{
    ?>
    <div class="bcp-page">
        <div class="bcp-inner">
    <?php
}

function bcp_close_page(): void
{
    ?>
        </div>
    </div>
    <?php
}

function bcp_html_foot(): void
{
    ?>
</body>
</html>
    <?php
}
