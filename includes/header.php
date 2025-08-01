<?php
$page_title = $page_title ?? 'Barangay Gumaoc East E-Services System';
$page_description = $page_description ?? 'IoT-Enabled Incident Reporting & E-Services Information System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $page_title; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base_path; ?>css/styles.css">
</head>
<body>
  <div class="bg-image"></div>
  
  <header>
    <h1><?php echo $header_title ?? 'Barangay Gumaoc East'; ?></h1>
    <p><?php echo $header_subtitle ?? $page_description; ?></p>
  </header>
</body>
</html> 