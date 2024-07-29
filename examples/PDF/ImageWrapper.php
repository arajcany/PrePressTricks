<?php
/**
 * https://github.com/tecnickcom/tc-lib-pdf/blob/main/examples/index.php
 * https://github.com/tecnickcom/TCPDF/blob/5fce932fcee4371865314ab7f6c0d85423c5c7ce/examples/example_060.php#L79
 *
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use arajcany\PrePressTricks\Graphics\PDF\ImageWrapper;

require __DIR__ . '/../../vendor/autoload.php';


$images = [
    __DIR__ . '\\..\\..\\tests\\Graphics\\Images\\tomas-malik-yeLp8cX1BP4-unsplash.jpg',
    __DIR__ . '\\..\\..\\tests\\Graphics\\Images\\alex-tyson-xiiX89sNvkc-unsplash.jpg',
];

foreach ($images as $image) {
    $IW = new ImageWrapper();
    $imageProperties = [
        'format' => 'jpg',      //(not working yet) jpg of tif
        'quality' => 100,       //0 = min quality, 100 = max quality when using lossy compression
        'anchor' => 5,          //(not working yet) int 1-9. Corresponds to an anchor point based on keyboard numberpad (e.g. 7 = top-left)
        'fitting' => 'fill',    //fit, fill, stretch
        'resolution' => '@',    //int=desired resolution || null=any resolution || @=adaptive resolution
        'clipping' => false,    //clip portions of the image outside the bleed
    ];
    $pageProperties = [
        'unit' => 'mm',
        'page_width' => '600',
        //'page_height' => '600',
        'crop_length' => 5,
        'crop_offset' => 5,
        'bleed' => 5,
        'slug' => 100,
        'info' => true,
    ];
    $pdfSavePath = $image . ".pdf";
    $pdf = $IW->wrapImage($image, $imageProperties, $pageProperties);
    $result = $IW->savePdf($pdf, $pdfSavePath);
    dump($IW->getAllAlertsLogSequence());
}
