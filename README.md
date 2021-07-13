PrePress Tricks Library
=======================
Box of Tricks for common PrePress related tasks.

## Table of Contents

- [Purpose](#purpose)
- [Installation](#installation)
- [Basic Usage](#basic-usage---xpif-ticket-maker)

## Purpose

If you have ever asked any of the following question, this collection of Classes might be for you.

Pages Class

- I need to scale this picture. At what percent will it fit inside this box?

Boxes Class

- How do clean up a range of pages to something readable e.g. '3,4,6,12,13,10-20,1,2,8' => '1-4,6,8,10-20'?

Ticketing Classes

- How do i generate an XPIF ticket for Xerox Printing Devices?

## Installation

The recommended method of installation is via Composer.

```json
{
  "require": {
    "php": ">=7.0.0",
    "arajcany/pre-press-tricks": "*"
  }
}
```

## Basic Usage - XPIF Ticket Maker

### Example 1 - Base Ticket

```php
<?php
use arajcany\PrePressTricks\Ticketing\XPIF\XpifTicket;

/*
 * Probably the most basic ticket you can make.
 *
 * Notes:
 * Finishing values of
 *  28 = staple-dual-left
 *  92 = punch-4-hole
 *  93 = punch-left
 * The 'media' is defined as 'A4-White-80gsm' as my RIP has a stock catalogue with such defined
 *
 */

//create a ticket object based on a specific XPIF version
$ticket = new XpifTicket('2.082a');

//populate the ticket with properties
$ticket
    ->setJobName("For_the_Term_of_His_Natural_Life.pdf")
    ->setRequestingUserName("John Smith")
    ->setCopies(2)
    ->setFinishings([28, 93, 92])
    ->setSheetCollate('collated')
    ->setSides('two-sided-long-edge')
    ->setMedia('A4-White-80gsm');

//render the ticket (returns xml string AND optionally writes to filesystem)
$string = $ticket->render("C:\\tmp\\Example_01.xpf");
```

`$string` contains the following XML

```xml
<?xml version="1.0"?>
<!DOCTYPE xpif SYSTEM "xpif-v02082a.dtd">
<xpif version="1.0" cpss-version="2.082a" xml:lang="en">
    <xpif-operation-attributes>
        <job-name syntax="name" xml:space="preserve">For_the_Term_of_His_Natural_Life.pdf</job-name>
        <requesting-user-name syntax="name" xml:space="preserve">John Smith</requesting-user-name>
    </xpif-operation-attributes>
    <job-template-attributes>
        <copies syntax="integer">2</copies>
        <finishings syntax="1setOf">
            <value syntax="enum">28</value>
            <value syntax="enum">93</value>
            <value syntax="enum">92</value>
        </finishings>
        <sheet-collate syntax="keyword">collated</sheet-collate>
        <sides syntax="keyword">two-sided-long-edge</sides>
        <media syntax="keyword">A4-White-80gsm</media>
    </job-template-attributes>
</xpif>

```

### Example 2 - Base Ticket Supported Properties

The XPIF Specification supports almost 650 properties inside the ticket. You can control standard properties such as '
copies' to exotic properties such as 'rgb-monochrome-grayline-mapping'. I have selected a handful of the most commonly
used properties and created concrete methods around setting those properties. For settings rarely used properties, see
Example 3.

```php
<?php
//name of the job, typically the filename
$ticket->setJobName("For_the_Term_of_His_Natural_Life.pdf");

//name of user printing the job
$ticket->setRequestingUserName("Joe Citizen");

//number of copies
$ticket->setCopies(2);

//print only the pages within this range inclusive
$ticket->setPageRanges([1, 77]);

//the finishing values, you will need to know what each number represents
$ticket->setFinishings([28, 93, 92]);

//mime type
$ticket->setDocumentFormat("application/pdf");

//name of the stock as defined in the RIP or printer
$ticket->setMedia('Plain-White-A4-80gsm');

//print the job as 'color' │ 'monochrome-grayscale'
$ticket->setColorEffectsType('color');

//id for accounting / reconciliation purposes
$ticket->setJobAccountId('123');

//user id requesting job accounting data
$ticket->setJobAccountingUserId('AU004133');

//extra data that is useful for accounting and reconciliation purposes
$ticket->setJobAccountingData("From Web-2-Portal System A");

//name of the user that will receive the job
$ticket->setJobRecipientName("Jane Doe");

//message that is passed to the RIP or printer with the job, typically printed on a banner page
$ticket->setJobSheetMessage("Please refer to work ticket for additional instructions");

//message that is passed to the RIP or printer with the job, typically displayed to the operator
$ticket->setJobMessageToOperator("Urgent and fussy client");

//use the indicated orientation (portrait, landscape,  reverse-landscape, reverse-portrait)
$ticket->setOrientationRequested(3);

//job collation
$ticket->setSheetCollate("Collated");

//sides to print on 'one-sided' │ 'two-sided' │ 'two-sided-short-edge' │ 'two-sided-long-edge'
$ticket->setSides("one-sided");

//commonly used in forcing a page to print on the RHS for double sided printing (i.e. chapter starts)
$ticket->setForceFrontSide([1, 5, 9, 12]);

//render the ticket (returns xml string AND optionally writes to filesystem)
$string = $ticket->render("C:\\tmp\\Example_02.xpf");
```

### Example 3 - Setting Additional Properties

For setting properties that do not have a concrete methods, you can use the generic set `setProperty()` method.

```php
<?php
/*
 * The following examples achieve the same result
 */

//concrete method
$ticket->setJobName("For the Term of His Natural Life");
//generic
$ticket->setProperty("job-name", "For the Term of His Natural Life");

//concrete method
$ticket->setSides("one-sided");
//generic
$ticket->setProperty("sides", "one-sided");

//concrete method
$ticket->setSheetCollate("Collated");
//generic
$ticket->setProperty("sheet-collate", "Collated");


//example of other properties you might like to set
$ticket->setProperty("file-name", "For_the_Term_of_His_Natural_Life.pdf");
$ticket->setProperty("job-id-from-client", "D704432_SEQ001");
$ticket->setProperty("printer-resolution", "1200");
```

### Example 4 - Issues With Setting Additional Properties

The `setProperty()` may or may not work for a given property. This is because the code uses some smarts to try and
figure out the attributes of the property in question. This is done via the published `xpif.dtd` files (which I auto
convert to JSON format for easy reading).

Consider the following XPIF ticket

```xml
<?xml version="1.0"?>
<!DOCTYPE xpif SYSTEM "xpif-v02082a.dtd">
<xpif version="1.0" cpss-version="2.082a" xml:lang="en">
    <xpif-operation-attributes>
        <job-name syntax="name" xml:space="preserve">For_the_Term_of_His_Natural_Life.pdf</job-name>
        <requesting-user-name syntax="name" xml:space="preserve">John Smith</requesting-user-name>
    </xpif-operation-attributes>
    <job-template-attributes>
        <copies syntax="integer">2</copies>
        <finishings syntax="1setOf">
            <value syntax="enum">28</value>
            <value syntax="enum">93</value>
            <value syntax="enum">92</value>
        </finishings>
        <page-ranges syntax="1setOf">
            <value syntax="rangeOfInteger">
                <lower-bound syntax="integer">1</lower-bound>
                <upper-bound syntax="integer">50</upper-bound>
            </value>
        </page-ranges>
        <sheet-collate syntax="keyword">collated</sheet-collate>
        <sides syntax="keyword">two-sided-long-edge</sides>
        <media syntax="keyword">A4-White-80gsm</media>
        <pad-printing>
            <pad-printing-type syntax="keyword">pads-single-back-cover</pad-printing-type>
            <number-of-sheets-per-pad syntax="integer">50</number-of-sheets-per-pad>
        </pad-printing>
        <finishings-col>
            <finishings-media-sheets-min-max>
                <upper-bound syntax="integer">1</upper-bound>
                <lower-bound syntax="integer">50</lower-bound>
            </finishings-media-sheets-min-max>
        </finishings-col>
    </job-template-attributes>
</xpif>
```

**Issue 1 - Ambiguous Properties**  
If you try to set a 'lower-bound' property as follows, `$ticket->setProperty('lower-bound', 1)` are you setting it
for:  
`/xpif/job-template-attributes/finishings-col/finishings-media-sheets-min-max/lower-bound` or  
`/xpif/job-template-attributes/page-ranges/value/lower-bound`?

**Solution**  
Use dot-notation to explicitly define the path. Notice the dot-notation in the first argument:  
`$ticket->setProperty('finishings-media-sheets-min-max.lower-bound', 1)`  
This clearly defines which 'lower-bound' to use in the `xpif.dtd` file.

You could go all out and use the full xPath in dot-notation format  
`$ticket->setProperty('xpif.job-template-attributes.finishings-col.finishings-media-sheets-min-max.lower-bound', 1)`  
but it is rarely more than necessary to define the parent and grand-parent.

**Issue 2 - Undefined Children of 'Value'**  
For whatever reason, the Xerox `xpif.dtd` files do not publish what the valid child nodes of 'value' are. Looking at the
example XPIF above :  
`/xpif/job-template-attributes/finishings/value` contains text values 28, 93 and 92.  
`/xpif/job-template-attributes/page-ranges/value`  contains child nodes of 'lower-bound' and 'upper-bound'.  
This is super confusing as literally 'value' can contain anything, it is not defined in the xpif.dtd.

**Solution**  
Unfortunately, you just have to know the XPIF structure post 'value' of the following properties:

```
 xpif.job-template-attributes.finishings.value
 xpif.job-template-attributes.page-ranges.value
 xpif.job-template-attributes.force-front-side.value
 xpif.job-template-attributes.insert-sheet.value
 xpif.job-template-attributes.page-overrides.value
 xpif.job-template-attributes.job-save-disposition.save-info.value
 xpif.job-template-attributes.pages-per-subset.value
 xpif.job-template-attributes.finishings-col.stitching.stitching-locations.value
 xpif.job-template-attributes.finishings-col.creasing-col.crease-position-specifications-col.value
 xpif.job-template-attributes.resource-cleanup.value
 xpif.job-template-attributes.pdl-init-file.value
 xpif.job-template-attributes.forms-col.value
 xpif.job-template-attributes.job-offset.value
 xpif.job-template-attributes.form-save.form-save-info.value
 xpif.job-template-attributes.imposition-mark-front.value
 xpif.job-template-attributes.pcl-paper-source-col.value
 xpif.job-template-attributes.edge-enhancement-disable.value
 xpif.job-template-attributes.job-print-with-saved-jobs.value
 xpif.job-template-attributes.adjust-custom-color-col.value
 xpif.job-template-attributes.natural-language-adjustment-col.natural-language-adjustment-string.value
 xpif.job-template-attributes.fax-out-col.recipients-col.value
 xpif.job-template-attributes.output-gloss-col.value
 xpif.job-template-attributes.edge-enhancement-col.value
 xpif.job-template-attributes.black-enhancement-col.value
 xpif.job-template-attributes.colorant-set-col.colorant-col.value
 xpif.job-template-attributes.output-white-col.value
``` 

I have coded in some tricks that you can use:

```php
<?php
//concrete methods for 3 of the most common undefined children of value

//concrete method
$ticket->setFinishings([28, 93, 92]);
//generic equivalent
$ticket->setProperty('xpif.job-template-attributes.finishings.value.0', '28', ['syntax' => "enum"]);
$ticket->setProperty('xpif.job-template-attributes.finishings.value.1', '93', ['syntax' => "enum"]);
$ticket->setProperty('xpif.job-template-attributes.finishings.value.2', '92', ['syntax' => "enum"]);


//concrete method
$ticket->setPageRanges([1, 50]);
//generic equivalent
$ticket->setProperty('xpif.job-template-attributes.page-ranges.value.lower-bound', '1');
$ticket->setProperty('xpif.job-template-attributes.page-ranges.value.upper-bound', '50');


//concrete method
$ticket->setForceFrontSide([1, 10, 19, 32]);
//generic equivalent
$ticket->setProperty('xpif.job-template-attributes.force-front-side.value.0', '1', ['syntax' => "integer"]);
$ticket->setProperty('xpif.job-template-attributes.force-front-side.value.2', '10', ['syntax' => "integer"]);
$ticket->setProperty('xpif.job-template-attributes.force-front-side.value.3', '19', ['syntax' => "integer"]);
$ticket->setProperty('xpif.job-template-attributes.force-front-side.value.4', '32', ['syntax' => "integer"]);
```

### Example 5 - Handling Media and Media Collection

Depending on the RIP you use, you can either use Media or Media Collection:

**Media** Use this when your RIP has a defined media catalogue and you know the name of that media in the catalogue.

```php
<?php
use arajcany\PrePressTricks\Ticketing\XPIF\XpifTicket;

$ticket = new XpifTicket('2.082a');
$ticket->setJobName("For_the_Term_of_His_Natural_Life.pdf");
$ticket->setMedia('Plain-White-A4-80gsm');
```

```xml
<?xml version="1.0"?>
<!DOCTYPE xpif SYSTEM "xpif-v02082a.dtd">
<xpif version="1.0" cpss-version="2.082a" xml:lang="en">
    <xpif-operation-attributes>
        <job-name syntax="name" xml:space="preserve">For_the_Term_of_His_Natural_Life.pdf</job-name>
    </xpif-operation-attributes>
    <job-template-attributes>
        <media syntax="keyword">Plain-White-A4-80gsm</media>
    </job-template-attributes>
</xpif>
```

**Media Collection** Use this when your RIP does not have a defined media catalogue and you need to set the properties
of the stock.

```php
<?php
use arajcany\PrePressTricks\Ticketing\XPIF\XpifTicket;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifMediaCollection;

$mediaCollection = new XpifMediaCollection('2.082a');
$mediaCollection
    ->setMediaKey('plain-white-a4-80gsm')
    ->setMediaType('plain')
    ->setMediaInfo('This is our standard white paper')
    ->setMediaColor('white')
    ->setMediaPrePrinted('blank')
    ->setMediaHoleCount(0)
    ->setMediaOrderCount(1)
    ->setMediaSize([21000, 29700])
    ->setMediaWeightMetric(80)
    ->setMediaBackCoating('plain')
    ->setMediaFrontCoating('plain')
    ->setMediaRecycled('')
    ->setMediaDescription('')
    ->setMediaTooth('')
    ->setMediaGrain('')
    ->setMediaMaterial('')
    ->setMediaThickness('')
    ->setMediaSizeName('A4')
    ->setInputTray('')
    ->setTrayFeed('')
    ->setFeedOrientation('')
    ->setMediaMismatchPropertyPolicy('')
    ->setMediaMismatchSizePolicy('');
//note: the above is a complete list of concrete methods, you do not need to set all of them!

$ticket = new XpifTicket('2.082a');
$ticket->setJobName("For_the_Term_of_His_Natural_Life.pdf");
$ticket->setMediaCollection($mediaCollection);
```

```xml
<?xml version="1.0"?>
<!DOCTYPE xpif SYSTEM "xpif-v02082a.dtd">
<xpif version="1.0" cpss-version="2.082a" xml:lang="en">
    <xpif-operation-attributes>
        <job-name syntax="name" xml:space="preserve">For_the_Term_of_His_Natural_Life.pdf</job-name>
    </xpif-operation-attributes>
    <job-template-attributes>
        <media-col syntax="collection">
            <media-key syntax="keyword">plain-white-a4-80gsm</media-key>
            <media-type syntax="keyword">plain</media-type>
            <media-info syntax="text" xml:space="preserve">This is our standard white paper</media-info>
            <media-color syntax="keyword">white</media-color>
            <media-pre-printed syntax="keyword"/>
            <media-hole-count syntax="integer">0</media-hole-count>
            <media-order-count syntax="integer">1</media-order-count>
            <media-size syntax="collection">
                <x-dimension syntax="integer">21000</x-dimension>
                <y-dimension syntax="integer">29700</y-dimension>
            </media-size>
            <media-weight-metric syntax="integer">80</media-weight-metric>
            <media-back-coating syntax="keyword">plain</media-back-coating>
            <media-front-coating syntax="keyword">plain</media-front-coating>
            <media-recycled syntax="keyword"/>
            <media-description syntax="keyword"/>
            <media-tooth syntax="keyword"/>
            <media-grain syntax="keyword"/>
            <media-material syntax="keyword"/>
            <media-thickness syntax="integer"/>
            <media-size-name syntax="keyword">A4</media-size-name>
            <input-tray syntax="keyword"/>
            <tray-feed syntax="keyword"/>
            <feed-orientation syntax="keyword"/>
            <media-mismatch-property-policy syntax="keyword"/>
            <media-mismatch-size-policy syntax="keyword"/>
        </media-col>
    </job-template-attributes>
</xpif>
```

Media Collections are awesome because you can clone/modify/reuse them:

```php
<?php
use arajcany\PrePressTricks\Ticketing\XPIF\XpifMediaCollection;

$whiteMediaCollection = new XpifMediaCollection('2.082a');
$whiteMediaCollection
    ->setMediaType('plain')
    ->setMediaColor('white')
    ->setMediaPrePrinted(false)
    ->setMediaSize([21000, 29700])
    ->setMediaWeightMetric(80);

$pinkMediaCollection = (clone $whiteMediaCollection)->setMediaColor('pink');
$greenMediaCollection = (clone $whiteMediaCollection)->setMediaColor('green');
$blueMediaCollection = (clone $whiteMediaCollection)->setMediaColor('blue');
$yellowMediaCollection = (clone $whiteMediaCollection)->setMediaColor('yellow');

$blueCoverMediaCollection = (clone $blueMediaCollection)->setMediaWeightMetric(200);
$greenCoverMediaCollection = (clone $greenMediaCollection)->setMediaWeightMetric(200);
```

To see how to reuse Media Collections refer to [Example 6](#example-6---collections).

### Example 6 - Other Collections

In addition to the Media Collection the following collections are also available:

- Front Cover Collection
- Back Cover Collection
- Insert Sheet Collection
- Page Overrides Collection

```php
<?php
use arajcany\PrePressTricks\Ticketing\XPIF\XpifCoverFrontCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifCoverBackCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifInsertSheetCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifPageOverridesCollection;

//front cover collection
$coverFrontCollection = new XpifCoverFrontCollection('2.082a');
$coverFrontCollection
    ->setCoverType('print-both')
    ->setMediaCollection($blueCoverMediaCollection) //we created $blueCoverMedia in Example 5
    ->setMedia('the-name-of-the-stock'); //not necessary if you use setMediaCollection() 

//back cover collection
$coverBackCollection = new XpifCoverBackCollection('2.082a');
$coverBackCollection
    ->setCoverType('print-both')
    ->setMediaCollection($greenCoverMediaCollection) //we created $greenCoverMedia in Example 5
    ->setMedia('the-name-of-the-stock'); //not necessary if you use setMediaCollection() 

//insert sheet collection
$pinkInsertSheetCollection = new XpifInsertSheetCollection('2.082a');
$pinkInsertSheetCollection
    ->setInsertAfterPageNumber('0')
    ->setInsertCount(1)
    ->setMediaCollection($pinkMediaCollection) //we created $pinkMediaCollection in Example 5
    ->setMedia('the-name-of-the-stock'); //not necessary if you use setMediaCollection() 

//page overrides collection
$yellowPageOverridesCollection = new XpifPageOverridesCollection('2.082a');
$yellowPageOverridesCollection
    ->setPages('1-5')
    ->setMediaCollection($yellowMediaCollection) //we created $yellowMediaCollection in Example 5
    ->setMedia('the-name-of-the-stock'); //not necessary if you use setMediaCollection() 


//insert the above 4 collections into your xpif ticket with the following 4 concrete methods
$ticket->setCoverFrontCollection($coverFrontCollection);
$ticket->setCoverBackCollection($coverBackCollection);
$ticket->setInsertSheetCollection($pinkInsertSheetCollection);
$ticket->setPageOverridesCollection($yellowPageOverridesCollection);


```

### Example 7 - Using Collections In Your XPIF Tickets (the kitchen sink)

In case you got lost in Example 6 about Media Collections, here is a visual on where Media Collections can sit inside an
XPIF Ticket:

```
xpif
├── Media Collection
│
├── Cover Front Collection
│   └── Media Collection
│
├── Cover Back Collection
│   └── Media Collection
│
├── Slip Sheets (many)
│   ├── Insert Sheet Collection
│   │   └── Media Collection
│   ├── Insert Sheet Collection
│   │   └── Media Collection
│   │
│   ├── <...>
│   │
│   └── Insert Sheet Collection
│       └── Media Collection
│
└── Page Exceptions (many)
    ├── Page Overrides Collection
    │   └── Media Collection
    ├── Page Overrides Collection
    │   └── Media Collection
    │
    ├── <...>
    │
    └── Page Overrides Collection
        └── Media Collection
```

From the representation above, you can probably see why I said Collections are awesome:

- They allow you to write less code.
- You don't need to know the full xPath of the property you are trying to set - done automatically by the Collection.
- Create a base Collection then clone/modify/reuse that Collection in different parts of the ticket.

So here is a Collections kitchen sink example, I imagine that this will cover about 90% of what you need to do with xpif
ticketing. This kitchen sink example is based on the historical fiction "For The Term of His Natural Life" by Marcus
Clarke (public domain). You can download a copy of this classic novel
here https://adc.library.usyd.edu.au/data-2/p00023.pdf

**The PDF has the following properties:**

- The PDF has 433 pages
- The PDF has 8 has parts
    - TITLE & IMPRINT
    - PREFACE
    - PROLOGUE
    - BOOK I.—THE SEA. 1827.
    - BOOK II.—MACQUARIE HARBOUR. 1833.
    - BOOK III. PORT ARTHUR 1838.
    - BOOK IV. NORFOLK ISLAND 1846.
    - EPILOGUE
- Each of the above BOOKS has several chapters

**I want the xpif to print the PDF as follows:**

- Printed double-sided on A4 white 80gsm paper
- 4 hole punched as it needs to be inserted into a binder
- FRONT COVER is the first page of the PDF, printed single-sided on A4 blue 200gsm card
- Print the IMPRINT page single-sided on A4 white 80gsm paper
- Print the PREFACE on A4 pink 80gsm paper
- For the PROLOGUE, EPILOGUE and each of 4 BOOKS, insert an A4 yellow 200gsm divider card
- For each of the CHAPTERS, print the first 2 pdf-pages on A4 pink 80gsm paper
- BACK COVER required, use a piece of A4 blue 200gsm card (i.e. no printing)

```php
<?php
use arajcany\PrePressTricks\Ticketing\XPIF\XpifTicket;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifMediaCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifCoverFrontCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifCoverBackCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifInsertSheetCollection;
use arajcany\PrePressTricks\Ticketing\XPIF\XpifPageOverridesCollection;

$titleStart = '1';
$imprintStart = '2';
$prefaceStart = '3';
$prologueStart = '4';
$bookStarts ='13,79,173,331';
$chapterStarts ='14,21,28,31,35,41,49,56,62,67,75,78,80,83,86,95,98,101,108,113,118,123,129,140,147,151,158,164,168,174,185,194,201,208,212,217,221,225,233,243,245,249,253,260,265,269,273,276,279,282,290,300,305,311,315,325,332,341,351,357,361,365,371,375,379,383,389,392,397,402,410,415,418,423';
$epilogueStart = '428';

//Start by setting up a default white media collection.
$defaultWhiteMediaCollection = (new XpifMediaCollection('2.082a'))
    ->setMediaType('plain')
    ->setMediaColor('white')
    ->setMediaPrePrinted(false)
    ->setMediaSize([21000, 29700])
    ->setMediaWeightMetric(80);

//Create the other needed media collections by cloning and modifying
$blueCoverMediaCollection = (clone $defaultWhiteMediaCollection)->setMediaColor('blue')->setMediaWeightMetric(200);
$yellowDividerMediaCollection = (clone $defaultWhiteMediaCollection)->setMediaColor('yellow')->setMediaWeightMetric(200);
$pinkMediaCollection = (clone $defaultWhiteMediaCollection)->setMediaColor('pink');

//Create the exception and insert pages that don't require looping
$frontCoverException = (new XpifCoverFrontCollection())
    ->setCoverType('print-front')
    ->setMediaCollection($blueCoverMediaCollection);

$backCoverException = (new XpifCoverBackCollection())
    ->setCoverType('print-none')
    ->setMediaCollection($blueCoverMediaCollection);

$imprintException = (new XpifPageOverridesCollection())
    ->setPages($imprintStart)
    ->setSides('one-sided');

$prefaceException = (new XpifPageOverridesCollection())
    ->setPages($prefaceStart)
    ->setMediaCollection($pinkMediaCollection);
    
$prologueInsert = (new XpifInsertSheetCollection())
    ->setInsertBeforePageNumber($prologueStart)
    ->setMediaCollection($yellowDividerMediaCollection);
    
$epilogueInsert = (new XpifInsertSheetCollection())
    ->setInsertBeforePageNumber($epilogueStart)
    ->setMediaCollection($yellowDividerMediaCollection);

//Create the ticket and set the properties created so far
$ticket = (new XpifTicket('2.082a'))
  ->setJobName("For_the_Term_of_His_Natural_Life.pdf")
  ->setMediaCollection($defaultWhiteMediaCollection)
  ->setCoverFrontCollection($frontCoverException)
  ->setCoverBackCollection($backCoverException)
  ->setPageOverridesCollection($imprintException)
  ->setPageOverridesCollection($prefaceException)
  ->setInsertSheetCollection($prologueInsert)
  ->setInsertSheetCollection($epilogueInsert)
  ;

//we need to loop the book starts
foreach (explode(',', $bookStarts) as $bookStart) {
    $bookStartInsert = (new XpifInsertSheetCollection('2.082a'))
        ->setInsertBeforePageNumber($bookStart)
        ->setInsertCount(1)
        ->setMediaCollection($yellowDividerMediaCollection);

    //push into the xpif ticket
    $ticket = $ticket->setInsertSheetCollection($bookStartInsert);
}

//we need to loop the chapter starts
foreach (explode(',', $chapterStarts) as $chapterStart) {
    $chapterStartException = (new XpifPageOverridesCollection('2.082a'))
        ->setPages([$chapterStart, $chapterStart + 1])
        ->setMediaCollection($pinkMediaCollection);

    //push into the xpif ticket
    $ticket = $ticket->setPageOverridesCollection($chapterStartException);
}

$xpifXml = $ticket->render();
print_r($xpifXml);

```
