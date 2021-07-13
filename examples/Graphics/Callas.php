<?php

require __DIR__ . '/../../vendor/autoload.php';

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
$cls->setCallasPath($callasPath);

$version = $cls->getCliVersion();
//r($version);

$help = $cls->getCliHelp();
//r($help);

$info = $cls->getCliStatus();
//r($info);


$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/001 SDI Iridesse Ink Swatches.pdf';
$reportOutput = __DIR__ . '/../../tests/Graphics/SampleFiles/001 SDI Iridesse Ink Swatches.callas_report.json';
$fileTmpOutput = __DIR__ . '/../../tmp/';


//$cls->removeCallasQuickCheckFilter("$.aggregated.pages");
//$cls->addCallasQuickCheckFilter("$.aggregated.resources", false);
//$cls->addCallasQuickCheckFilter("$.aggregated.resources.images.summary", true);
//$cls->insertCallasQuickCheckFilter("$.aggregated.resources.images.summary.bitmap_images.eff_highest_ppi", true);
//$cls->insertCallasQuickCheckFilter("$.aggregated.resources.images.summary.bitmap_images.eff_lowest_ppi", true);
//$cls->insertCallasQuickCheckFilter("$.aggregated.resources.images.summary.ct_images.eff_highest_ppi", true);
//$cls->insertCallasQuickCheckFilter("$.aggregated.resources.images.summary.ct_images.eff_lowest_ppi", true);
$pdfReport = $cls->getQuickCheckReport($pdfInput, false, $reportOutput);
r($pdfReport);

$pdfReportSeps = $cls->getPageSeparationsReport($pdfInput, false, true);
r($pdfReportSeps);

$pageSizeGroupsReport = $cls->getPageSizeGroupsReport($pdfInput, false, true);
r($pageSizeGroupsReport);
