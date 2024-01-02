<?php

namespace arajcany\PrePressTricks\Ticketing\String;

/**
 * Class StringToTicket
 * Extract from a string, data that can be used in Print Engine DFE Job Tickets
 */
class StringToTicket
{
    //setup parameters
    private $inputString;
    private $stringDelimiters;
    private $extensionCheck;
    private $filepathCheck;
    private $caseSensitive;
    private $outputFailValue;

    //patterns for searching strings
    private $quantityPattern;
    private $stockPattern;
    private $plexPattern;
    private $orderIdPattern;
    private $jobIdPattern;
    private $groupKeyPattern;
    private $filterMainPattern;
    private $filterSubPattern;
    private $printerPattern;
    private $widthPattern;
    private $heightPattern;
    private $widthAndHeightPattern;

    function __construct($inputString = null)
    {
        $this->setInputString($inputString);
        $this->setDefaults();
    }

    /**
     * @return mixed
     */
    public function getInputString()
    {
        return $this->inputString;
    }

    /**
     * @param mixed $inputString
     * @return StringToTicket
     */
    public function setInputString($inputString)
    {
        $this->inputString = $inputString;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStringDelimiters()
    {
        return $this->stringDelimiters;
    }

    /**
     * @param mixed $stringDelimiters
     * @return StringToTicket
     */
    public function setStringDelimiters($stringDelimiters)
    {
        $this->stringDelimiters = $stringDelimiters;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCaseSensitive()
    {
        return $this->caseSensitive;
    }

    /**
     * @param mixed $caseSensitive
     * @return StringToTicket
     */
    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutputFailValue()
    {
        return $this->outputFailValue;
    }

    /**
     * @param mixed $outputFailValue
     * @return $this
     */
    public function setOutputFailValue($outputFailValue)
    {
        $this->outputFailValue = $outputFailValue;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getQuantityPattern()
    {
        return $this->quantityPattern;
    }

    /**
     * @param mixed $quantityPattern
     * @return StringToTicket
     */
    public function setQuantityPattern($quantityPattern)
    {
        $this->quantityPattern = $quantityPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStockPattern()
    {
        return $this->stockPattern;
    }

    /**
     * @param mixed $stockPattern
     * @return StringToTicket
     */
    public function setStockPattern($stockPattern)
    {
        $this->stockPattern = $stockPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlexPattern()
    {
        return $this->plexPattern;
    }

    /**
     * @param mixed $plexPattern
     * @return StringToTicket
     */
    public function setPlexPattern($plexPattern)
    {
        $this->plexPattern = $plexPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderIdPattern()
    {
        return $this->orderIdPattern;
    }

    /**
     * @param mixed $orderIdPattern
     * @return StringToTicket
     */
    public function setOrderIdPattern($orderIdPattern)
    {
        $this->orderIdPattern = $orderIdPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobIdPattern()
    {
        return $this->jobIdPattern;
    }

    /**
     * @param mixed $jobIdPattern
     * @return StringToTicket
     */
    public function setJobIdPattern($jobIdPattern)
    {
        $this->jobIdPattern = $jobIdPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroupKeyPattern()
    {
        return $this->groupKeyPattern;
    }

    /**
     * @param mixed $groupKeyPattern
     * @return StringToTicket
     */
    public function setGroupKeyPattern($groupKeyPattern)
    {
        $this->groupKeyPattern = $groupKeyPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilterMainPattern()
    {
        return $this->filterMainPattern;
    }

    /**
     * @param mixed $filterMainPattern
     * @return StringToTicket
     */
    public function setFilterMainPattern($filterMainPattern)
    {
        $this->filterMainPattern = $filterMainPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilterSubPattern()
    {
        return $this->filterSubPattern;
    }

    /**
     * @param mixed $filterSubPattern
     * @return StringToTicket
     */
    public function setFilterSubPattern($filterSubPattern)
    {
        $this->filterSubPattern = $filterSubPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrinterPattern()
    {
        return $this->printerPattern;
    }

    /**
     * @param mixed $printerPattern
     * @return StringToTicket
     */
    public function setPrinterPattern($printerPattern)
    {
        $this->printerPattern = $printerPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidthPattern()
    {
        return $this->widthPattern;
    }

    /**
     * @param mixed $widthPattern
     * @return StringToTicket
     */
    public function setWidthPattern($widthPattern)
    {
        $this->widthPattern = $widthPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeightPattern()
    {
        return $this->heightPattern;
    }

    /**
     * @param mixed $heightPattern
     * @return StringToTicket
     */
    public function setHeightPattern($heightPattern)
    {
        $this->heightPattern = $heightPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidthAndHeightPattern()
    {
        return $this->widthAndHeightPattern;
    }

    /**
     * @param mixed $widthAndHeightPattern
     * @return StringToTicket
     */
    public function setWidthAndHeightPattern($widthAndHeightPattern)
    {
        $this->widthAndHeightPattern = $widthAndHeightPattern;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtensionCheck()
    {
        return $this->extensionCheck;
    }

    /**
     * @param mixed $extensionCheck
     * @return StringToTicket
     */
    public function setExtensionCheck($extensionCheck)
    {
        $this->extensionCheck = $extensionCheck;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilepathCheck()
    {
        return $this->filepathCheck;
    }

    /**
     * @param mixed $filepathCheck
     * @return StringToTicket
     */
    public function setFilepathCheck($filepathCheck)
    {
        $this->filepathCheck = $filepathCheck;
        return $this;
    }


    private function setDefaults()
    {
        $default = [
            'delimiters' => [
                '_',
                '\\',
                '/',
            ],
            'fail-value' => false
        ];

        $defaultPattern = [
            'quantity' => [
                '#(quantity|qty|q|copies|cps|x)(\d+)#s',
            ],
            'plex' => [
                'single-sided' => ['ss', 'single', 'single-sided'],
                'double-sided' => ['ds', 'double', 'double-sided', 'work-and-turn'],
                'head-to-toe' => ['ht', 'head-to-toe', 'wt', 'work-and-tumble'],
            ],
            'stock' => [
                'silk',
                'smooth',
                'gloss',
                'satin',
                'plain',
                'recycled',
            ],
            'order-id' => [
                '#(o|ord|order|orderid|order-id)(\d+)#s',
            ],
            'job-id' => [
                '#(j|jb|job|jobid|job-id)(\d+)#s',
            ],
            'group-key' => [
                '#(gk|groupkey|group-key)([0-9a-zA-Z-]+|\d+)#s',
            ],
            'width' => [
                '#(w|wd|width)(\d+)#s',
            ],
            'height' => [
                '#(h|ht|height)(\d+)#s',
            ],
            'width-and-height' => [
                '#\d+[ ]*[mm]*[ ]*[x][ ]*\d+[ ]*[mm]*#s',
                '#\d+[ ]*[in]*[ ]*[x][ ]*\d+[ ]*[in]*#s',
            ],
            'colours' => [
                'red',
                'green',
                'blue',
                'cyan',
                'magenta',
                'yellow',
                'black',
                'orange',
                'purple',
                'pink',
            ],
        ];

        //setup
        $this->setStringDelimiters($default['delimiters']);
        $this->setOutputFailValue($default['fail-value']);
        $this->setExtensionCheck(true);
        $this->setFilepathCheck(true);
        $this->setCaseSensitive(true);

        //patterns
        $this->setQuantityPattern($defaultPattern['quantity']);
        $this->setPlexPattern($defaultPattern['plex']);
        $this->setStockPattern($defaultPattern['stock']);
        $this->setOrderIdPattern($defaultPattern['order-id']);
        $this->setJobIdPattern($defaultPattern['job-id']);
        $this->setGroupKeyPattern($defaultPattern['group-key']);
        $this->setWidthPattern($defaultPattern['width']);
        $this->setHeightPattern($defaultPattern['height']);
        $this->setWidthAndHeightPattern($defaultPattern['width-and-height']);

    }

    private function normaliseString($str)
    {
        $strNormalised = $str;

        if ($this->getExtensionCheck() === true) {
            $strNormalised = pathinfo($str, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($str, PATHINFO_FILENAME);
        }

        if ($this->getFilepathCheck() === true) {
            $strNormalised = str_replace(["/", "\\"], "_", $strNormalised);
        }

        $delimiters = $this->stringDelimiters;
        foreach ($delimiters as $delimiter) {
            $strNormalised = str_replace($delimiter, "_", $strNormalised);
        }

        $strNormalised = "_" . $strNormalised . "_";

        return $strNormalised;
    }


    /**
     * Extract the QTY from a string
     *
     * @return int|bool
     */
    public function extractQuantity()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);

        //removed possibility of 000x000 fooling the qty of x000
        $regexPatterns = $this->getWidthAndHeightPattern();
        foreach ($regexPatterns as $regexPattern) {
            if ($this->getCaseSensitive() === true) {
                $regexPattern .= '';
            } else {
                $regexPattern .= 'i';
            }

            $stringNormalised = preg_replace($regexPattern, '', $stringNormalised);
        }

        //extract the qty
        $regexPatterns = $this->getQuantityPattern();
        foreach ($regexPatterns as $regexPattern) {
            if ($this->getCaseSensitive() === true) {
                $regexPattern .= '';
            } else {
                $regexPattern .= 'i';
            }

            preg_match($regexPattern, $stringNormalised, $matches);
            //return the Group2 match
            if (isset($matches[2])) {
                return $matches[2];
            }
        }

        return $this->getOutputFailValue();
    }

    /**
     * Extract the STOCK from a string
     *
     * @return string|bool
     */
    public function extractStock()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->stockPattern;

        $found = [];
        foreach ($patterns as $stockPattern) {
            $currentPattern = "_" . $stockPattern . "_";
            if ($this->getCaseSensitive() === true) {
                if (strstr($stringNormalised, $currentPattern)) {
                    $found[] = $stockPattern;
                }
            } else {
                if (stristr($stringNormalised, $currentPattern)) {
                    $found[] = $stockPattern;
                }
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return $this->getOutputFailValue();
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }

        return false;
    }

    /**
     * Extract the PLEX from a string
     *
     * @return string|bool
     */
    public function extractPlex()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->plexPattern;

        $found = [];
        foreach ($patterns as $plexName => $plexPatterns) {
            foreach ($plexPatterns as $plexPattern) {
                $currentPattern = "_" . $plexPattern . "_";
                if ($this->getCaseSensitive() === true) {
                    if (strstr($stringNormalised, $currentPattern)) {
                        $found[] = $plexName;
                    }
                } else {
                    if (stristr($stringNormalised, $currentPattern)) {
                        $found[] = $plexName;
                    }
                }
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return $this->getOutputFailValue();
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }

        return false;
    }

    /**
     * Extract the ORDER ID from a string
     *
     * @return int|bool
     */
    public function extractOrderId()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);
        $regexPatterns = $this->getOrderIdPattern();

        foreach ($regexPatterns as $regexPattern) {
            if ($this->getCaseSensitive() === true) {
                $regexPattern .= '';
            } else {
                $regexPattern .= 'i';
            }

            preg_match($regexPattern, $stringNormalised, $matches);
            //return the Group2 match
            if (isset($matches[2])) {
                return $matches[2];
            }
        }

        return $this->getOutputFailValue();
    }

    /**
     * Extract the JOB ID from a string
     *
     * @return int|bool
     */
    public function extractJobId()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);
        $regexPatterns = $this->getJobIdPattern();

        foreach ($regexPatterns as $regexPattern) {
            if ($this->getCaseSensitive() === true) {
                $regexPattern .= '';
            } else {
                $regexPattern .= 'i';
            }

            preg_match($regexPattern, $stringNormalised, $matches);
            //return the Group2 match
            if (isset($matches[2])) {
                return $matches[2];
            }
        }

        return $this->getOutputFailValue();
    }

    /**
     * Extract the GROUP KEY from a string
     *
     * @return int|bool
     */
    public function extractGroupKey()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);
        $regexPatterns = $this->getGroupKeyPattern();

        foreach ($regexPatterns as $regexPattern) {
            if ($this->getCaseSensitive() === true) {
                $regexPattern .= '';
            } else {
                $regexPattern .= 'i';
            }

            preg_match($regexPattern, $stringNormalised, $matches);
            //return the Group2 match
            if (isset($matches[2])) {
                return $matches[2];
            }
        }

        return $this->getOutputFailValue();
    }

    /**
     * Extract the WIDTH from a string
     *
     * @return int|bool
     */
    public function extractWidth()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);
        $regexPatterns = $this->getWidthPattern();

        foreach ($regexPatterns as $regexPattern) {
            if ($this->getCaseSensitive() === true) {
                $regexPattern .= '';
            } else {
                $regexPattern .= 'i';
            }

            preg_match($regexPattern, $stringNormalised, $matches);

            if (isset($matches[0])) {
                preg_match_all('/\d+/si', $matches[0], $numbers);
                if (isset($numbers[0][0])) {
                    return $numbers[0][0];
                }
            }
        }

        return $this->getOutputFailValue();
    }

    /**
     * Extract the HEIGHT from a string
     *
     * @return int|bool
     */
    public function extractHeight()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);
        $regexPatterns = $this->getHeightPattern();

        foreach ($regexPatterns as $regexPattern) {
            if ($this->getCaseSensitive() === true) {
                $regexPattern .= '';
            } else {
                $regexPattern .= 'i';
            }

            preg_match($regexPattern, $stringNormalised, $matches);

            if (isset($matches[0])) {
                preg_match_all('/\d+/si', $matches[0], $numbers);

                if (isset($numbers[0][0])) {
                    return $numbers[0][0];
                }
            }
        }

        return $this->getOutputFailValue();
    }

    /**
     * Extract the WIDTH and HEIGHT from a string
     *
     * Special case looking for format 000x000
     *
     * @return array|bool
     */
    public function extractWidthAndHeight()
    {
        $string = $this->getInputString();
        $stringNormalised = $this->normaliseString($string);
        $regexPatterns = $this->getWidthAndHeightPattern();

        foreach ($regexPatterns as $regexPattern) {
            if ($this->getCaseSensitive() === true) {
                $regexPattern .= '';
            } else {
                $regexPattern .= 'i';
            }

            preg_match($regexPattern, $stringNormalised, $matches);

            if (isset($matches[0])) {
                preg_match_all('/\d+/si', $matches[0], $numbers);

                if (isset($numbers[0][1]) && isset($numbers[0][1])) {
                    return $numbers[0];
                }
            }
        }

        return [$this->getOutputFailValue(), $this->getOutputFailValue()];
    }


    //var dumping
    private function pr($str)
    {
        echo("\r\n====" . json_encode($str) . "====\r\n");
    }

}
