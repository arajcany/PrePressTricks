<?php

use arajcany\PrePressTricks\Ticketing\XPIF\XpifTicket;

//create a ticket object based on a specific XPIF version
$ticket = new XpifTicket('2.082a');

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
$string = $ticket->render();
r($string);
