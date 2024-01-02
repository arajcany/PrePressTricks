<?php

namespace arajcany\PrePressTricks\Ticketing\XPIF;

use Cake\Utility\Xml;
use Exception;

/**
 * Class XpifTicket
 * Create XPIF Tickets for DFE's
 *
 * @package arajcany\PrePressTricks\Ticketing
 */
class XpifTicket extends XpifBase
{
    /**
     * XpifTicket constructor.
     *
     * @param null $cpss_version
     * @param string $lang
     * @throws Exception
     * @internal param $dtd
     */
    function __construct($cpss_version = null, $lang = "en")
    {
        parent::__construct($cpss_version, $lang);

        $this->rawProperties = [
            'xpif' => [
                'xpif-operation-attributes' => [],
                'job-template-attributes' => [],
            ]
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        $this->_populateCollection();
        return $this->collection;
    }

    /**
     * Deliver the ticket as an XML string/file
     *
     * @param null $saveLocation
     * @return string
     */
    public function render($saveLocation = null)
    {
        //populate the ticket
        $this->_populateCollection();

        //main domDocument
        $mainDomDocument = new \DOMDocument('1.0', 'UTF-8');
        $mainDomDocument->preserveWhiteSpace = true;
        $mainDomDocument->formatOutput = true;
        $docType = (new \DOMImplementation())->createDocumentType('xpif', '', $this->dtd_version);
        $mainDomDocument->appendChild($docType);

        //xpif domDocument
        $defaults = [
            'return' => 'domdocument',
            'loadEntities' => false,
            'readFile' => true,
            'parseHuge' => false,
        ];
        $xpifDomDocument = Xml::build($this->collection, $defaults);

        //import xpif domDocument into main domDocument
        $mainDomDocument->appendChild(
            $mainDomDocument->importNode($xpifDomDocument->documentElement, true)
        );

        //convert to XML string
        $xmlString = $mainDomDocument->saveXML();

        //save string
        if ($saveLocation) {
            $savePath = pathinfo($saveLocation, PATHINFO_DIRNAME);
            if (is_dir($savePath) . DIRECTORY_SEPARATOR) {
                if (is_writable($savePath) . DIRECTORY_SEPARATOR) {
                    file_put_contents($saveLocation, $xmlString);
                }
            }
        }

        //return string
        return $xmlString;
    }

    /**
     * Wrapper function to set a property
     *
     * @param $property_name
     * @param $property_value
     * @param array $attributes
     * @return XpifTicket
     */
    public function setProperty($property_name, $property_value, $attributes = [])
    {
        $this->_setProperty($property_name, $property_value, $attributes, 'xpif');
        return $this;
    }

    /**
     * @param string $job_name
     * @param array $attributes
     * @return XpifTicket
     */
    public function setJobName($job_name, $attributes = [])
    {
        $this->setProperty('job-name', $job_name, $attributes);
        return $this;
    }

    /**
     * @param string $requesting_user_name
     * @param array $attributes
     * @return XpifTicket
     */
    public function setRequestingUserName($requesting_user_name, $attributes = [])
    {
        $this->setProperty('requesting-user-name', $requesting_user_name, $attributes);
        return $this;
    }

    /**
     * @param integer $copies
     * @param array $attributes
     * @return XpifTicket
     */
    public function setCopies($copies, $attributes = [])
    {
        $this->setProperty('copies', $copies, $attributes);
        return $this;
    }

    /**
     * @param array|integer $page_ranges
     * @param array $attributes
     * @return XpifTicket
     */
    public function setPageRanges($page_ranges, $attributes = [])
    {
        if (is_int($page_ranges) || is_numeric($page_ranges)) {
            $page_ranges = [$page_ranges, $page_ranges];
        }

        $page_ranges_data = [
            '@syntax' => '1setOf',
            'value' => [
                '@syntax' => 'rangeOfInteger',
                'lower-bound' => [
                    '@syntax' => 'integer',
                    '@' => $page_ranges[0]
                ],
                'upper-bound' => [
                    '@syntax' => 'integer',
                    '@' => $page_ranges[1]
                ]
            ]
        ];

        $this->setProperty('page-ranges', $page_ranges_data, $attributes);
        return $this;
    }

    /**
     * @param string $color_effects_type
     * @param array $attributes
     * @return XpifTicket
     */
    public function setColorEffectsType($color_effects_type, $attributes = [])
    {
        $this->setProperty('color-effects-type', $color_effects_type, $attributes);
        return $this;
    }

    /**
     * @param string $document_format
     * @param array $attributes
     * @return XpifTicket
     */
    public function setDocumentFormat($document_format, $attributes = [])
    {
        $this->setProperty('document-format', $document_format, $attributes);
        return $this;
    }

    /**
     * @param int $job_account_id
     * @param array $attributes
     * @return XpifTicket
     */
    public function setJobAccountId($job_account_id, $attributes = [])
    {
        $this->setProperty('job-account-id', $job_account_id, $attributes);
        return $this;
    }

    /**
     * @param int $job_accounting_user_id
     * @param array $attributes
     * @return XpifTicket
     */
    public function setJobAccountingUserId($job_accounting_user_id, $attributes = [])
    {
        $this->setProperty('job-accounting-user-id', $job_accounting_user_id, $attributes);
        return $this;
    }

    /**
     * @param int $job_accounting_data
     * @param array $attributes
     * @return XpifTicket
     */
    public function setJobAccountingData($job_accounting_data, $attributes = [])
    {
        $this->setProperty('job-accounting-data', $job_accounting_data, $attributes);
        return $this;
    }

    /**
     * @param string $job_recipient_name
     * @param array $attributes
     * @return XpifTicket
     */
    public function setJobRecipientName($job_recipient_name, $attributes = [])
    {
        $this->setProperty('job-recipient-name', $job_recipient_name, $attributes);
        return $this;
    }

    /**
     * @param string $job_sheet_message
     * @param array $attributes
     * @return XpifTicket
     */
    public function setJobSheetMessage($job_sheet_message, $attributes = [])
    {
        $this->setProperty('job-sheet-message', $job_sheet_message, $attributes);
        return $this;
    }

    /**
     * @param string $job_message_to_operator
     * @param array $attributes
     * @return XpifTicket
     */
    public function setJobMessageToOperator($job_message_to_operator, $attributes = [])
    {
        $this->setProperty('job-message-to-operator', $job_message_to_operator, $attributes);
        return $this;
    }

    /**
     * @param integer|array $finishings
     * @param array $attributes
     * @return XpifTicket
     */
    public function setFinishings($finishings, $attributes = [])
    {
        if (is_numeric($finishings)) {
            $finishings = [$finishings];
        } elseif (is_string($finishings)) {
            $finishings = [$finishings];
        }

        $values['value'] = [];
        foreach ($finishings as $k => $finishing) {
            $values['value'][$k] = [
                '@syntax' => 'enum',
                '@' => $finishing
            ];
        }

        $this->setProperty('finishings', $values, $attributes);
        return $this;
    }

    /**
     * @param integer $orientation_requested
     * @param array $attributes
     * @return XpifTicket
     */
    public function setOrientationRequested($orientation_requested, $attributes = [])
    {
        $this->setProperty('orientation-requested', $orientation_requested, $attributes);
        return $this;
    }

    /**
     * @param string $sheet_collate
     * @param array $attributes
     * @return XpifTicket
     */
    public function setSheetCollate($sheet_collate, $attributes = [])
    {
        $this->setProperty('sheet-collate', $sheet_collate, $attributes);
        return $this;
    }

    /**
     * @param string $sides
     * @param array $attributes
     * @return XpifTicket
     */
    public function setSides($sides, $attributes = [])
    {
        $this->setProperty('sides', $sides, $attributes);
        return $this;
    }

    /**
     * @param string $rotation
     * @param array $attributes
     * @return XpifTicket
     */
    public function setPageRotation($rotation, $attributes = [])
    {
        $this->setProperty('page-rotation', $rotation, $attributes);
        return $this;
    }

    /**
     * @param string $media
     * @param array $attributes
     * @return XpifTicket
     */
    public function setMedia($media, $attributes = [])
    {
        $this->setProperty('media', $media, $attributes);
        return $this;
    }

    /**
     * @param XpifMediaCollection $media_collection
     * @return XpifTicket
     */
    public function setMediaCollection(XpifMediaCollection $media_collection)
    {
        $this->media_collection = clone $media_collection;
        return $this;
    }

    /**
     * @param XpifCoverFrontCollection $cover_collection
     * @return XpifTicket
     */
    public function setCoverFrontCollection(XpifCoverFrontCollection $cover_collection)
    {
        $this->cover_front = clone $cover_collection;
        return $this;
    }

    /**
     * @param XpifCoverBackCollection $cover_collection
     * @return XpifTicket
     */
    public function setCoverBackCollection(XpifCoverBackCollection $cover_collection)
    {
        $this->cover_back = clone $cover_collection;
        return $this;
    }

    /**
     * @param XpifPageOverridesCollection $page_overrides
     * @return XpifTicket
     */
    public function setPageOverridesCollection(XpifPageOverridesCollection $page_overrides)
    {
        $this->page_overrides[] = clone $page_overrides;
        return $this;
    }

    /**
     * @param XpifInsertSheetCollection $insert_sheet
     * @return XpifTicket
     * @internal param array $attributes
     */
    public function setInsertSheetCollection(XpifInsertSheetCollection $insert_sheet)
    {
        $this->insert_sheet[] = clone $insert_sheet;
        return $this;
    }

    /**
     * @param integer|array $force_front_side
     * @param array $attributes
     * @return XpifTicket
     */
    public function setForceFrontSide($force_front_side, $attributes = [])
    {
        if (is_numeric($force_front_side)) {
            $force_front_side = [$force_front_side];
        } elseif (is_string($force_front_side)) {
            $force_front_side = [$force_front_side];
        }

        $values['value'] = [];
        foreach ($force_front_side as $k => $pageNumber) {
            $values['value'][$k] = [
                '@syntax' => 'integer',
                '@' => $pageNumber
            ];
        }

        $this->setProperty('force-front-side', $values, $attributes);
        return $this;
    }

    /**
     * Base structure as an array
     *
     * @return array
     * @internal param $cpss_version
     * @internal param $lang
     */
    private function _getBaseArray()
    {
        $a = [
            'version' => '1.0',
            'cpss-version' => $this->cpss_version,
            'xml:lang' => $this->lang
        ];
        $element = $this->DTD->_buildElement('xpif', '', $a);
        $element = [
            'xpif' => $element
        ];

        return $element;
    }

    /**
     * Converts the raw properties into a well formed ticket
     */
    private function _populateCollection()
    {
        //base collection
        $this->collection = $this->_getBaseArray();

        //merge the simple properties into the collection
        $this->_mergeRawPropertiesIntoCollection();

        //merge the complex properties into the collection
        if ($this->media_collection) {
            $dataTmp = $this->media_collection->getCollection()['media-col'];
            $this->_mergeDataIntoCollection($dataTmp, 'xpif.job-template-attributes.media-col');
        }

        if ($this->cover_front) {
            $dataTmp = $this->cover_front->getCollection()['cover-front'];
            $this->_mergeDataIntoCollection($dataTmp, 'xpif.job-template-attributes.cover-front');
        }

        if ($this->cover_back) {
            $dataTmp = $this->cover_back->getCollection()['cover-back'];
            $this->_mergeDataIntoCollection($dataTmp, 'xpif.job-template-attributes.cover-back');
        }

        if ($this->insert_sheet) {
            //we need a copy as we modify it
            $insertSheets = $this->insert_sheet;

            //use the first insert-sheet as the basis (removing the value element)
            $dataTmp = reset($insertSheets)->getCollection();
            unset($dataTmp['insert-sheet']['value']);
            $this->_mergeDataIntoCollection($dataTmp, 'xpif.job-template-attributes');

            //loop and insert the value element
            $counter = 0;
            foreach ($insertSheets as $insertSheet) {
                $dataTmp = $insertSheet->getCollection()['insert-sheet']['value'];
                $this->_insertDataIntoCollection($dataTmp, "xpif.job-template-attributes.insert-sheet.value.$counter");
                $counter++;
            }
        }

        if ($this->page_overrides) {
            //we need a copy as we modify it
            $pageOverrides = $this->page_overrides;

            //use the first page-overrides as the basis (removing the value element)
            $dataTmp = reset($pageOverrides)->getCollection();
            unset($dataTmp['page-overrides']['value']);
            $this->_mergeDataIntoCollection($dataTmp, 'xpif.job-template-attributes');

            //loop and insert the value element
            $counter = 0;
            foreach ($pageOverrides as $pageOverride) {
                $dataTmp = $pageOverride->getCollection()['page-overrides']['value'];
                $this->_insertDataIntoCollection($dataTmp,
                    "xpif.job-template-attributes.page-overrides.value.$counter");
                $counter++;
            }
        }
    }

}
