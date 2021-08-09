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
    public function __construct()
    {
        parent::__construct();
        $this->pdfGeometry = new PDFGeometry();
    }

    public function testFitIntoBox()
    {
        $original = [10, 20, 307, 230];

        $actual = $this->pdfGeometry->getEffectiveGeometry($original, 0, 1);
        $expected = [
            "left" => 10,
            "bottom" => 20,
            "right" => 307,
            "top" => 230,
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