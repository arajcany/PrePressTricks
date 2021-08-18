<?php

use arajcany\PrePressTricks\Utilities\Pages;

$pages = new Pages();
?>

<?php
drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$result = $pages->rangeExpand('3-5,10-15', ['returnFormat' => 'string']);
r($codeSnippet, $result);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$result = $pages->rangeExpand('3-5,10-15', ['returnFormat' => 'array']);
r($codeSnippet, $result);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1) . "\r\n" . getFileLine(__FILE__, __LINE__ + 2);
$options = ['returnFormat' => 'string', 'duplicateStringSingles' => false];
$result = $pages->rangeCompact('3,4,6,12,13,10-20,1,2,8', $options);
r($codeSnippet, $result);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1) . "\r\n" . getFileLine(__FILE__, __LINE__ + 2);
$options = ['returnFormat' => 'string', 'duplicateStringSingles' => true];
$result = $pages->rangeCompact('3,4,6,12,13,10-20,1,2,8', $options);
r($codeSnippet, $result);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1) . "\r\n" . getFileLine(__FILE__, __LINE__ + 2);
$options = ['returnFormat' => 'array', 'duplicateStringSingles' => false];
$result = $pages->rangeCompact('3,4,6,12,13,10-20,1,2,8', $options);
r($codeSnippet, $result);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$result = $pages->rangeFlip('3-4,10-20');
r($codeSnippet, $result);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$result = $pages->rangeFlip('3-4,10-20', 1, 24);
r($codeSnippet, $result);

drawLine();

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$fileNames = ['file_9_bar_02.png', 'file_0_a_002.png', 'file_9_bar_12.png', 'file_0_a_001.png', 'file_9_bar_04.png', 'unrelated_file_001.png', 'file_0_a_003.png', 'file_0_a_004.png', 'file_9_bar_05.png',];
$result = $pages->groupByPageSequences($fileNames);
r($codeSnippet, $result);

drawLine();
