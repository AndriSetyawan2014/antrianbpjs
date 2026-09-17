<?php

$files = [
    'vendor/nullpunkt/lz-string-php/src/LZCompressor/LZUtil.php',
    'vendor/nullpunkt/lz-string-php/src/LZCompressor/LZUtil16.php',
    'vendor/nullpunkt/lz-string-php/src/LZCompressor/LZContext.php',
    'vendor/nullpunkt/lz-string-php/src/LZCompressor/LZData.php',
    'vendor/nullpunkt/lz-string-php/src/LZCompressor/LZReverseDictionary.php',
    'vendor/nullpunkt/lz-string-php/src/LZCompressor/LZString.php'
];

$output = "<?php\n\n";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Remove <?php tags
        $content = str_replace('<?php', '', $content);
        $output .= $content . "\n\n";
    }
}

file_put_contents('app/Helpers/LZStringStandalone.php', $output);
echo 'Generated app/Helpers/LZStringStandalone.php successfully!';
