<?php

require __DIR__ . '/../../vendor/autoload.php';

use arajcany\PrePressTricks\Graphics\ImageMagick\IdentifyParser;
use arajcany\PrePressTricks\Graphics\ImageMagick\ImageMagickCommands;


$command = "where magick";
$output = [];
$return_var = '';
exec($command, $output, $return_var);
if (isset($output[0])) {
    if (is_file($output[0])) {
        $imPath = $output[0];
    } else {
        $imPath = false;
    }
} else {
    $imPath = false;
}

$imCommands = new ImageMagickCommands();
$imCommands->setImPath($imPath);

$pdf = __DIR__ . '/../../tests/Graphics/SampleFiles/000 SDI Base Test Pages.pdf';
$ripOptions = [
    'resolution' => '2',
];
$report = $imCommands->analyseSpecialtyDryInks($pdf, false, false, $ripOptions);
r($report);