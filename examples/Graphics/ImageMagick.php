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

$file = __DIR__ . '/../../tests/Graphics/SampleFiles/000 SDI Base Test Pages-1(Gold).tif';
$file = __DIR__ . '/../../tests/Graphics/SampleFiles/000 SDI Base Test Pages-2(Gold).tif';
$file = __DIR__ . '/../../tests/Graphics/SampleFiles/000 SDI Base Test Pages-3(Gold).tif';
$file = __DIR__ . '/../../tests/Graphics/SampleFiles/000 SDI Base Test Pages-4(Gold).tif';

$report = $imCommands->getIdentifyReportViaCli($file, true, true);
r($report);

$reportFormatted = (new IdentifyParser())->parse($report)->toArray();
r($reportFormatted);

$reportHistogram = $imCommands->getHistogramJson($file, false, false);
r($reportHistogram);
