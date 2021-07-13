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

$gs = new GhostscriptCommands();
$gs->setGsPath($gsPath);

$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/001 SDI Iridesse Ink Swatches.pdf';
$reportOutput = __DIR__ . '/../../tests/Graphics/SampleFiles/001 SDI Iridesse Ink Swatches.ghostscript_report.json';
$fileTmpOutput = __DIR__ . '/../../tmp/';
$pdfReport = $gs->getPdfReport($pdfInput, true, true);
r($pdfReport);

$pdfReportSeps = $gs->getPageSeparationsReport($pdfInput, true, true);
r($pdfReportSeps);

$pageSizeGroupsReport = $gs->getPageSizeGroupsReport($pdfInput, true, true);
r($pageSizeGroupsReport);

$callasReport = $gs->getCallasReport($pdfInput, true, $reportOutput);
r($callasReport);


$ripOptions = [
    'format' => 'tiff',
    'colorspace' => 'tiffsep',
    //'format' => 'png',
    //'colorspace' => 'colour',
    'resolution' => '512x512',
    //'resolution' => '300',
    'smoothing' => false,
    //'pagebox' => 'TrimBox',
    //'pagelist' => [1,2],
    //'pagelist' => '3,8',
    //'outputfolder' => pathinfo($pdfInput, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . "meta" . DIRECTORY_SEPARATOR,
    'outputfolder' => $fileTmpOutput,
];
$images = $gs->savePdfAsImages($pdfInput, $ripOptions);
r($images);
