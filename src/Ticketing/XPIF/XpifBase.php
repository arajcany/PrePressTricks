<?php

namespace arajcany\PrePressTricks\Ticketing\XPIF;

use Cake\Utility\Hash;

/**
 * Class XpifBase
 *
 *
 *
 * @package arajcany\PrePressTricks\Ticketing\XPIF
 *
 * @property XpifMediaCollection $media_collection
 * @property XpifCoverFrontCollection $cover_front
 * @property XpifCoverFrontCollection $cover_back
 * @property XpifInsertSheetCollection[] $insert_sheet
 * @property XpifPageOverridesCollection[] $page_overrides
 */
class XpifBase
{
    //main collection
    protected $collection;

    //simple properties
    protected $rawProperties;

    //complex properties
    protected $media_collection;
    protected $cover_front;
    protected $cover_back;
    protected $insert_sheet;
    protected $page_overrides;

    //version control
    public $DTD;
    public $cpss_version;
    public $dtd_version;
    public $lang;

    /**
     * XpifBase constructor.
     *
     * @param null $cpss_version
     * @param string $lang
     * @throws \Exception
     */
    function __construct($cpss_version = null, $lang = "en")
    {
        $this->DTD = new XpifDtd($cpss_version, $lang);

        $this->cpss_version = $this->DTD->getCpssVersion();
        $this->lang = $this->DTD->getLang();
        $this->dtd_version = $this->DTD->getDtdVersion();

        return $this;
    }

    /**
     * Main method to set a property
     *
     * @param string $element
     * @param string|integer|array $text
     * @param array $attributes
     * @param $elementLimit
     * @return $this
     */
    protected function _setProperty($element, $text, $attributes, $elementLimit)
    {
        //check if valid property
        if (!$this->DTD->_isValidElement($element)) {
            //throw new Exception('Invalid property: "' . $element . '" is not a valid XPIF property.');
            return $this;
        }

        $lineage = $this->DTD->_traceHeritage($element, $elementLimit);
        $builtElement = $this->DTD->_buildElement($element, $text, $attributes);
        $this->rawProperties = Hash::insert($this->rawProperties, $lineage, $builtElement);

        return $this;
    }

    /**
     * Merge the rawProperties into the collection
     *
     * @internal param $rawPropertiesPath
     * @internal param $collectionPath
     */
    protected function _mergeRawPropertiesIntoCollection()
    {
        $this->collection = array_merge_recursive($this->collection, $this->rawProperties);
    }

    /**
     * Merges data into the collection.
     * Does not overwrite existing data. New data is merged in over the top.
     *
     * @param $dataToMerge
     * @param $collectionPath
     */
    protected function _mergeDataIntoCollection($dataToMerge, $collectionPath)
    {
        $existingData = Hash::extract($this->collection, $collectionPath);
        $newDataSet = array_merge($existingData, $dataToMerge);
        $this->collection = Hash::insert($this->collection, $collectionPath, $newDataSet);
    }

    /**
     * Inserts data into the collection.
     * Overwrites existing data.
     *
     * @param $dataToInsert
     * @param $collectionPath
     */
    protected function _insertDataIntoCollection($dataToInsert, $collectionPath)
    {
        $this->collection = Hash::insert($this->collection, $collectionPath, $dataToInsert);
    }

    /**
     * @return XpifDtd
     */
    public function getDTD()
    {
        return $this->DTD;
    }

    /**
     * @return null
     */
    public function getCpssVersion()
    {
        return $this->cpss_version;
    }

    /**
     * @return mixed
     */
    public function getDtdVersion()
    {
        return $this->dtd_version;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param $element
     * @return bool|array
     */
    public function hasChildren($element)
    {
        return $this->DTD->_hasChildren($element);
    }

    /**
     * @param $element
     * @return bool|array
     */
    public function getChildren($element)
    {
        return $this->DTD->_getChildren($element);
    }


}