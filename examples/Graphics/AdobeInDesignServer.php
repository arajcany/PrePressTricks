<?php

require __DIR__ . '/../../vendor/autoload.php';

use arajcany\PrePressTricks\Graphics\Adobe\InDesignServer;

$idn = new InDesignServer();

$mode = 'fluent'; //fluent||array
$idnServerUrl = "http://192.168.0.175:12346";
$jsxFiles = [
    __DIR__ . '/../../tests/Graphics/Adobe/test_pass.jsx',
    __DIR__ . '/../../tests/Graphics/Adobe/test_fail.jsx'
];

foreach ($jsxFiles as $k => $jsxFile) {
    $jsxScriptContent = file_get_contents($jsxFile);
    $round = $k + 1;

    if ($mode == 'fluent') {
        $result = $idn
            ->setHost($idnServerUrl)
            ->setSslSecurity(false)
            ->setDebugInfo(false)
            ->setScriptText($jsxScriptContent)
            ->setScriptFile(null)
            ->setScriptLanguage('javascript')
            ->setScriptArgs([['name' => 'doc_name', 'value' => date("Ymd_His") . '_foo'], ['name' => 'delay', 'value' => 0]])
            ->aisRunScript();
    } elseif ($mode == 'array') {
        $options = [
            'host' => $idnServerUrl,
            'sslSecurity' => false,
            'debugInfo' => false,
            'scriptText' => $jsxScriptContent,
            'scriptFile' => null,
            'scriptLanguage' => 'javascript',
            'scriptArgs' => [['name' => 'doc_name', 'value' => date("Ymd_His") . '_bar'], ['name' => 'delay', 'value' => 0]],
        ];
        $result = $idn->setOptionsRunScript($options);
    } else {
        $result = null;
    }

    print_r("===============START Round {$round}===============\r\n");
    //$result has the native SOAP response
    //print_r($result);

    //$result is automatically parsed for convenience
    print_r("+++++++++++++++\r\nRetrun Var: " . $idn->getReturnValue() . "\r\n+++++++++++++++\r\n\r\n");
    print_r($idn->getReturnMessage());
    print_r("\r\n\r\n\r\n\r\n");
}
