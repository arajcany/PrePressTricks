<?php

namespace arajcany\PrePressTricks\Ticketing\XPIF;

use Cake\Utility\Xml;

/**
 * Class XpifFrontCoverCollection
 *
 *
 * @package arajcany\PrePressTricks\Ticketing
 */
class XpifCoverFrontCollection extends XpifBase
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
            'cover-front' => []
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
     * @return XpifCoverFrontCollection
     */
    public function setProperty($property_name, $property_value, $attributes = [])
    {
        $this->_setProperty($property_name, $property_value, $attributes, 'cover-front');
        return $this;
    }

    /**
     * @param string $media
     * @param array $attributes
     * @return XpifCoverFrontCollection
     */
    public function setMedia($media, $attributes = [])
    {
        $this->setProperty('cover-front.media', $media, $attributes);
        return $this;
    }

    /**
     * @param XpifMediaCollection $media_collection
     * @return XpifCoverFrontCollection
     */
    public function setMediaCollection(XpifMediaCollection $media_collection)
    {
        $this->media_collection = $media_collection;
        return $this;
    }

    /**
     * @param $cover_type
     * @param array $attributes
     * @return XpifCoverFrontCollection
     * @internal param mixed $media_mismatch_property_policy
     */
    public function setCoverType($cover_type, $attributes = [])
    {
        $this->setProperty('cover-front.cover-type', $cover_type, $attributes);
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
        $element = $this->DTD->_buildElement('cover-front', '', $a);
        $element = [
            'cover-front' => $element
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
        $this->collection['cover-front'] = array_merge(
            $this->collection['cover-front'],
            $this->rawProperties['cover-front']);

        //merge the complex properties into the ticket
        if ($this->media_collection) {
            $this->collection['cover-front']['media-col'] = $this->media_collection->getCollection()['media-col'];
        }
    }

}
