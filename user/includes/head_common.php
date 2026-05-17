<?php
/**
 * Shared head assets for authenticated user portal pages.
 * Set $page_title before including. Optional: $extra_css = ['css/foo.css']
 */
$page_title = $page_title ?? 'Portal';
$extra_css = $extra_css ?? [];
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?php echo htmlspecialchars($page_title); ?> - Barangay Gumaoc East</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/background.css">
<link rel="stylesheet" href="css/mobile.css">
<?php foreach ($extra_css as $href): ?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($href); ?>">
<?php endforeach; ?>
