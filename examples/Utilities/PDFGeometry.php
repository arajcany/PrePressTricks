<?php

require __DIR__ . '/../../vendor/autoload.php';

use arajcany\PrePressTricks\Utilities\PDFGeometry;

$pdfGeometry = new PDFGeometry();
?>

<?php
$boxes = [
    'BleedBox[8.503937 18.108204 867.76184 630.6161]',
    'CropBox[0.0 9.604266 876.26575 639.12]',
    'MediaBox[0.0 9.604266 876.26575 639.12]',
    'TrimBox[17.007874 26.61214 859.25793 622.1122]',
];
foreach ($boxes as $box) {
    $geo = $pdfGeometry->parseGeometry($box);
    //print_r($geo);
}

//$box = [0, 0, 210, 297];
//$rotation = 90;
//$geo = $pdfGeometry->getAnchorTopLeft($box, $rotation, 1);
//print_r($geo);
//$geo = $pdfGeometry->getAnchorTopCenter($box, $rotation, 1);
//print_r($geo);
//$geo = $pdfGeometry->getAnchorTopRight($box, $rotation, 1);
//print_r($geo);
//$geo = $pdfGeometry->getAnchorLeftCenter($box, $rotation, 1);
//print_r($geo);
//$geo = $pdfGeometry->getAnchorCenter($box, $rotation, 1);
//print_r($geo);
//$geo = $pdfGeometry->getAnchorRightCenter($box, $rotation, 1);
//print_r($geo);
//$geo = $pdfGeometry->getAnchorBottomLeft($box, $rotation, 1);
//print_r($geo);
//$geo = $pdfGeometry->getAnchorBottomCenter($box, $rotation, 1);
//print_r($geo);
//$geo = $pdfGeometry->getAnchorBottomRight($box, $rotation, 1);
//print_r($geo);


//$box = [0, 0, 297, 210];
$box = [7, 10, 297, 210];
$geo = $pdfGeometry->getEffectiveGeometry($box, 0, 1);
print_r($geo);

//$geo = $pdfGeometry->getEffectiveGeometry($box, 90, 1);
////print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 180, 1);
////print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 270, 1);
////print_r($geo);


//$box = [10, 20, 307, 230];
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 0, 1);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 90, 1);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 180, 1);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 270, 1);
//print_r($geo);


//$box = [40, 30, 160, 277];
//$boundingBox = [0, 0, 210, 297];
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 0, 1, $boundingBox);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 90, 1, $boundingBox);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 180, 1, $boundingBox);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 270, 1, $boundingBox);
//print_r($geo);


//$box = [70, -17, (70 + 120), (-17 + 247)];
//$boundingBox = [30, -47, (30 + 210), (-47 + 297)];
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 0, 1, $boundingBox);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 90, 1, $boundingBox);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 180, 1, $boundingBox);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 270, 1, $boundingBox);
//print_r($geo);


//$x = 10;
//$y = 15;
//$box = [105 + $x, 148.5 + $y, 210 + $x, 297 + $y];
//$boundingBox = [0 + $x, 0 + $y, 210 + $x, 297 + $y];

//$geo = $pdfGeometry->getEffectiveGeometry($box, 0, 1, $boundingBox);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 90, 1, $boundingBox);
//print_r($geo);
//
//$geo = $pdfGeometry->getEffectiveGeometry($box, 180, 1, $boundingBox);
//print_r($geo);

//$geo = $pdfGeometry->getEffectiveGeometry($box, 270, 1, $boundingBox);
//print_r($geo);


//print_r($pdfGeometry->convertUnit(1, 'in', 'mm'));