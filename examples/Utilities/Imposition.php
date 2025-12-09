<?php

require __DIR__ . '/../../vendor/autoload.php';

use arajcany\PrePressTricks\Utilities\Imposition;

$Impo = new Imposition();
?>

<?php
$options = [
    'sheet_width' => 450,
    'sheet_height' => 320,
    'page_width' => 210,
    'page_height' => 297,
    'gutters_horizontal' => 5,
    'gutters_vertical' => 5,
    'margin_top' => 0,
    'margin_bottom' => 0,
    'margin_left' => 0,
    'margin_right' => 0,
    'auto_rotation' => 'sheet', // false|sheet|page
];

$colsAndRows = $Impo->calculateColumnsAndRows($options);
//print_r($colsAndRows);


$impositionOptions = [
    'plex' => 2,
    'pp' => 8,
    'qty' => 2,
    'mode' => 'sequential',
];
$impositionOptions = array_merge($colsAndRows, $impositionOptions);
$sheets = $Impo->calculateTotalSheets($impositionOptions);
//print_r($sheets);


$stockProperties = [
    'width_mm' => 450,
    'height_mm' => 320,
    'gsm' => 120,
    'sheets_per_ream' => 250,
    'ream_depth_mm' => 30,
    'ream_weight_kg' => 2,
    'reams_per_box' => 5,
];
$packagingData = $Impo->calculatePackingData($stockProperties, $impositionOptions);
print_r($packagingData);