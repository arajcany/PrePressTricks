<?php

namespace Utilities;

use PHPUnit\Framework\TestCase;
use arajcany\PrePressTricks\Utilities\Boxes;

class BoxesTest extends TestCase
{

    public function testFitIntoBox()
    {
        $boxes = new Boxes();

        $imageWidth = 100.0;
        $imageHeight = 100.0;
        $boxWidth = 210.0;
        $boxHeight = 297.0;
        $expectedSize = [
            "width" => 210.0,
            "height" => 210.0
        ];
        $expectedScale = [
            "scale_width" => 2.1,
            "scale_height" => 2.1
        ];
        $newImageSize = $boxes->fitIntoBox($imageWidth, $imageHeight, $boxWidth, $boxHeight);
        $newImageScale = $boxes->fitIntoBoxFactor($imageWidth, $imageHeight, $boxWidth, $boxHeight);
        $this->assertEquals($expectedSize, $newImageSize);
        $this->assertEquals($expectedScale, $newImageScale);

    }

    public function testFillIntoBox()
    {
        $boxes = new Boxes();

        $imageWidth = 100.0;
        $imageHeight = 100.0;
        $boxWidth = 210.0;
        $boxHeight = 297.0;
        $expectedSize = [
            "width" => 297.0,
            "height" => 297.0,
        ];
        $expectedScale = [
            "scale_width" => 2.97,
            "scale_height" => 2.97,
        ];
        $newImageSize = $boxes->fillIntoBox($imageWidth, $imageHeight, $boxWidth, $boxHeight);
        $newImageScale = $boxes->fillIntoBoxFactor($imageWidth, $imageHeight, $boxWidth, $boxHeight);
        $this->assertEquals($expectedSize, $newImageSize);
        $this->assertEquals($expectedScale, $newImageScale);
    }

    public function testStretchIntoBox()
    {
        $boxes = new Boxes();

        $imageWidth = 100.0;
        $imageHeight = 100.0;
        $boxWidth = 210.0;
        $boxHeight = 297.0;
        $expectedSize = [
            "width" => 210.0,
            "height" => 297.0,
        ];
        $expectedScale = [
            "scale_width" => 2.1,
            "scale_height" => 2.97,
        ];
        $newImageSize = $boxes->stretchIntoBox($imageWidth, $imageHeight, $boxWidth, $boxHeight);
        $newImageScale = $boxes->stretchIntoBoxFactor($imageWidth, $imageHeight, $boxWidth, $boxHeight);
        $this->assertEquals($expectedSize, $newImageSize);
        $this->assertEquals($expectedScale, $newImageScale);
    }

    public function testBestFitFactor()
    {
        $boxes = new Boxes();

        $imageWidth = 420.0;
        $imageHeight = 297.0;
        $boxWidth = 210.0;
        $boxHeight = 297.0;
        $expectedSize = [
            "width" => 210.0,
            "height" => 297.0,
        ];
        $expectedScale = [
            "scale_width" => 0.7071,
            "scale_height" => 0.7071,
            "rotate" => true,
        ];
        $newImageScale = $boxes->bestFitFactor($imageWidth, $imageHeight, $boxWidth, $boxHeight, true);
        $this->assertEquals($expectedScale, $newImageScale);
    }


}