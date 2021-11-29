<?php

require __DIR__ . '/../../vendor/autoload.php';

use arajcany\PrePressTricks\Graphics\Ghostscript\GhostscriptCommands;

$command = "where gswin64c";
$output = [];
$return_var = '';
exec($command, $output, $return_var);
if (isset($output[0])) {
    if (is_file($output[0])) {
        $gsPath = $output[0];
    } else {
        $gsPath = false;
    }
} else {
    $gsPath = false;
}

$cls = new GhostscriptCommands();
$cls->setGsPath($gsPath);


$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/001 SDI Iridesse Ink Swatches.pdf';
$pdfReport = $cls->getQuickCheckReport($pdfInput, false, true);
//print_r($pdfReport);

