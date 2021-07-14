<?php

namespace arajcany\PrePressTricks\Graphics\Adobe;


use SoapClient;
use SoapFault;
use stdClass;

class InDesignServer
{
    private $wsdl;
    private $host = null;
    private $debugInfo;
    private $sslSecurity;

    private $scriptText = null;
    private $scriptLanguage = null;
    private $scriptFile = null;
    private $scriptArgs = null;

    private $returnValue = null;
    private $returnMessage = null;


    /**
     * InDesignServer constructor.
     */
    public function __construct()
    {
        $this->setSslSecurity(true);
        $this->setDebugInfo(false);
    }

    /**
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * @param mixed $returnValue
     */
    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;
    }

    /**
     * @return mixed
     */
    public function getReturnMessage()
    {
        return $this->returnMessage;
    }

    /**
     * @param mixed $returnMessage
     */
    public function setReturnMessage($returnMessage)
    {
        $this->returnMessage = $returnMessage;
    }

    /**
     * @param mixed $hostWithPortNumber
     * @return InDesignServer
     */
    public function setHost($hostWithPortNumber)
    {
        $hostWithPortNumber = rtrim($hostWithPortNumber, "\\/");

        $this->host = $hostWithPortNumber;
        $this->wsdl = $hostWithPortNumber . "/Service?wsdl";
        return $this;
    }

    /**
     * @param mixed $debugInfo
     * @return InDesignServer
     */
    public function setDebugInfo($debugInfo)
    {
        $this->debugInfo = $debugInfo;
        return $this;
    }

    /**
     * @param mixed $sslSecurity
     * @return InDesignServer
     */
    public function setSslSecurity($sslSecurity)
    {
        $this->sslSecurity = $sslSecurity;
        return $this;
    }

    /**
     * @param string $scriptText
     * @return InDesignServer
     */
    public function setScriptText($scriptText)
    {
        $this->scriptText = $scriptText;
        return $this;
    }

    /**
     * @param string $scriptLanguage
     * @return InDesignServer
     */
    public function setScriptLanguage($scriptLanguage)
    {
        $this->scriptLanguage = $scriptLanguage;
        return $this;
    }

    /**
     * @param string $scriptFile
     * @return InDesignServer
     */
    public function setScriptFile($scriptFile)
    {
        $this->scriptFile = $scriptFile;
        return $this;
    }

    /**
     * @param array $scriptArgs
     * @return InDesignServer
     */
    public function setScriptArgs($scriptArgs)
    {
        $this->scriptArgs = $scriptArgs;
        return $this;
    }


    /**
     * Main function to send a command to AIS.
     * Before running this function, you must runs the setter functions to configure the call.
     * Otherwise use the wrapper function to auto set.
     *
     * @return \Exception|SoapFault
     */
    public function aisRunScript()
    {
        $soapOptions = $hostOptions = ['trace' => $this->debugInfo, 'encoding' => 'utf-8', 'location' => $this->host];

        if ($this->sslSecurity === false) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            $sslOptions = [
                'stream_context' => $context,
            ];
        } else {
            $sslOptions = [];
        }
        $soapOptions = array_merge($soapOptions, $sslOptions);

        try {
            $client = new SoapClient($this->wsdl, $soapOptions);

            $runScriptParams = [
                'runScriptParameters' => [
                    'scriptText' => $this->scriptText,
                    'scriptLanguage' => $this->scriptLanguage,
                    'scriptFile' => $this->scriptFile,
                    'scriptArgs' => $this->scriptArgs
                ]
            ];

            /**
             * @var stdClass $result
             */
            $result = $client->RunScript($runScriptParams);

            unset($client);

            $this->setReturnValue($result->errorNumber);

            if (isset($result->errorString)) {
                $this->setReturnMessage($result->errorString);
            } else {

                if (isset($result->scriptResult->data)) {
                    $data = $result->scriptResult->data;
                    if (is_string($data)) {
                        $this->setReturnMessage($data);
                    } elseif (is_object($data)) {
                        $compiled = json_decode(json_encode($data->item), JSON_OBJECT_AS_ARRAY);
                        if (count($compiled) * 2 === count($compiled, COUNT_RECURSIVE)) {
                            //simple top level array so squash it down
                            $simplified = [];
                            foreach ($compiled as $item) {
                                if (isset($item['data'])) {
                                    $simplified[] = $item['data'];
                                }
                            }
                            $this->setReturnMessage($simplified);
                        } else {
                            //complex nested array so just leave as is
                            $this->setReturnMessage($compiled);
                        }

                    }
                } else {
                    $this->setReturnMessage(null);
                }
            }
        } catch (SoapFault $exception) {
            $result = $exception;

            $this->setReturnValue($exception->getCode());
            $this->setReturnMessage($exception->getMessage());
        }

        return $result;
    }

    /**
     * Wrapper function to auto configure and run
     *
     * @param $options
     * @return \Exception|false|SoapFault|stdClass
     */
    public function setOptionsRunScript($options)
    {
        $defaultOptions = [
            'host' => null,
            'sslSecurity' => false,
            'debugInfo' => false,
            'scriptText' => null,
            'scriptFile' => null,
            'scriptLanguage' => null,
            'scriptArgs' => null,
        ];

        $options = array_merge($defaultOptions, $options);

        if (empty($options['host'])) {
            return false;
        }

        if (empty($options['scriptText']) && empty($options['scriptFile'])) {
            return false;
        }

        if (empty($options['scriptLanguage'])) {
            return false;
        }

        $ais = $this
            ->setHost($options['host'])
            ->setSslSecurity($options['sslSecurity'])
            ->setDebugInfo($options['debugInfo'])
            ->setScriptText($options['scriptText'])
            ->setScriptFile($options['scriptFile'])
            ->setScriptLanguage($options['scriptLanguage'])
            ->setScriptArgs($options['scriptArgs']);

        return $ais->aisRunScript();
    }


}
