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
$string = $ticket->render();
dump($string);
