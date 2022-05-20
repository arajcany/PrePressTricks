<?php

use arajcany\PrePressTricks\Utilities\Boxes;

$boxes = new Boxes();
?>

<?php
drawLine();

echo"";
$codeSnippet = getFileLine(__FILE__, __LINE__ + 1, 5);
$imageWidth = 200;
$imageHeight = 600;
$boxWidth = 256;
$boxHeight = 256;
$newImageSize = $boxes->fitIntoBox($imageWidth, $imageHeight, $boxWidth, $boxHeight);
dump($codeSnippet, $newImageSize);

drawLine();


$codeSnippet = getFileLine(__FILE__, __LINE__ + 1, 5);
$imageWidth = 200;
$imageHeight = 600;
$boxWidth = 256;
$boxHeight = 256;
$newImageSize = $boxes->fitIntoBoxFactor($imageWidth, $imageHeight, $boxWidth, $boxHeight);
dump($codeSnippet, $newImageSize);

drawLine();


$codeSnippet = getFileLine(__FILE__, __LINE__ + 1, 5);
$imageWidth = 200;
$imageHeight = 600;
$boxWidth = 256;
$boxHeight = 256;
$newImageSize = $boxes->fillIntoBox($imageWidth, $imageHeight, $boxWidth, $boxHeight);
dump($codeSnippet, $newImageSize);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1, 5);
$imageWidth = 200;
$imageHeight = 600;
$boxWidth = 256;
$boxHeight = 256;
$newImageSize = $boxes->fillIntoBoxFactor($imageWidth, $imageHeight, $boxWidth, $boxHeight);
dump($codeSnippet, $newImageSize);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1, 5);
$imageWidth = 100;
$imageHeight = 100;
$boxWidth = 200;
$boxHeight = 50;
$newImageSize = $boxes->stretchIntoBoxFactor($imageWidth, $imageHeight, $boxWidth, $boxHeight);
dump($codeSnippet, $newImageSize);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1, 5);
$imageWidth = 420;
$imageHeight = 297;
$boxWidth = 210;
$boxHeight = 297;
$newImageSize = $boxes->bestFitFactor($imageWidth, $imageHeight, $boxWidth, $boxHeight);
dump($codeSnippet, $newImageSize);

drawLine();
