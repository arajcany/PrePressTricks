<?php

namespace arajcany\Test\Ticketing;

use PHPUnit\Framework\TestCase;
use arajcany\PrePressTricks\Ticketing\StringToTicket;

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
        $this->tstHomeDir = __DIR__ . "\\..\\";
        $this->tstTmpDir = __DIR__ . "\\..\\..\\tmp\\";
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
        ];

        $expectedResults = [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
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
            'silk',
            'smooth',
            'gloss',
            'satin',
            'plain',
            'recycled',
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
            'oid1',
            'orderid2',
            'order-id3',
            'oidXX123',
            'orderid123-xx',
            'order-idy2018m06d05-XYZ',
        ];

        $expectedResults = [
            '1',
            '2',
            '3',
            'XX123',
            '123-xx',
            'y2018m06d05-XYZ',
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
            'jid1',
            'jobid2',
            'job-id3',
            'jidXX123',
            'jobid123-xx',
            'job-idy2018m06d05-XYZ',
        ];

        $expectedResults = [
            '1',
            '2',
            '3',
            'XX123',
            '123-xx',
            'y2018m06d05-XYZ',
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
        ];

        $expectedResults = [
            '1',
            '2',
            '3',
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
            '210x297_1x2',
            '210x297',
            '420X297',
            '_x30_320x450',
            '',
        ];

        $expectedResults = [
            [210, 297],
            [210, 297],
            [420, 297],
            [320, 450],
            false,
        ];

        foreach ($strings as $key => $string) {
            $this->str2ticket->setInputString($string);
            $return = $this->str2ticket->extractWidthAndHeight();
            $this->assertEquals($expectedResults[$key], $return);
        }
    }


}
