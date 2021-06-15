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


$file = "M:\\GenericRepository_Test\\QuickPosition\\jobs\\15 - SDI Test Page\\meta\\SDI Iridesse Inks-8.tif";

$report = $imCommands->getIdentifyReport($file, true, true);
r($report);

$reportFormatted = (new IdentifyParser())->parse($report)->toArray();
r($reportFormatted);

$reportHistogram = $imCommands->getHistogramJson($file, false, true);
r($reportHistogram);
