<?php

namespace arajcany\PrePressTricks\Ticketing\XPIF;

use Cake\Utility\Xml;

/**
 * Class XpifMediaCollection
 *
 *
 * @package arajcany\PrePressTricks\Ticketing
 */
class XpifMediaCollection extends XpifBase
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
            'media-col' => []
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
     * @return XpifMediaCollection
     */
    public function setProperty($property_name, $property_value, $attributes = [])
    {
        $this->_setProperty($property_name, $property_value, $attributes, 'media-col');
        return $this;
    }

    /**
     * @param mixed $media_key
     * @param array $attributes
     * @return XpifMediaCollection
     */
    public function setMediaKey($media_key, $attributes = [])
    {
        $this->setProperty('media-key', $media_key, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_type
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaType($media_type, $attributes = [])
    {
        $this->setProperty('media-type', $media_type, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_info
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaInfo($media_info, $attributes = [])
    {
        $this->setProperty('media-info', $media_info, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_color
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaColor($media_color, $attributes = [])
    {
        $this->setProperty('media-color', $media_color, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_pre_printed
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaPrePrinted($media_pre_printed, $attributes = [])
    {
        $this->setProperty('media-pre-printed', $media_pre_printed, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_hole_count
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaHoleCount($media_hole_count, $attributes = [])
    {
        $this->setProperty('media-hole-count', $media_hole_count, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_order_count
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaOrderCount($media_order_count, $attributes = [])
    {
        $this->setProperty('media-order-count', $media_order_count, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_size
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaSize($media_size, $attributes = [])
    {
        $media_size_data = [
            '@syntax' => 'collection',
            'x-dimension' => [
                '@syntax' => 'integer',
                '@' => $media_size[0]
            ],
            'y-dimension' => [
                '@syntax' => 'integer',
                '@' => $media_size[1]
            ]
        ];

        $this->setProperty('media-size', $media_size_data, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_weight_metric
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaWeightMetric($media_weight_metric, $attributes = [])
    {
        $this->setProperty('media-weight-metric', $media_weight_metric, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_back_coating
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaBackCoating($media_back_coating, $attributes = [])
    {
        $this->setProperty('media-back-coating', $media_back_coating, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_front_coating
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaFrontCoating($media_front_coating, $attributes = [])
    {
        $this->setProperty('media-front-coating', $media_front_coating, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_recycled
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaRecycled($media_recycled, $attributes = [])
    {
        $this->setProperty('media-recycled', $media_recycled, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_description
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaDescription($media_description, $attributes = [])
    {
        $this->setProperty('media-description', $media_description, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_tooth
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaTooth($media_tooth, $attributes = [])
    {
        $this->setProperty('media-tooth', $media_tooth, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_grain
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaGrain($media_grain, $attributes = [])
    {
        $this->setProperty('media-grain', $media_grain, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_material
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaMaterial($media_material, $attributes = [])
    {
        $this->setProperty('media-material', $media_material, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_thickness
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaThickness($media_thickness, $attributes = [])
    {
        $this->setProperty('media-thickness', $media_thickness, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_size_name
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaSizeName($media_size_name, $attributes = [])
    {
        $this->setProperty('media-size-name', $media_size_name, $attributes);
        return $this;
    }

    /**
     * @param mixed $input_tray
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setInputTray($input_tray, $attributes = [])
    {
        $this->setProperty('input-tray', $input_tray, $attributes);
        return $this;
    }

    /**
     * @param mixed $tray_feed
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setTrayFeed($tray_feed, $attributes = [])
    {
        $this->setProperty('tray-feed', $tray_feed, $attributes);
        return $this;
    }

    /**
     * @param mixed $feed_orientation
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setFeedOrientation($feed_orientation, $attributes = [])
    {
        $this->setProperty('media-col.feed-orientation', $feed_orientation, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_mismatch_property_policy
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaMismatchPropertyPolicy($media_mismatch_property_policy, $attributes = [])
    {
        $this->setProperty('media-col.media-mismatch-property-policy', $media_mismatch_property_policy, $attributes);
        return $this;
    }

    /**
     * @param mixed $media_mismatch_size_policy
     * @param $attributes
     * @return XpifMediaCollection
     */
    public function setMediaMismatchSizePolicy($media_mismatch_size_policy, $attributes = [])
    {
        $this->setProperty('media-col.media-mismatch-size-policy', $media_mismatch_size_policy, $attributes);
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
        $element = $this->DTD->_buildElement('media-col', '', $a);
        $element = [
            'media-col' => $element
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
        $this->collection['media-col'] = array_merge(
            $this->collection['media-col'],
            $this->rawProperties['media-col']
        );
    }

}
