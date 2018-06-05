<?php

namespace arajcany\PrePressTricks\Ticketing;

/**
 * Class StringToTicket
 * Extract from a string, data that can be used in Print Engine DFE Job Tickets
 */
class StringToTicket
{
    //input string parameters
    private $inputString;
    private $stringDelimiters;
    private $extensionCheck;
    private $filepathCheck;
    private $caseSensitive;

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
        $defaultPattern = [
            'delimiters' => [
                '_',
                '\\',
                '/',
            ],
            'quantity' => [
                'quantity',
                'qty',
                'q',
                'copies',
                'cps',
                'x',
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
                'oid',
                'orderid',
                'order-id',
            ],
            'job-id' => [
                'jid',
                'jobid',
                'job-id',
            ],
            'group-key' => [
                'gk',
                'groupkey',
                'group-key',
            ],
            'width' => [
                'w',
                'wd',
                'width',
            ],
            'height' => [
                'h',
                'ht',
                'height',
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

        $this->setStringDelimiters($defaultPattern['delimiters']);
        $this->setQuantityPattern($defaultPattern['quantity']);
        $this->setPlexPattern($defaultPattern['plex']);
        $this->setStockPattern($defaultPattern['stock']);
        $this->setOrderIdPattern($defaultPattern['order-id']);
        $this->setJobIdPattern($defaultPattern['job-id']);
        $this->setGroupKeyPattern($defaultPattern['group-key']);
        $this->setWidthPattern($defaultPattern['width']);
        $this->setHeightPattern($defaultPattern['height']);
        $this->setExtensionCheck(true);
        $this->setFilepathCheck(true);
        $this->setCaseSensitive(true);

    }

    private function normaliseString($str)
    {
        $strNormalised = $str;

        if ($this->getExtensionCheck() === true) {
            $strNormalised = pathinfo($str, PATHINFO_FILENAME);
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
        $string = $this->inputString;
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->quantityPattern;

        $found = [];
        foreach ($patterns as $pattern) {
            $currentPattern = "_" . $pattern;
            $stringParts = explode($currentPattern, $stringNormalised);
            if (count($stringParts) >= 2) {
                $stringParts = explode("_", $stringParts[1]);
                $qty = $stringParts[0];
                if ($this->getCaseSensitive() === true) {
                    $qtyStrReplaced = str_replace($currentPattern, '', $qty);
                } else {
                    $qtyStrReplaced = str_ireplace($currentPattern, '', $qty);
                }
                $qtyPregReplaced = preg_replace("/[^0-9]/", '', $qty);
                if ($qtyStrReplaced == $qtyPregReplaced) {
                    if ($qty === '') {
                        $found[] = false;
                    } elseif ($qty === false) {
                        $found[] = false;
                    } elseif ($qty === null) {
                        $found[] = false;
                    } else {
                        $found[] = $qty;
                    }
                }
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return false;
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }
    }

    /**
     * Extract the STOCK from a string
     *
     * @return string|bool
     */
    public function extractStock()
    {
        $string = $this->inputString;
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->stockPattern;

        $found = [];
        foreach ($patterns as $stockPattern) {
            $currentPattern = "_" . $stockPattern . "_";
            if (strstr($stringNormalised, $currentPattern)) {
                $found[] = $stockPattern;
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return false;
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }
    }

    /**
     * Extract the PLEX from a string
     *
     * @return string|bool
     */
    public function extractPlex()
    {
        $string = $this->inputString;
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
            return false;
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }
    }

    /**
     * Extract the ORDER ID from a string
     *
     * @return int|bool
     */
    public function extractOrderId()
    {
        $string = $this->inputString;
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->orderIdPattern;

        $found = [];
        foreach ($patterns as $pattern) {
            $currentPattern = "_" . $pattern;
            $stringParts = explode($currentPattern, $stringNormalised);
            if (count($stringParts) >= 2) {
                $stringParts = explode("_", $stringParts[1]);
                $id = $stringParts[0];
                if ($this->getCaseSensitive() === true) {
                    $idStrReplaced = str_replace($currentPattern, '', $id);
                } else {
                    $idStrReplaced = str_ireplace($currentPattern, '', $id);
                }
                $idPregReplaced = preg_replace("/[^0-9a-zA-Z\-]/", '', $id);
                if ($idStrReplaced == $idPregReplaced) {
                    if ($id === '') {
                        $found[] = false;
                    } elseif ($id === false) {
                        $found[] = false;
                    } elseif ($id === null) {
                        $found[] = false;
                    } else {
                        $found[] = $id;
                    }
                }
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return false;
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }
    }

    /**
     * Extract the JOB ID from a string
     *
     * @return int|bool
     */
    public function extractJobId()
    {
        $string = $this->inputString;
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->jobIdPattern;

        $found = [];
        foreach ($patterns as $pattern) {
            $currentPattern = "_" . $pattern;
            $stringParts = explode($currentPattern, $stringNormalised);
            if (count($stringParts) >= 2) {
                $stringParts = explode("_", $stringParts[1]);
                $id = $stringParts[0];
                if ($this->getCaseSensitive() === true) {
                    $idStrReplaced = str_replace($currentPattern, '', $id);
                } else {
                    $idStrReplaced = str_ireplace($currentPattern, '', $id);
                }
                $idPregReplaced = preg_replace("/[^0-9a-zA-Z\-]/", '', $id);
                if ($idStrReplaced == $idPregReplaced) {
                    if ($id === '') {
                        $found[] = false;
                    } elseif ($id === false) {
                        $found[] = false;
                    } elseif ($id === null) {
                        $found[] = false;
                    } else {
                        $found[] = $id;
                    }
                }
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return false;
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }
    }

    /**
     * Extract the GROUP KEY from a string
     *
     * @return int|bool
     */
    public function extractGroupKey()
    {
        $string = $this->inputString;
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->groupKeyPattern;

        $found = [];
        foreach ($patterns as $pattern) {
            $currentPattern = "_" . $pattern;
            $stringParts = explode($currentPattern, $stringNormalised);
            if (count($stringParts) >= 2) {
                $stringParts = explode("_", $stringParts[1]);
                $id = $stringParts[0];
                if ($this->getCaseSensitive() === true) {
                    $idStrReplaced = str_replace($currentPattern, '', $id);
                } else {
                    $idStrReplaced = str_ireplace($currentPattern, '', $id);
                }
                $idPregReplaced = preg_replace("/[^0-9a-zA-Z\-]/", '', $id);
                if ($idStrReplaced == $idPregReplaced) {
                    if ($id === '') {
                        $found[] = false;
                    } elseif ($id === false) {
                        $found[] = false;
                    } elseif ($id === null) {
                        $found[] = false;
                    } else {
                        $found[] = $id;
                    }
                }
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return false;
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }
    }

    /**
     * Extract the WIDTH from a string
     *
     * @return int|bool
     */
    public function extractWidth()
    {
        $string = $this->inputString;
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->widthPattern;

        $found = [];
        foreach ($patterns as $pattern) {
            $currentPattern = "_" . $pattern;
            $stringParts = explode($currentPattern, $stringNormalised);
            if (count($stringParts) >= 2) {
                $stringParts = explode("_", $stringParts[1]);
                $id = $stringParts[0];
                if ($this->getCaseSensitive() === true) {
                    $idStrReplaced = str_replace($currentPattern, '', $id);
                } else {
                    $idStrReplaced = str_ireplace($currentPattern, '', $id);
                }
                $idPregReplaced = preg_replace("/[^0-9]/", '', $id);
                if ($idStrReplaced == $idPregReplaced) {
                    if ($id === '') {
                        $found[] = false;
                    } elseif ($id === false) {
                        $found[] = false;
                    } elseif ($id === null) {
                        $found[] = false;
                    } else {
                        $found[] = $id;
                    }
                }
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return false;
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }
    }

    /**
     * Extract the HEIGHT from a string
     *
     * @return int|bool
     */
    public function extractHeight()
    {
        $string = $this->inputString;
        $stringNormalised = $this->normaliseString($string);
        $patterns = $this->heightPattern;

        $found = [];
        foreach ($patterns as $pattern) {
            $currentPattern = "_" . $pattern;
            $stringParts = explode($currentPattern, $stringNormalised);
            if (count($stringParts) >= 2) {
                $stringParts = explode("_", $stringParts[1]);
                $id = $stringParts[0];
                if ($this->getCaseSensitive() === true) {
                    $idStrReplaced = str_replace($currentPattern, '', $id);
                } else {
                    $idStrReplaced = str_ireplace($currentPattern, '', $id);
                }
                $idPregReplaced = preg_replace("/[^0-9]/", '', $id);
                if ($idStrReplaced == $idPregReplaced) {
                    if ($id === '') {
                        $found[] = false;
                    } elseif ($id === false) {
                        $found[] = false;
                    } elseif ($id === null) {
                        $found[] = false;
                    } else {
                        $found[] = $id;
                    }
                }
            }
        }

        $found = array_unique($found);
        if (count($found) == 0) {
            return false;
        } elseif (count($found) == 1) {
            return $found[0];
        } elseif (count($found) > 1) {
            return $found[0];
        }
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
        $string = $this->inputString;
        $stringNormalised = $this->normaliseString($string);

        //width
        $regexDelimiter = '#';
        $startTag = '_';
        $endTag = 'x';
        $regex = $regexDelimiter
            . preg_quote($startTag, $regexDelimiter) . '(.*?)' . preg_quote($endTag, $regexDelimiter)
            . $regexDelimiter
            . 's';
        if ($this->getCaseSensitive() === true) {
            $regex .= '';
        } else {
            $regex .= 'i';
        }
        preg_match($regex, $stringNormalised, $matchesWidth);

        //height
        $regexDelimiter = '#';
        $startTag = 'x';
        $endTag = '_';
        $regex = $regexDelimiter
            . preg_quote($startTag, $regexDelimiter) . '(.*?)' . preg_quote($endTag, $regexDelimiter)
            . $regexDelimiter
            . 's';
        if ($this->getCaseSensitive() === true) {
            $regex .= '';
        } else {
            $regex .= 'i';
        }
        preg_match($regex, $stringNormalised, $matchesHeight);

        if (isset($matchesWidth[1]) && isset($matchesHeight[1])) {
            $found = [$matchesWidth[1], $matchesHeight[1]];
        } else {
            $found = false;
        }

        return $found;
    }


    //var dumping
    private function pr($str)
    {
        echo("\r\n====" . json_encode($str) . "====\r\n");
    }

}
