<?php

use arajcany\PrePressTricks\Utilities\Boxes;

class BoxesTest extends PHPUnit_Framework_TestCase
{

    public function testFitIntoBox()
    {
        $boxes = new Boxes();

        $expected = [
            "width" => 0.5,
            "height" => 1
        ];
        $actual = $boxes->fitIntoBox(1, 2, 1, 1);
        $this->assertEquals($expected, $actual);


        echo "testFitInBox completed\r\n";
    }

    public function testFillIntoBox()
    {
        $boxes = new Boxes();

        $expected = [
            "width" => 1,
            "height" => 2
        ];
        $actual = $boxes->fillIntoBox(1, 2, 1, 1);
        $this->assertEquals($expected, $actual);


        echo "testFillInBox completed\r\n";
    }


}