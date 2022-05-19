<?php

use arajcany\PrePressTricks\Utilities\Boxes;

$boxes = new Boxes();
?>

<?php
$codeSnippet = getFileLine(__FILE__, __LINE__ + 1, 5);
$imageWidth = 200;
$imageHeight = 600;
$boxWidth = 256;
$boxHeight = 256;
$newImageSize = $boxes->fitIntoBox($imageWidth, $imageHeight, $boxWidth, $boxHeight);
dump($codeSnippet, $newImageSize);

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1, 5);
$imageWidth = 200;
$imageHeight = 600;
$boxWidth = 256;
$boxHeight = 256;
$newImageSize = $boxes->fillIntoBox($imageWidth, $imageHeight, $boxWidth, $boxHeight);
dump($codeSnippet, $newImageSize);
