<?php

use arajcany\PrePressTricks\Utilities\Pages;

$pages = new Pages();
?>

<?php
$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$range = $pages->rangeExpand('3-5,10-15', ['returnFormat' => 'string']);
dbg::print_r($codeSnippet, $range);

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$range = $pages->rangeExpand('3-5,10-15', ['returnFormat' => 'array']);
dbg::print_r($codeSnippet, $range);

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1) . "\r\n" . getFileLine(__FILE__, __LINE__ + 2);
$options = ['returnFormat' => 'string', 'duplicateStringSingles' => false];
$range = $pages->rangeCompact('3,4,6,12,13,10-20,1,2,8', $options);
dbg::print_r($codeSnippet, $range);

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1) . "\r\n" . getFileLine(__FILE__, __LINE__ + 2);
$options = ['returnFormat' => 'string', 'duplicateStringSingles' => true];
$range = $pages->rangeCompact('3,4,6,12,13,10-20,1,2,8', $options);
dbg::print_r($codeSnippet, $range);

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1) . "\r\n" . getFileLine(__FILE__, __LINE__ + 2);
$options = ['returnFormat' => 'array', 'duplicateStringSingles' => false];
$range = $pages->rangeCompact('3,4,6,12,13,10-20,1,2,8', $options);
dbg::print_r($codeSnippet, $range);

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$range = $pages->rangeFlip('3-4,10-20');
dbg::print_r($codeSnippet, $range);

$codeSnippet = getFileLine(__FILE__, __LINE__ + 1);
$range = $pages->rangeFlip('3-4,10-20', 1, 24);
dbg::print_r($codeSnippet, $range);
?>
