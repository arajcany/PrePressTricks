<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/cakephp/i18n/functions_global.php';

use arajcany\PrePressTricks\Graphics\Callas\CallasCommands;

$command = "where pdftoolbox";
$output = [];
$return_var = '';
exec($command, $output, $return_var);
if (isset($output[0])) {
    if (is_file($output[0])) {
        $callasPath = $output[0];
    } else {
        $callasPath = false;
    }
} else {
    $callasPath = false;
}

$cls = new CallasCommands();


$cls->isAlive();
dd('DONE');
