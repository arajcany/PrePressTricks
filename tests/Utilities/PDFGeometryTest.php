<?php

namespace Utilities;

use PHPUnit\Framework\TestCase;
use arajcany\PrePressTricks\Utilities\PDFGeometry;

class PDFGeometryTest extends TestCase
{

    public $pdfGeometry;


    /**
     * PDFGeometryTest constructor.
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->pdfGeometry = new PDFGeometry();
    }

    public function testFitIntoBox()
    {
        $original = [10, 20, 307, 230];

        $actual = $this->pdfGeometry->getEffectiveGeometry($original, 0, 1);
        unset($actual['anchors'], $actual['anchors_percent']);
        $expected = [
            "left" => 10.0,
            "bottom" => 20.0,
            "right" => 307.0,
            "top" => 230.0,
            "width" => 297.0,
            "height" => 210.0
        ];
        $this->assertEquals($actual, $expected);

//        $geo = $this->pdfGeometry->getEffectiveGeometry($original, 90, 1);
//        print_r($geo);
//
//        $geo = $this->pdfGeometry->getEffectiveGeometry($original, 180, 1);
//        print_r($geo);
//
//        $geo = $this->pdfGeometry->getEffectiveGeometry($original, 270, 1);
//        print_r($geo);


    }


}