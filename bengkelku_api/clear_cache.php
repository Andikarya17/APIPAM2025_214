<?php
/**
 * Clear OPCache - Run this file in browser then delete it
 */

// Clear OPCache if available
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "OPCache Reset: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
} else {
    echo "OPCache not available<br>";
}

// Also try invalidating specific files
$files = [
    __DIR__ . '/booking/create.php',
    __DIR__ . '/booking/list_customer.php',
    __DIR__ . '/booking/list_admin.php',
    __DIR__ . '/booking/update_status.php'
];

echo "<br>Invalidating files:<br>";
foreach ($files as $file) {
    if (file_exists($file)) {
        if (function_exists('opcache_invalidate')) {
            $result = opcache_invalidate($file, true);
            echo $file . ": " . ($result ? "Invalidated" : "Already clear") . "<br>";
        }
    } else {
        echo $file . ": NOT FOUND<br>";
    }
}

echo "<br><strong>DONE! Now delete this file and test again.</strong>";
echo "<br><br>To verify, open: <a href='booking/create.php'>booking/create.php</a>";
