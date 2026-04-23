<?php
require __DIR__ . '/../../vendor/autoload.php';



use arajcany\PrePressTricks\Graphics\FreeFlowCore\FreeFlowCoreConfig5;

$ffcc5 = new FreeFlowCoreConfig5();

//print_r($ffcc5->findAppConfigFiles());
print_r($ffcc5->getTenantHotFolders());