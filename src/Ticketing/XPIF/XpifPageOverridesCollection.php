<?php

namespace arajcany\PrePressTricks\Ticketing\XPIF;

use Cake\Utility\Xml;

/**
 * Class XpifPageOverridesCollection
 *
 *
 * @package arajcany\PrePressTricks\Ticketing
 */
class XpifPageOverridesCollection extends XpifBase
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
            'page-overrides' => []
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
     * @return XpifPageOverridesCollection
     */
    public function setProperty($property_name, $property_value, $attributes = [])
    {
        $this->_setProperty($property_name, $property_value, $attributes, 'page-overrides');
        return $this;
    }

    /**
     * @param array|int $page_ranges
     * @param array $attributes
     * @return XpifPageOverridesCollection
     */
    public function setPages($page_ranges, $attributes = [])
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

        $this->setProperty('page-overrides.value.pages', $page_ranges_data, $attributes);
        return $this;
    }

    /**
     * @param string $rotation
     * @param array $attributes
     * @return XpifPageOverridesCollection
     */
    public function setPageRotation($rotation, $attributes = [])
    {
        $this->setProperty('page-overrides.value.page-rotation', $rotation, $attributes);
        return $this;
    }

    /**
     * @param string $media
     * @param array $attributes
     * @return XpifPageOverridesCollection
     */
    public function setMedia($media, $attributes = [])
    {
        $this->setProperty('page-overrides.value.media', $media, $attributes);
        return $this;
    }

    /**
     * @param string $color_effects_type
     * @param array $attributes
     * @return XpifPageOverridesCollection
     */
    public function setColorEffectsType($color_effects_type, $attributes = [])
    {
        $this->setProperty('page-overrides.value.color-effects-type', $color_effects_type, $attributes);
        return $this;
    }

    /**
     * @param XpifMediaCollection $media_collection
     * @return XpifPageOverridesCollection
     */
    public function setMediaCollection(XpifMediaCollection $media_collection)
    {
        $this->media_collection = $media_collection;
        return $this;
    }

    /**
     * @param string $sides
     * @param array $attributes
     * @return XpifPageOverridesCollection
     */
    public function setSides($sides, $attributes = [])
    {
        $this->setProperty('page-overrides.value.sides', $sides, $attributes);
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
        $element = $this->DTD->_buildElement('page-overrides', $element, $a);
        $element = [
            'page-overrides' => $element
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
            $this->_mergeDataIntoCollection($dataTmp, 'page-overrides.value.media-col');
        }
    }

}
