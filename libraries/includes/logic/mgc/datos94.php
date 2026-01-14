<?php
require('pdo-wrapper.php');

use CloudDB\DB\Database;

$flgServer = 1;

if ($flgServer == 1) {
    $options = [
        'host' => "localhost",
        'database' => "u214874994_alina_cloud",
        'username' => "u214874994_alina_cloud",
        'password' => '4=KRSh:;#1Sh'
    ];
} else {
    $options = [
        'host' => "localhost",
        'database' => "seam_global",
        'username' => "root",
        'password' => ""
    ];
}

$cloud = new Database($options);
?>