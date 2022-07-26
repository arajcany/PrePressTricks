<?php

namespace Ticketing\String;

use PHPUnit\Framework\TestCase;
use arajcany\PrePressTricks\Ticketing\String\StringToTicket;

/**
 * Class StringToTicketTest
 */
class StringToTicketTest extends TestCase
{
    public $tstHomeDir;
    public $tstTmpDir;
    public $now;
    public $str2ticket;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->now = date("Y-m-d H:i:s");
        $this->tstHomeDir = __DIR__ . DIRECTORY_SEPARATOR .".." . DIRECTORY_SEPARATOR;
        $this->tstTmpDir = __DIR__ . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR;
    }

    /**
     * Extract QTY
     */
    public function testExtractQty()
    {
        $this->str2ticket = new StringToTicket("");
        $strings = [
            'quantity1',
            'qty2',
            'q3',
            'copies4',
            'cps5',
            'x6',
            '7x8_x9',
        ];

        $expectedResults = [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '9',
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractQuantity();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }

    /**
     * Extract PLEX
     */
    public function testExtractPlex()
    {
        $this->str2ticket = (new StringToTicket(""))->setCaseSensitive(false);
        $strings = [
            "SS",
            "single",
            "double",
            "DS",
            "na",
            "SSingle",
        ];

        $expectedResults = [
            'single-sided',
            'single-sided',
            'double-sided',
            'double-sided',
            false,
            false,
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractPlex();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }

    /**
     * Extract STOCK
     */
    public function testExtractStock()
    {
        $this->str2ticket = (new StringToTicket(""))->setCaseSensitive(false);
        $strings = [
            'Silk',
            'SMOOTH',
            'gloSS',
            'sAtin',
            'plain',
            'Recycled',
        ];

        $expectedResults = [
            'silk',
            'smooth',
            'gloss',
            'satin',
            'plain',
            'recycled',
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractStock();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }

    /**
     * Extract ORDER ID
     */
    public function testExtractOrderId()
    {
        $this->str2ticket = (new StringToTicket(""))->setCaseSensitive(false);
        $strings = [
            'o1',
            'ord2',
            'order3',
            'orderid4',
            'order-id5',
        ];

        $expectedResults = [
            '1',
            '2',
            '3',
            '4',
            '5',
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractOrderId();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }

    /**
     * Extract JOB ID
     */
    public function testExtractJobId()
    {
        $this->str2ticket = (new StringToTicket(""))->setCaseSensitive(false);
        $strings = [
            'j1',
            'jb2',
            'job3',
            'jobid4',
            'job-id5',
        ];

        $expectedResults = [
            '1',
            '2',
            '3',
            '4',
            '5',
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractJobId();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }

    /**
     * Extract JOB ID
     */
    public function testExtractGroupKey()
    {
        $this->str2ticket = (new StringToTicket(""))->setCaseSensitive(false);
        $strings = [
            'gk1',
            'groupkey2',
            'group-key3',
            'gk66axg do not include this',
            'groupkey77-a0x_g do not include from _g onwards',
            'group-key00-0C-29-F6-2B-CF',
        ];

        $expectedResults = [
            '1',
            '2',
            '3',
            '66axg',
            '77-a0x',
            '00-0C-29-F6-2B-CF',
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractGroupKey();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }

    /**
     * Extract WIDTH
     */
    public function testExtractWidth()
    {
        $this->str2ticket = (new StringToTicket(""))->setCaseSensitive(false);
        $strings = [
            'w210',
            'wd211',
            'width212',
            '',
        ];

        $expectedResults = [
            '210',
            '211',
            '212',
            false,
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractWidth();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }

    /**
     * Extract HEIGHT
     */
    public function testExtractHeight()
    {
        $this->str2ticket = (new StringToTicket(""))->setCaseSensitive(false);
        $strings = [
            'h297',
            'ht298',
            'height299',
            '',
        ];

        $expectedResults = [
            '297',
            '298',
            '299',
            false,
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractHeight();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }

    /**
     * Extract WIDTH and HEIGHT
     */
    public function testExtractWidthAndHeight()
    {
        $this->str2ticket = (new StringToTicket(""))->setCaseSensitive(false);
        $strings = [
            '000x000',
            '000 x 000',
            ' 000x000',
            '000x000 ',
            ' 000x000 ',
            ' 000 x 000 ',
            ' 000 x000 ',
            ' 000x 000 ',

            '000mmx000mm',
            '000mm x 000mm',
            ' 000mmx000mm',
            '000mmx000mm ',
            ' 000mmx000mm ',
            ' 000mm x 000mm ',
            ' 000mm x000mm ',
            ' 000mmx 000mm ',

            '000x000mm',
            '000 x 000mm',
            ' 000x000mm',
            '000x000mm ',
            ' 000x000mm ',
            ' 000 x 000mm ',
            ' 000 x000mm ',
            ' 000x 000mm ',

            '000mmx000',
            '000mm x 000',
            ' 000mmx000',
            '000mmx000 ',
            ' 000mmx000 ',
            ' 000mm x 000 ',
            ' 000mm x000 ',
            ' 000mmx 000 ',
        ];

        $expectedResults = [
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],

            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],

            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],

            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
            ['000', '000'],
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractWidthAndHeight();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }


}
