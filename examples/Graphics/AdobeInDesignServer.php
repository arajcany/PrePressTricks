<?php

require __DIR__ . '/../../vendor/autoload.php';

use arajcany\PrePressTricks\Graphics\Adobe\InDesignServer;

$idn = new InDesignServer();

$modes = ['fluent', 'array']; //fluent||array
$idnServerUrl = "http://192.168.0.136:12348";
$jsxFiles = [
    __DIR__ . '/../../tests/Graphics/Adobe/test_pass.jsx',
    __DIR__ . '/../../tests/Graphics/Adobe/test_fail.jsx'
];

foreach ($modes as $mode) {
    foreach ($jsxFiles as $k => $jsxFile) {
        $round = $k + 1;
        dump("===============START Mode {$mode} File {$round}===============\r\n");
        $jsxScriptContent = file_get_contents($jsxFile);

        if ($mode == 'fluent') {
            $result = $idn
                ->setHost($idnServerUrl)
                ->setSslSecurity(false)
                ->setDebugInfo(false)
                ->setScriptText($jsxScriptContent)
                ->setScriptFile(null)
                ->setScriptLanguage('javascript')
                ->setScriptArgs(
                    [
                        'doc_name' => date("Ymd_His") . '_foo',
                        'delay' => 0
                    ]
                )
                ->aisRunScript();
        } elseif ($mode == 'array') {
            $options = [
                'host' => $idnServerUrl,
                'sslSecurity' => false,
                'debugInfo' => false,
                'scriptText' => $jsxScriptContent,
                'scriptFile' => null,
                'scriptLanguage' => 'javascript',
                'scriptArgs' =>
                    [
                        ['name' => 'doc_name', 'value' => date("Ymd_His") . '_bar'],
                        ['name' => 'delay', 'value' => 0]
                    ],
            ];
            $result = $idn->setOptionsRunScript($options);
        } else {
            $result = null;
        }

        //$result has the native SOAP response
        //dump("SOAP Response: ",$result);

        //$result is automatically parsed for convenience
        dump("Return Var: ", $idn->getReturnValue());
        dump("Return Msg: ", $idn->getReturnMessage());
    }
}
