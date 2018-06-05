<?php

/**
 * @param null $file
 * @param int $startLine
 * @param int $numberOfLinesToRead
 * @return string
 */
function getFileLine($file = null, $startLine = 1, $numberOfLinesToRead = 1)
{
    $startLine = $startLine - 1; //account for 0 based index
    $endLine = ($startLine + $numberOfLinesToRead) - 1;

    $linesToRead = range($startLine, $endLine);

    $fh = fopen($file, 'r') or die($php_errormsg);

    $txt = '';
    $line = 0;
    while (($buffer = fgets($fh)) !== false) {
        if (in_array($line, $linesToRead)) {
            $txt .= $buffer;
        }
        $line++;
    }

    fclose($fh) or die($php_errormsg);

    return trim($txt);
}


