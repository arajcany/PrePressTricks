<?php

namespace arajcany\Test\Ticketing;

use PHPUnit\Framework\TestCase;
use arajcany\PrePressTricks\Utilities\Boxes;

class BoxesTest extends TestCase
{

    public function testFitIntoBox()
    {
        $boxes = new Boxes();

        $expected = [
            "width" => 0.5,
            "height" => 1
        ];
        $actual = $boxes->fitIntoBox(1, 2, 1, 1);
        $this->assertEquals(1, 1);

    }

    public function testFillIntoBox()
    {
        $boxes = new Boxes();

        $expected = [
            "width" => 1,
            "height" => 2
        ];
        $actual = $boxes->fillIntoBox(1, 2, 1, 1);
        $this->assertEquals(1, 1);
    }


}