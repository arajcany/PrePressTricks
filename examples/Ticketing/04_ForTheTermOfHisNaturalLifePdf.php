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
$bookStarts = '13,79,173,331';
$chapterStarts = '14,21,28,31,35,41,49,56,62,67,75,78,80,83,86,95,98,101,108,113,118,123,129,140,147,151,158,164,168,174,185,194,201,208,212,217,221,225,233,243,245,249,253,260,265,269,273,276,279,282,290,300,305,311,315,325,332,341,351,357,361,365,371,375,379,383,389,392,397,402,410,415,418,423';
$epilogueStart = '428';

//Start by setting up a default white media collection.
$defaultWhiteMediaCollection = (new XpifMediaCollection('2.082a'))
    ->setMediaType('stationery')
    ->setMediaColor('white')
    ->setMediaSize([21000, 29700])
    ->setMediaWeightMetric(80);

//Create the other needed media collections by cloning and modifying
$blueCoverMediaCollection = (clone $defaultWhiteMediaCollection)->setMediaColor('blue')->setMediaWeightMetric(200);
$yellowDividerMediaCollection = (clone $defaultWhiteMediaCollection)->setMediaColor('yellow')->setMediaWeightMetric(200);
$pinkMediaCollection = (clone $defaultWhiteMediaCollection)->setMediaColor('pink');

//Create the exception and insert pages that don't require looping
$frontCoverException = (new XpifCoverFrontCollection('2.082a'))
    ->setCoverType('print-front')
    ->setMediaCollection($blueCoverMediaCollection);

$backCoverException = (new XpifCoverBackCollection('2.082a'))
    ->setCoverType('print-none')
    ->setMediaCollection($blueCoverMediaCollection);

$imprintException = (new XpifPageOverridesCollection('2.082a'))
    ->setPages($imprintStart)
    ->setSides('one-sided');

$prefaceException = (new XpifPageOverridesCollection('2.082a'))
    ->setPages($prefaceStart)
    ->setMediaCollection($pinkMediaCollection);

$prologueInsert = (new XpifInsertSheetCollection('2.082a'))
    ->setInsertBeforePageNumber($prologueStart)
    ->setInsertCount(1)
    ->setMediaCollection($yellowDividerMediaCollection);

$epilogueInsert = (new XpifInsertSheetCollection('2.082a'))
    ->setInsertBeforePageNumber($epilogueStart)
    ->setInsertCount(1)
    ->setMediaCollection($yellowDividerMediaCollection);

//Create the ticket and set the properties created so far
$ticket = (new XpifTicket('2.082a'))
    ->setJobName("For_the_Term_of_His_Natural_Life.pdf")
    ->setMediaCollection($defaultWhiteMediaCollection)
    ->setFinishings(['92']) //punch-4-hole
    ->setSides('two-sided-long-edge')
    ->setCoverFrontCollection($frontCoverException)
    ->setCoverBackCollection($backCoverException)
    ->setPageOverridesCollection($imprintException)
    ->setPageOverridesCollection($prefaceException)
    ->setInsertSheetCollection($prologueInsert)
    ->setInsertSheetCollection($epilogueInsert);

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
r($xpifXml);