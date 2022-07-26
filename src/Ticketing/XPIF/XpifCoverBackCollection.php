<?php

namespace arajcany\PrePressTricks\Ticketing\XPIF;

use Cake\Utility\Xml;

/**
 * Class XpifBackCoverCollection
 *
 *
 * @package arajcany\PrePressTricks\Ticketing
 */
class XpifCoverBackCollection extends XpifBase
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
            'cover-back' => []
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
     * @return XpifCoverBackCollection
     */
    public function setProperty($property_name, $property_value, $attributes = [])
    {
        $this->_setProperty($property_name, $property_value, $attributes, 'cover-back');
        return $this;
    }

    /**
     * @param string $media
     * @param array $attributes
     * @return XpifCoverBackCollection
     */
    public function setMedia($media, $attributes = [])
    {
        $this->setProperty('cover-back.media', $media, $attributes);
        return $this;
    }

    /**
     * @param XpifMediaCollection $media_collection
     * @return XpifCoverBackCollection
     */
    public function setMediaCollection(XpifMediaCollection $media_collection)
    {
        $this->media_collection = $media_collection;
        return $this;
    }

    /**
     * @param $cover_type 'print-none'|'print-front'|'print-back'|'print-both'
     * @param array $attributes
     * @return XpifCoverBackCollection
     * @internal param mixed $media_mismatch_property_policy
     */
    public function setCoverType($cover_type, $attributes = [])
    {
        $this->setProperty('cover-back.cover-type', $cover_type, $attributes);
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
        $element = $this->DTD->_buildElement('cover-back', '', $a);
        $element = [
            'cover-back' => $element
        ];

        return $element;
    }

    /**
     * Converts the raw properties into a well formed media collection
     */
    private function _populateCollection()
    {
        //merge the properties into the collection
        $this->collection = $this->_getBaseArray();
        $this->collection['cover-back'] = array_merge(
            $this->collection['cover-back'],
            $this->rawProperties['cover-back']);

        //merge the complex properties into the ticket
        if ($this->media_collection) {
            $this->collection['cover-back']['media-col'] = $this->media_collection->getCollection()['media-col'];
        }
    }

}
