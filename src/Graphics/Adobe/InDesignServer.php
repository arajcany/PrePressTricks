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
     * @param string $hostWithPortNumber
     * @return InDesignServer
     */
    public function setHost(string $hostWithPortNumber): static
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
    public function setDebugInfo(mixed $debugInfo): static
    {
        $this->debugInfo = $debugInfo;
        return $this;
    }

    /**
     * @param bool $sslSecurity
     * @return InDesignServer
     */
    public function setSslSecurity(bool $sslSecurity): static
    {
        $this->sslSecurity = $sslSecurity;
        return $this;
    }

    /**
     * @param string $scriptText
     * @return InDesignServer
     */
    public function setScriptText(string $scriptText): static
    {
        $this->scriptText = $scriptText;
        return $this;
    }

    /**
     * @param string $scriptLanguage
     * @return InDesignServer
     */
    public function setScriptLanguage(string $scriptLanguage): static
    {
        $this->scriptLanguage = $scriptLanguage;
        return $this;
    }

    /**
     * @param null|string $scriptFile
     * @return InDesignServer
     */
    public function setScriptFile(null|string $scriptFile): InDesignServer
    {
        $this->scriptFile = $scriptFile;
        return $this;
    }

    /**
     * Set arguments in the Script
     *
     * Can be set as simple associative array of key=>value
     *  [
     *      'doc_name' => date("Ymd_His") . '_bar',
     *      'delay' => 0
     *      'foo' => 'bar'
     *  ]
     *
     * Or set as a SOAP like array
     *  [
     *      ['name' => 'doc_name', 'value' => date("Ymd_His") . '_bar'],
     *      ['name' => 'delay', 'value' => 0]
     *      ['name' => 'foo', 'value' => foo]
     *  ]
     *
     * Both of the above produce the same result.
     *
     * @param null|array $scriptArgs
     * @return InDesignServer
     */
    public function setScriptArgs(null|array $scriptArgs): static
    {
        $clean = [];

        if ($scriptArgs === null) {
            $clean = null;
        } else {
            if (count(array_filter(array_keys($scriptArgs), 'is_string')) === 0) {
                //this is a numerically indexed array
                foreach ($scriptArgs as $arg) {
                    if (isset($arg['name']) && isset($arg['value'])) {
                        $clean[] = $arg;
                    }
                }
            } else {
                foreach ($scriptArgs as $key => $value) {
                    $clean[] = ['name' => $key, 'value' => $value];
                }
            }
        }

        $this->scriptArgs = $clean;
        return $this;
    }


    /**
     * Main function to send a command to AIS.
     * Before running this function, you must run the setter functions to configure the call.
     * Otherwise, use the wrapper function to auto set.
     *
     * @return false|stdClass
     */
    public function aisRunScript(): bool|stdClass
    {
        $soapOptions = ['trace' => $this->debugInfo, 'encoding' => 'utf-8', 'location' => $this->host];

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
            $this->setReturnValue($exception->getCode());
            $this->setReturnMessage($exception->getMessage());
            $result = false;
        }

        return $result;
    }

    /**
     * Wrapper function to auto configure and run
     *
     * @param $options
     * @return false|stdClass
     */
    public function setOptionsRunScript($options): bool|stdClass
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
