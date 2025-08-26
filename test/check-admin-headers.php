<?php
echo "🔍 Finding admin files that include user header...\n";
echo str_repeat("=", 50) . "\n";

$admin_dir = '../admin/';
$files_with_header = [];
$files_to_check = glob($admin_dir . '*.php');

foreach ($files_to_check as $file) {
    $content = file_get_contents($file);
    
    // Check for user header includes
    if (preg_match('/include\s+[\'"]\.\.\/includes\/header\.php[\'"]|require\s+[\'"]\.\.\/includes\/header\.php[\'"]/', $content)) {
        $files_with_header[] = basename($file);
        echo "❌ " . basename($file) . " includes user header\n";
    } else {
        echo "✅ " . basename($file) . " does not include user header\n";
    }
}

if (empty($files_with_header)) {
    echo "\n🎉 All admin files are properly configured!\n";
} else {
    echo "\n⚠️  Files that need fixing:\n";
    foreach ($files_with_header as $file) {
        echo "   - $file\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
?>