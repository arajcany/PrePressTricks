<?php

use Intervention\Image\ImageManager;

require __DIR__ . '/../../vendor/autoload.php';

use arajcany\PrePressTricks\Utilities\ImageInfo;

$imageGeometry = new ImageInfo();

$image = "W:\\arajcany_Projects\\PhotoPackageAdapter\\tests\\InDesignMaterial\\PeopleShots1.jpg";
$image = "c:\\tmp\\cow-army-2000.jpg";
$meta = $imageGeometry->getImageMeta($image);
dump($meta);

////clone image
//$rotations = 64;
//$image = "c:\\tmp\\cow-army-2000.jpg";
//$imageSaved = "c:\\tmp\\cow-army-2000-rotated-{$rotations}.jpg";
//copy($image, $imageSaved);
//
//// create image manager with desired driver
//$manager = new ImageManager(['driver' => 'imagick']);
//foreach (range(1, $rotations) as $count) {
//    // read image from file system
//    $imageLoaded = $manager->make($imageSaved);
//    //rotate the image
//    $imageLoaded->rotate(90);
//    // save modified image
//    $imageLoaded->save($imageSaved);
//    dump("Round {$count} Completed.");
//}

