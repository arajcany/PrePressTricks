<?php

namespace arajcany\Test\Utility;

use arajcany\PrePressTricks\Ticketing\XPIF\XpifCoverBackCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifCoverFrontCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifInsertSheetCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifMediaCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifPageOverridesCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifTicket;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

/**
 * Class TimeMakerTest
 * @package phpUnitTutorial\Test
 *
 */
class XpifTest extends TestCase
{
    public $tstHomeDir;
    public $tstTmpDir;
    public $tstSampleTicketsDir;
    public $now;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->now = date("Y-m-d H:i:s");
        $this->tstHomeDir = str_replace("\\Ticketing\\XPIF", '', __DIR__) . DS;
        $this->tstTmpDir = __DIR__ . "\\..\\..\\..\\tmp\\";
        $this->tstSampleTicketsDir = __DIR__ . DS . "SampleTickets" . DS;
    }

    public function testMediaCollection()
    {
        $whiteMediaCollection = new XpifMediaCollection('2.082a');

        //standard defaults
        $whiteMediaCollection
            ->setMediaKey('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setMediaType('plain')
            ->setMediaDescription('This is our standard white A4 paper.')
            ->setMediaColor('white')
            ->setMediaPrePrinted('blank')
            ->setMediaHoleCount(0)
            ->setMediaOrderCount(1)
            ->setMediaSize([21000, 29700])
            ->setMediaWeightMetric(80)
            ->setMediaBackCoating('plain')
            ->setMediaFrontCoating('plain')
            ->setMediaRecycled('none')
            ->setMediaInfo('A4 White 80gsm')
            ->setMediaTooth('fine')
            ->setMediaGrain('x-grain')
            ->setMediaSizeName('A4')
            ->setMediaMaterial('keyword')
            ->setMediaThickness('integer')
            ->setInputTray('keyword')
            ->setTrayFeed('keyword')
            ->setFeedOrientation('keyword')
            ->setMediaMismatchPropertyPolicy('keyword')
            ->setMediaMismatchSizePolicy('keyword');

        $doc = new DOMDocument();
        $doc->loadXML($whiteMediaCollection->render());
        $xpath = new DOMXPath($doc);

        $tests = [
            [
                'q' => '//media-col/media-key',
                'r' => 'plain-white-a4-80gsm',
            ],
            [
                'q' => '//media-col/media-key/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//media-col/media-type',
                'r' => 'plain',
            ],
            [
                'q' => '//media-col/media-description',
                'r' => 'This is our standard white A4 paper.',
            ],
            [
                'q' => '//media-col/media-color',
                'r' => 'white',
            ],
            [
                'q' => '//media-col/media-pre-printed',
                'r' => 'blank',
            ],
            [
                'q' => '//media-col/media-hole-count',
                'r' => '0',
            ],
            [
                'q' => '//media-col/media-order-count',
                'r' => '1',
            ],
            [
                'q' => '//media-col/media-size/x-dimension',
                'r' => '21000',
            ],
            [
                'q' => '//media-col/media-size/y-dimension',
                'r' => '29700',
            ],
            [
                'q' => '//media-col/media-weight-metric',
                'r' => 80,
            ],
            [
                'q' => '//media-col/media-back-coating',
                'r' => 'plain',
            ],
            [
                'q' => '//media-col/media-front-coating',
                'r' => 'plain',
            ],
            [
                'q' => '//media-col/media-recycled',
                'r' => 'none',
            ],
            [
                'q' => '//media-col/media-info',
                'r' => 'A4 White 80gsm',
            ],
            [
                'q' => '//media-col/media-tooth',
                'r' => 'fine',
            ],
            [
                'q' => '//media-col/media-grain',
                'r' => 'x-grain',
            ],
            [
                'q' => '//media-col/media-size-name',
                'r' => 'A4',
            ],
            [
                'q' => '//media-col/media-material',
                'r' => 'keyword',
            ],
            [
                'q' => '//media-col/media-thickness',
                'r' => 'integer',
            ],
            [
                'q' => '//media-col/input-tray',
                'r' => 'keyword',
            ],
            [
                'q' => '//media-col/tray-feed',
                'r' => 'keyword',
            ],
            [
                'q' => '//media-col/feed-orientation',
                'r' => 'keyword',
            ],
            [
                'q' => '//media-col/media-mismatch-property-policy',
                'r' => 'keyword',
            ],
            [
                'q' => '//media-col/media-mismatch-size-policy',
                'r' => 'keyword',
            ],
        ];

        foreach ($tests as $test) {
            $entries = $xpath->query($test['q']);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }


        //alter some of the defaults
        $whiteMediaCollection
            ->setMediaColor('fuchsia', ['syntax' => 'name', 'this' => 'that'])
            ->setMediaPrePrinted('draft', ['syntax' => 'name', 'foo' => 'bar']);

        $doc = new DOMDocument();
        $doc->loadXML($whiteMediaCollection->render());
        $xpath = new DOMXPath($doc);

        $tests = [
            [
                'q' => '//media-col/media-color',
                'r' => 'fuchsia',
            ],
            [
                'q' => '//media-col/media-color/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//media-col/media-color/@this',
                'r' => 'that',
            ],
            [
                'q' => '//media-col/media-pre-printed',
                'r' => 'draft',
            ],
            [
                'q' => '//media-col/media-pre-printed/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//media-col/media-pre-printed/@foo',
                'r' => 'bar',
            ],
        ];

        foreach ($tests as $test) {
            $entries = $xpath->query($test['q']);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }
    }

    public function testFrontCoverCollection()
    {
        $mediaCollection = new XpifMediaCollection('2.082a');
        $mediaCollection
            ->setMediaType('plain')
            ->setMediaColor('white')
            ->setMediaSize([21000, 29700])
            ->setMediaWeightMetric(80);

        $coverFrontCollection = new XpifCoverFrontCollection('2.082a');
        $coverFrontCollection
            ->setMediaCollection($mediaCollection)
            ->setMedia('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setCoverType('print-both');

        $doc = new DOMDocument();
        $doc->loadXML($coverFrontCollection->render());
        $xpath = new DOMXPath($doc);

        $tests = [
            [
                'q' => '//cover-front/media-col/media-type',
                'r' => 'plain',
            ],
            [
                'q' => '//cover-front/media-col/media-color',
                'r' => 'white',
            ],
            [
                'q' => '//cover-front/media-col/media-size/x-dimension',
                'r' => '21000',
            ],
            [
                'q' => '//cover-front/media-col/media-size/y-dimension',
                'r' => '29700',
            ],
            [
                'q' => '//cover-front/media-col/media-weight-metric',
                'r' => 80,
            ],
            [
                'q' => '//cover-front/media',
                'r' => 'plain-white-a4-80gsm',
            ],
            [
                'q' => '//cover-front/media/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//cover-front/cover-type',
                'r' => 'print-both',
            ],
        ];

        foreach ($tests as $test) {
            $entries = $xpath->query($test['q']);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }

    }

    public function testBackCoverCollection()
    {
        $mediaCollection = new XpifMediaCollection('2.082a');
        $mediaCollection
            ->setMediaType('plain')
            ->setMediaColor('white')
            ->setMediaSize([21000, 29700])
            ->setMediaWeightMetric(80);

        $coverBackCollection = new XpifCoverBackCollection('2.082a');
        $coverBackCollection
            ->setMediaCollection($mediaCollection)
            ->setMedia('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setCoverType('print-both');

        $doc = new DOMDocument();
        $doc->loadXML($coverBackCollection->render());
        $xpath = new DOMXPath($doc);

        $tests = [
            [
                'q' => '//cover-back/media-col/media-type',
                'r' => 'plain',
            ],
            [
                'q' => '//cover-back/media-col/media-color',
                'r' => 'white',
            ],
            [
                'q' => '//cover-back/media-col/media-size/x-dimension',
                'r' => '21000',
            ],
            [
                'q' => '//cover-back/media-col/media-size/y-dimension',
                'r' => '29700',
            ],
            [
                'q' => '//cover-back/media-col/media-weight-metric',
                'r' => 80,
            ],
            [
                'q' => '//cover-back/media',
                'r' => 'plain-white-a4-80gsm',
            ],
            [
                'q' => '//cover-back/media/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//cover-back/cover-type',
                'r' => 'print-both',
            ],
        ];

        foreach ($tests as $test) {
            $entries = $xpath->query($test['q']);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }

    }

    public function testInsertSheetCollection()
    {
        $mediaCollection = new XpifMediaCollection('2.082a');
        $mediaCollection
            ->setMediaType('plain')
            ->setMediaColor('white')
            ->setMediaSize([21000, 29700])
            ->setMediaWeightMetric(80);

        $insertSheetCollection = new XpifInsertSheetCollection('2.082a');
        $insertSheetCollection
            ->setMediaCollection($mediaCollection)
            ->setMedia('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setInsertAfterPageNumber(0)
            ->setInsertCount(1);

        $doc = new DOMDocument();
        $doc->loadXML($insertSheetCollection->render());
        $xpath = new DOMXPath($doc);

        $tests = [
            [
                'q' => '//insert-sheet/value/media-col/media-type',
                'r' => 'plain',
            ],
            [
                'q' => '//insert-sheet/value/media-col/media-color',
                'r' => 'white',
            ],
            [
                'q' => '//insert-sheet/value/media-col/media-size/x-dimension',
                'r' => '21000',
            ],
            [
                'q' => '//insert-sheet/value/media-col/media-size/y-dimension',
                'r' => '29700',
            ],
            [
                'q' => '//insert-sheet/value/media-col/media-weight-metric',
                'r' => 80,
            ],
            [
                'q' => '//insert-sheet/value/media',
                'r' => 'plain-white-a4-80gsm',
            ],
            [
                'q' => '//insert-sheet/value/media/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//insert-sheet/value/insert-after-page-number',
                'r' => '0',
            ],
            [
                'q' => '//insert-sheet/value/insert-count',
                'r' => '1',
            ],
        ];

        foreach ($tests as $test) {
            //print_r("\r\n" . $test['q']);
            $entries = $xpath->query($test['q']);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }

    }

    public function testPageOverridesCollection()
    {
        $mediaCollection = new XpifMediaCollection('2.082a');
        $mediaCollection
            ->setMediaType('plain')
            ->setMediaColor('white')
            ->setMediaSize([21000, 29700])
            ->setMediaWeightMetric(80);

        $pageOverridesCollection = new XpifPageOverridesCollection('2.082a');
        $pageOverridesCollection
            ->setMediaCollection($mediaCollection)
            ->setMedia('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setSides('one-sided')
            ->setPages([20, 24])
            ->setColorEffectsType('monochrome-grayscale')
            ->setPageRotation('rotate-0');

        $doc = new DOMDocument();
        $doc->loadXML($pageOverridesCollection->render());
        $xpath = new DOMXPath($doc);

        $tests = [
            [
                'q' => '//page-overrides/value/media-col/media-type',
                'r' => 'plain',
            ],
            [
                'q' => '//page-overrides/value/media-col/media-color',
                'r' => 'white',
            ],
            [
                'q' => '//page-overrides/value/media-col/media-size/x-dimension',
                'r' => '21000',
            ],
            [
                'q' => '//page-overrides/value/media-col/media-size/y-dimension',
                'r' => '29700',
            ],
            [
                'q' => '//page-overrides/value/media-col/media-weight-metric',
                'r' => 80,
            ],
            [
                'q' => '//page-overrides/value/media',
                'r' => 'plain-white-a4-80gsm',
            ],
            [
                'q' => '//page-overrides/value/media/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//page-overrides/value/sides',
                'r' => 'one-sided',
            ],
            [
                'q' => '//page-overrides/value/pages/value/lower-bound',
                'r' => '20',
            ],
            [
                'q' => '//page-overrides/value/pages/value/upper-bound',
                'r' => '24',
            ],
            [
                'q' => '//page-overrides/value/color-effects-type',
                'r' => 'monochrome-grayscale',
            ],
            [
                'q' => '//page-overrides/value/page-rotation',
                'r' => 'rotate-0',
            ],
        ];

        foreach ($tests as $test) {
            //print_r("\r\n" . $test['q']);
            $entries = $xpath->query($test['q']);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }

    }

    public function testXpifTicket()
    {
        $xpifCollection = new XpifTicket('2.082a');
        $xpifCollection->setJobName("For_the_Term_of_His_Natural_Life.pdf");
        $xpifCollection->setRequestingUserName("Joe Citizen");
        $xpifCollection->setCopies(2);
        $xpifCollection->setPageRanges([1, 433]);
        $finishings = [28, 93, 92];
        $xpifCollection->setFinishings($finishings);
        $xpifCollection->setDocumentFormat("application/pdf");
        $xpifCollection->setMedia('plain-white-a4-80gsm', ['syntax' => 'name']);
        $xpifCollection->setColorEffectsType('color');
        $xpifCollection->setJobAccountId('123');
        $xpifCollection->setJobAccountingUserId('AU004133');
        $xpifCollection->setJobAccountingData("From Web-2-Portal System A");
        $xpifCollection->setJobRecipientName("Jane Doe");
        $xpifCollection->setJobSheetMessage("Please refer to work ticket for additional instructions");
        $xpifCollection->setJobMessageToOperator("Urgent and fussy client");
        $xpifCollection->setOrientationRequested(3);
        $xpifCollection->setSheetCollate("collated");
        $xpifCollection->setSides("one-sided");
        $forceFrontSides = [1, 5, 9, 12];
        $xpifCollection->setForceFrontSide($forceFrontSides);

        $doc = new DOMDocument();
        $doc->loadXML($xpifCollection->render());
        $xpath = new DOMXPath($doc);

        $tests = [
            [
                'q' => '//xpif/xpif-operation-attributes/job-name',
                'r' => "For_the_Term_of_His_Natural_Life.pdf",
            ],
            [
                'q' => '//xpif/xpif-operation-attributes/requesting-user-name',
                'r' => "Joe Citizen",
            ],
            [
                'q' => '//xpif/job-template-attributes/copies',
                'r' => "2",
            ],
            [
                'q' => '//xpif/job-template-attributes/page-ranges/value/lower-bound',
                'r' => "1",
            ],
            [
                'q' => '//xpif/job-template-attributes/page-ranges/value/upper-bound',
                'r' => "433",
            ],
            [
                'q' => '//xpif/xpif-operation-attributes/document-format',
                'r' => "application/pdf",
            ],
            [
                'q' => '//xpif/job-template-attributes/media',
                'r' => "plain-white-a4-80gsm",
            ],
            [
                'q' => '//xpif/job-template-attributes/media/@syntax',
                'r' => "name",
            ],
            [
                'q' => '//xpif/job-template-attributes/color-effects-type',
                'r' => "color",
            ],
            [
                'q' => '//xpif/job-template-attributes/job-account-id',
                'r' => "123",
            ],
            [
                'q' => '//xpif/job-template-attributes/job-accounting-user-id',
                'r' => "AU004133",
            ],
            [
                'q' => '//xpif/job-template-attributes/job-accounting-data',
                'r' => "From Web-2-Portal System A",
            ],
            [
                'q' => '//xpif/job-template-attributes/job-recipient-name',
                'r' => "Jane Doe",
            ],
            [
                'q' => '//xpif/job-template-attributes/job-sheet-message',
                'r' => "Please refer to work ticket for additional instructions",
            ],
            [
                'q' => '//xpif/job-template-attributes/job-sheet-message/@syntax',
                'r' => "text",
            ],
            [
                'q' => '//xpif/job-template-attributes/job-message-to-operator',
                'r' => "Urgent and fussy client",
            ],
            [
                'q' => '//xpif/job-template-attributes/job-message-to-operator/@syntax',
                'r' => "text",
            ],
            [
                'q' => '//xpif/job-template-attributes/orientation-requested',
                'r' => "3",
            ],
            [
                'q' => '//xpif/job-template-attributes/sheet-collate',
                'r' => "collated",
            ],
            [
                'q' => '//xpif/job-template-attributes/sides',
                'r' => "one-sided",
            ],
        ];

        foreach ($tests as $test) {
            //print_r("\r\n" . $test['q']);
            $entries = $xpath->query($test['q']);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }


        foreach ($finishings as $k => $finishing) {
            $tests = [
                [
                    'q' => '//xpif/job-template-attributes/finishings/value',
                    'r' => $finishing,
                ],

            ];

            foreach ($tests as $test) {
                //print_r("\r\n" . $test['q']);
                $entries = $xpath->query($test['q']);
                $this->assertEquals($test['r'], $entries->item($k)->nodeValue);
            }
        }


        foreach ($forceFrontSides as $k => $forceFrontSide) {
            $tests = [
                [
                    'q' => '//xpif/job-template-attributes/force-front-side/value',
                    'r' => $forceFrontSide,
                ],

            ];

            foreach ($tests as $test) {
                //print_r("\r\n" . $test['q']);
                $entries = $xpath->query($test['q']);
                $this->assertEquals($test['r'], $entries->item($k)->nodeValue);
            }
        }

    }

    public function testXpifTicketWithSubCollections()
    {
        $mediaCollection = new XpifMediaCollection('2.082a');
        $mediaCollection
            ->setMediaType('plain')
            ->setMediaColor('white')
            ->setMediaSize([21000, 29700])
            ->setMediaWeightMetric(80);

        $pageOverridesCollection = new XpifPageOverridesCollection('2.082a');
        $pageOverridesCollection
            ->setMediaCollection($mediaCollection)
            ->setMedia('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setSides('one-sided')
            ->setColorEffectsType('monochrome-grayscale')
            ->setPageRotation('rotate-0');

        $insertSheetCollection = new XpifInsertSheetCollection('2.082a');
        $insertSheetCollection
            ->setMediaCollection($mediaCollection)
            ->setMedia('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setInsertCount(1);

        $coverBackCollection = new XpifCoverBackCollection('2.082a');
        $coverBackCollection
            ->setMediaCollection($mediaCollection)
            ->setMedia('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setCoverType('print-none');

        $coverFrontCollection = new XpifCoverFrontCollection('2.082a');
        $coverFrontCollection
            ->setMediaCollection($mediaCollection)
            ->setMedia('plain-white-a4-80gsm', ['syntax' => 'name'])
            ->setCoverType('print-none');


        $xpifCollection = new XpifTicket('2.082a');
        $xpifCollection
            ->setMediaCollection($mediaCollection)
            ->setCoverFrontCollection($coverFrontCollection)
            ->setCoverBackCollection($coverBackCollection);

        //set a few insert sheets
        $insertPageNumbers = [0, 12, 78, 172, 330];
        foreach ($insertPageNumbers as $num) {
            $xpifCollection->setInsertSheetCollection($insertSheetCollection->setInsertAfterPageNumber($num));
        }

        //set a few page overrides
        $overridePageNumbers = [1, 13, 79, 173, 331];
        foreach ($overridePageNumbers as $num) {
            $xpifCollection->setPageOverridesCollection($pageOverridesCollection->setPages([$num, $num]));
        }


        $doc = new DOMDocument();
        $doc->loadXML($xpifCollection->render());
        $xpath = new DOMXPath($doc);

        //media collection
        $tests = [
            [
                'q' => '//media-col/media-type',
                'r' => 'plain',
            ],
            [
                'q' => '//media-col/media-color',
                'r' => 'white',
            ],
            [
                'q' => '//media-col/media-size/x-dimension',
                'r' => '21000',
            ],
            [
                'q' => '//media-col/media-size/y-dimension',
                'r' => '29700',
            ],
            [
                'q' => '//media-col/media-weight-metric',
                'r' => 80,
            ],
        ];

        foreach ($tests as $test) {
            $q = str_replace('//', '//xpif/job-template-attributes/', $test['q']);
            //print_r("\r\n" . $q);
            $entries = $xpath->query($q);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }


        //font cover
        $tests = [
            [
                'q' => '//cover-front/media-col/media-type',
                'r' => 'plain',
            ],
            [
                'q' => '//cover-front/media-col/media-color',
                'r' => 'white',
            ],
            [
                'q' => '//cover-front/media-col/media-size/x-dimension',
                'r' => '21000',
            ],
            [
                'q' => '//cover-front/media-col/media-size/y-dimension',
                'r' => '29700',
            ],
            [
                'q' => '//cover-front/media-col/media-weight-metric',
                'r' => 80,
            ],
            [
                'q' => '//cover-front/media',
                'r' => 'plain-white-a4-80gsm',
            ],
            [
                'q' => '//cover-front/media/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//cover-front/cover-type',
                'r' => 'print-none',
            ],
        ];

        foreach ($tests as $test) {
            $q = str_replace('//', '//xpif/job-template-attributes/', $test['q']);
            //print_r("\r\n" . $q);
            $entries = $xpath->query($q);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }


        //back cover
        $tests = [
            [
                'q' => '//cover-back/media-col/media-type',
                'r' => 'plain',
            ],
            [
                'q' => '//cover-back/media-col/media-color',
                'r' => 'white',
            ],
            [
                'q' => '//cover-back/media-col/media-size/x-dimension',
                'r' => '21000',
            ],
            [
                'q' => '//cover-back/media-col/media-size/y-dimension',
                'r' => '29700',
            ],
            [
                'q' => '//cover-back/media-col/media-weight-metric',
                'r' => 80,
            ],
            [
                'q' => '//cover-back/media',
                'r' => 'plain-white-a4-80gsm',
            ],
            [
                'q' => '//cover-back/media/@syntax',
                'r' => 'name',
            ],
            [
                'q' => '//cover-back/cover-type',
                'r' => 'print-none',
            ],
        ];

        foreach ($tests as $test) {
            $q = str_replace('//', '//xpif/job-template-attributes/', $test['q']);
            //print_r("\r\n" . $q);
            $entries = $xpath->query($q);
            $this->assertEquals($test['r'], $entries->item(0)->nodeValue);
        }


        //insert sheet
        foreach ($insertPageNumbers as $k => $num) {
            $tests = [
                [
                    'q' => '//insert-sheet/value/media-col/media-type',
                    'r' => 'plain',
                ],
                [
                    'q' => '//insert-sheet/value/media-col/media-color',
                    'r' => 'white',
                ],
                [
                    'q' => '//insert-sheet/value/media-col/media-size/x-dimension',
                    'r' => '21000',
                ],
                [
                    'q' => '//insert-sheet/value/media-col/media-size/y-dimension',
                    'r' => '29700',
                ],
                [
                    'q' => '//insert-sheet/value/media-col/media-weight-metric',
                    'r' => 80,
                ],
                [
                    'q' => '//insert-sheet/value/media',
                    'r' => 'plain-white-a4-80gsm',
                ],
                [
                    'q' => '//insert-sheet/value/media/@syntax',
                    'r' => 'name',
                ],
                [
                    'q' => '//insert-sheet/value/insert-after-page-number',
                    'r' => $num,
                ],
                [
                    'q' => '//insert-sheet/value/insert-count',
                    'r' => '1',
                ],
            ];

            foreach ($tests as $test) {
                $q = str_replace('//', '//xpif/job-template-attributes/', $test['q']);
                //print_r("\r\n" . $q);
                $entries = $xpath->query($q);
                $this->assertEquals($test['r'], $entries->item($k)->nodeValue);
            }
        }

        //page overrides
        foreach ($overridePageNumbers as $k => $num) {
            $tests = [
                [
                    'q' => '//page-overrides/value/media-col/media-type',
                    'r' => 'plain',
                ],
                [
                    'q' => '//page-overrides/value/media-col/media-color',
                    'r' => 'white',
                ],
                [
                    'q' => '//page-overrides/value/media-col/media-size/x-dimension',
                    'r' => '21000',
                ],
                [
                    'q' => '//page-overrides/value/media-col/media-size/y-dimension',
                    'r' => '29700',
                ],
                [
                    'q' => '//page-overrides/value/media-col/media-weight-metric',
                    'r' => 80,
                ],
                [
                    'q' => '//page-overrides/value/media',
                    'r' => 'plain-white-a4-80gsm',
                ],
                [
                    'q' => '//page-overrides/value/media/@syntax',
                    'r' => 'name',
                ],
                [
                    'q' => '//page-overrides/value/sides',
                    'r' => 'one-sided',
                ],
                [
                    'q' => '//page-overrides/value/pages/value/lower-bound',
                    'r' => $num,
                ],
                [
                    'q' => '//page-overrides/value/pages/value/upper-bound',
                    'r' => $num,
                ],
                [
                    'q' => '//page-overrides/value/color-effects-type',
                    'r' => 'monochrome-grayscale',
                ],
                [
                    'q' => '//page-overrides/value/page-rotation',
                    'r' => 'rotate-0',
                ],
            ];

            foreach ($tests as $test) {
                $q = str_replace('//', '//xpif/job-template-attributes/', $test['q']);
                //print_r("\r\n" . $q);
                $entries = $xpath->query($q);
                $this->assertEquals($test['r'], $entries->item($k)->nodeValue);
            }
        }

    }

}
