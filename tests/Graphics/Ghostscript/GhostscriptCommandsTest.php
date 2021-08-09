<?php


namespace Graphics\Ghostscript;

use PHPUnit\Framework\TestCase;
use arajcany\PrePressTricks\Graphics\Ghostscript\GhostscriptCommands;
use arajcany\PrePressTricks\Utilities\Boxes;

class GhostscriptCommandsTest extends TestCase
{
    public $tstHomeDir;
    public $tstTmpDir;
    public $tstSampleFilesDir;
    public $now;
    private $gsExe;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->now = date("Y-m-d H:i:s");
        $this->tstHomeDir = str_replace("\\Graphics\\Ghostscript", '', __DIR__) . DS;
        $this->tstTmpDir = __DIR__ . "\\..\\..\\..\\tmp\\";
        $this->tstSampleFilesDir = __DIR__ . DS . ".." . DS . "SampleFiles" . DS;

        //print_r("\r\n{$this->tstSampleFilesDir}\r\n");

        $command = "where gswin64c";
        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);
        if (isset($output[0])) {
            if (is_file($output[0])) {
                $this->gsExe = $output[0];
            } else {
                $this->gsExe = false;
            }
        } else {
            $this->gsExe = false;
        }
    }


    /**
     * test that GS path is valid
     */
    public function testIsGsValid()
    {
        $this->assertNotEquals(false, $this->gsExe);
    }


    public function testPdfReporting()
    {
        $gs = new GhostscriptCommands();
        $gs->setGsPath($this->gsExe);
        $pdf = $this->tstSampleFilesDir . "For_The_Term_of_His_Natural_Life.pdf";
        $rawTextReportSameDir = $this->tstSampleFilesDir . "For_The_Term_of_His_Natural_Life.report.txt";
        $rawTextReportOtherDir = $this->tstTmpDir . "For_The_Term_of_His_Natural_Life.report.txt";

        $jsonCallasReportSameDir = $this->tstSampleFilesDir . "For_The_Term_of_His_Natural_Life.report.json";
        $jsonCallasReportOtherDir = $this->tstTmpDir . "For_The_Term_of_His_Natural_Life.report.json";

        if (!$this->gsExe) {
            $this->assertEquals("Could not find GS executable!", false);
            return false;
        }


        //raw text, see if first and last page exist in the string
        $reportResult = $gs->getPdfReport($pdf, false, false);
        $this->assertStringContainsString('Page 1 MediaBox: [0 0 595 842] CropBox: [0 0 595 842]    Rotate = 0', $reportResult);
        $this->assertStringContainsString('Page 433 MediaBox: [0 0 595 842] CropBox: [0 0 595 842]    Rotate = 0', $reportResult);


        //see if report written to same directory and other directory
        $gs->getPdfReport($pdf, false, true);
        $gs->getPdfReport($pdf, false, $rawTextReportOtherDir);
        $this->assertFileExists($rawTextReportSameDir);
        $this->assertFileExists($rawTextReportOtherDir);
        unlink($rawTextReportSameDir);
        unlink($rawTextReportOtherDir);


        //see if report written to same directory and other directory
        $gs->getQuickCheckReport($pdf, false, true);
        $gs->getQuickCheckReport($pdf, false, $jsonCallasReportOtherDir);
        $this->assertFileExists($jsonCallasReportSameDir);
        $this->assertFileExists($jsonCallasReportOtherDir);
        unlink($jsonCallasReportSameDir);
        unlink($jsonCallasReportOtherDir);

    }

    public function testPdfRipping()
    {
        $gs = new GhostscriptCommands();
        $gs->setGsPath($this->gsExe);
        $pdf = $this->tstSampleFilesDir . "For_The_Term_of_His_Natural_Life.pdf";
        $rnd = mt_rand(1000, 9999);
        $imgDir = $this->tstTmpDir . $rnd . DS;


        if (!$this->gsExe) {
            $this->assertEquals("Could not find GS executable!", false);
            return false;
        }

        $pages = [3, 8, 420];
        $resolution = 36;

        $ripOptions = [
            'format' => 'png',
            'colorspace' => 'colour',
            'resolution' => $resolution,
            'smoothing' => false,
            'pagebox' => 'MediaBox',
            'pagelist' => $pages,
            'outputfolder' => $imgDir,
        ];
        $images = $gs->savePdfAsImages($pdf, $ripOptions);

        $expected = [];
        foreach ($pages as $page) {
            $expected[] = $imgDir . "For_The_Term_of_His_Natural_Life_{$page}.png";
        }

        $this->assertEquals($expected, $images);

        foreach ($images as $image) {
            $this->assertFileExists($image);
            $img = imagecreatefrompng($image);
            $outputRes = imageresolution($img);
            $this->assertEquals([36, 36], $outputRes);
            unlink($image);
        }
        rmdir($imgDir);

    }

    public function testPdfSeparation()
    {
        $gs = new GhostscriptCommands();
        $gs->setGsPath($this->gsExe);
        $pdf = $this->tstSampleFilesDir . "001 SDI Iridesse Ink Swatches.pdf";
        $rnd = mt_rand(1000, 9999);
        $imgDir = $this->tstTmpDir . $rnd . DS;


        if (!$this->gsExe) {
            $this->assertEquals("Could not find GS executable!", false);
            return false;
        }

        $pages = [1, 2, 3, 4, 5, 6, 7, 8];
        $resolution = 256;

        $ripOptions = [
            'format' => 'tiff',
            'colorspace' => 'tiffsep',
            'resolution' => "{$resolution}x{$resolution}",
            'smoothing' => false,
            'pagebox' => 'MediaBox',
            'pagelist' => $pages,
            'outputfolder' => $imgDir,
        ];
        $images = $gs->savePdfAsImages($pdf, $ripOptions);

        //should have produced 53 separations
        $this->assertEquals(count($images), 53);

        foreach ($images as $image) {
            $this->assertFileExists($image);
            $outputSize = getimagesize($image);
            $this->assertEquals($resolution, max($outputSize));
            unlink($image);
        }
        rmdir($imgDir);

    }

}