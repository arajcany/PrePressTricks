<?php

namespace arajcany\PrePressTricks\Ticketing\XPIF;

use Cake\Utility\Xml;

/**
 * Class XpifInsertSheetCollection
 *
 *
 * @package arajcany\PrePressTricks\Ticketing
 */
class XpifInsertSheetCollection extends XpifBase
{
    /**
     * XpifMediaCollection constructor.
     *
     * @param null $cpss_version
     * @param string $lang
     * @throws \Exception
     */
    function __construct($cpss_version = null, $lang = "en")
    {
        parent::__construct($cpss_version, $lang);

        $this->rawProperties = [
            'insert-sheet' => []
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


        //MediaCollection domDocument
        $defaults = [
            'return' => 'domdocument',
            'loadEntities' => false,
            'readFile' => true,
            'parseHuge' => false,
        ];
        $domDocument = Xml::build($this->collection, $defaults);
        $domDocument->preserveWhiteSpace = true;
        $domDocument->formatOutput = true;

        //convert to XML string
        $xmlString = $domDocument->saveXML();

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
     * @return XpifInsertSheetCollection
     */
    public function setProperty($property_name, $property_value, $attributes = [])
    {
        $this->_setProperty($property_name, $property_value, $attributes, 'insert-sheet');
        return $this;
    }

    /**
     * @param $insert_after_page_number
     * @param array $attributes
     * @return XpifInsertSheetCollection
     */
    public function setInsertAfterPageNumber($insert_after_page_number, $attributes = [])
    {
        $this->setProperty('insert-sheet.value.insert-after-page-number', $insert_after_page_number, $attributes);
        return $this;
    }

    /**
     * @param $insert_before_page_number
     * @param array $attributes
     * @return XpifInsertSheetCollection
     */
    public function setInsertBeforePageNumber($insert_before_page_number, $attributes = [])
    {
        $insert_after_page_number = $insert_before_page_number - 1;
        $this->setProperty('insert-sheet.value.insert-after-page-number', $insert_after_page_number, $attributes);
        return $this;
    }

    /**
     * @param $insert_count
     * @param array $attributes
     * @return XpifInsertSheetCollection
     */
    public function setInsertCount($insert_count, $attributes = [])
    {
        $this->setProperty('insert-sheet.value.insert-count', $insert_count, $attributes);
        return $this;
    }

    /**
     * @param string $media
     * @param array $attributes
     * @return XpifInsertSheetCollection
     */
    public function setMedia($media, $attributes = [])
    {
        $this->setProperty('insert-sheet.value.media', $media, $attributes);
        return $this;
    }

    /**
     * @param XpifMediaCollection $media_collection
     * @return XpifInsertSheetCollection
     */
    public function setMediaCollection(XpifMediaCollection $media_collection)
    {
        $this->media_collection = $media_collection;
        return $this;
    }

    /**
     * Base structure as an array
     *
     * @return array
     */
    private function _getBaseArray()
    {
        $a = [
            'syntax' => 'collection',
        ];
        $element = $this->DTD->_buildElement('value', '', $a);
        $element = [
            'value' => $element
        ];

        $a = [
            'syntax' => '1setOf',
        ];
        $element = $this->DTD->_buildElement('insert-sheet', $element, $a);
        $element = [
            'insert-sheet' => $element
        ];

        return $element;
    }

    /**
     * Converts the raw properties into a well formed media collection
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
            $this->_mergeDataIntoCollection($dataTmp, 'insert-sheet.value.media-col');
        }
    }

}
