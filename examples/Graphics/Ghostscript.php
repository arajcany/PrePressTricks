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


//------------------------------------------------------------
// Examples of every type of report that can be output
//------------------------------------------------------------

$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/001 SDI Iridesse Ink Swatches.pdf';
$pdfReport = $cls->getPdfReport($pdfInput, true, true);

$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/001 SDI Iridesse Ink Swatches.pdf';
$pdfReport = $cls->getQuickCheckReport($pdfInput, true, true);

$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/001 SDI Iridesse Ink Swatches.pdf';
$pdfReport = $cls->getPageSizeGroupsReport($pdfInput, true, true);

$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/001 SDI Iridesse Ink Swatches.pdf';
$pdfReport = $cls->getPageSeparationsReport($pdfInput, true, true);

//rip pages as images
$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/001 SDI Iridesse Ink Swatches.pdf';
$pdfFolderOutput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/Thumbs/';
$ripOptions = [
    'format' => 'png',
    'colorspace' => 'rbg',
    'quality' => '100',
    'resolution' => '72', //could also be in format NxN where image will fit into box NxN
    'smoothing' => false,
    'pagebox' => 'media',
    'pagelist' => [1, 3, '7 - 8'],
    'outputfolder' => $pdfFolderOutput,
];
//$images contains an array of paths
$images = $cls->savePdfAsImages($pdfInput, $ripOptions);
print_r($images);

//rip pages as separations
$pdfInput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/001 SDI Iridesse Ink Swatches.pdf';
$pdfFolderOutput = __DIR__ . '/../../tests/Graphics/SampleFiles/Ghostscript/Seps/';
$ripOptions = [
    'format' => 'png',
    'colorspace' => 'rgb',
    'quality' => '100',
    'resolution' => '500x500', //could also be in format NxN where image will fit into box NxN
    'smoothing' => false,
    'pagebox' => 'media',
    'pagelist' => [1, 3, '7 - 8'],
    'outputfolder' => $pdfFolderOutput,
];
//$images contains an array of paths
$images = $cls->savePdfAsSeparations($pdfInput, $ripOptions);
print_r($images);