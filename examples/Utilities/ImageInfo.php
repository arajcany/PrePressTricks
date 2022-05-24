<?php

use Intervention\Image\ImageManager;

require __DIR__ . '/../../vendor/autoload.php';

use arajcany\PrePressTricks\Utilities\ImageInfo;

$imageGeometry = new ImageInfo();

$image = "C:\\Users\\arajcany\\Pictures\\DSCF4617.JPG";
$image = "C:\\Users\\arajcany\\Pictures\\DSCF4617.RAF";
$image = "C:\\Users\\arajcany\\Pictures\\DSCF4618.JPG";
$image = "C:\\Users\\arajcany\\Pictures\\DSCF4618.RAF";
$meta = $imageGeometry->getImageMeta($image);
//dump($meta);
//print_r($meta);


