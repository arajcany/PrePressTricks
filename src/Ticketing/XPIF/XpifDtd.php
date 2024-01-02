<?php

namespace arajcany\PrePressTricks\Ticketing\XPIF;

use Cake\Utility\Hash;
use Exception;

/**
 * Class XpifDtd
 * Used to validate the XPIF
 *
 * @package arajcany\PrePressTricks\Ticketing
 *
 * @property string $cpss_version
 * @property string $dtd_version
 * @property string $lang
 *
 * @property array $cpssMap
 * @property array $dtdMap
 * @property array $dtdFlatList
 */
class XpifDtd
{
    private $cpss_version;
    private $dtd_version;
    private $lang;

    private $cpssMap;
    private $dtdMap;
    private $dtdFlatList;

    /**
     * XpifDtd constructor.
     *
     * @param null $cpss_version
     * @param string $lang
     * @throws Exception
     */
    function __construct($cpss_version = null, $lang = "en")
    {
        $this->cpssMap = $this->_cpssMap();

        if ($cpss_version) {
            if (isset($this->cpssMap[$cpss_version])) {
                $this->cpss_version = $cpss_version;
                $this->dtd_version = $this->cpssMap[$cpss_version];
                $this->dtdMap = $this->_dtdMap();
                $this->dtdFlatList = $this->_dtdMapToDtdFlatList();
            } else {
                $list = $this->_cpssMap('list', ', ');
                throw new Exception('Invalid CPSS Version. Valid versions are ' . $list . '.');
            }
        } else {
            throw new Exception('Please supply a CPSS Version.');
        }

        $allowedLanguages = ['en', 'en-us'];
        if (in_array($lang, $allowedLanguages)) {
            $this->lang = $lang;
        } else {
            $list = implode(', ', $allowedLanguages);
            throw new Exception('Invalid CPSS Version. Valid versions are ' . $list . '.');
        }
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
     * @return array|mixed|string
     */
    public function getCpssMap()
    {
        return $this->cpssMap;
    }

    /**
     * @return mixed
     */
    public function getDtdMap()
    {
        return $this->dtdMap;
    }

    /**
     * @return array
     */
    public function getDtdFlatList()
    {
        return $this->dtdFlatList;
    }

    /**
     * Build an element based on the input
     *
     * @param string $element
     * @param string|integer|array $text
     * @param array $attributes
     * @return array|bool
     */
    public function _buildElement($element, $text, $attributes)
    {
        if (!$this->_isValidElement($element)) {
            return false;
        } else {
            $element = $this->_getElementDefaults($element);
        }

        //set additional attributes
        foreach ($attributes as $k => $v) {
            $element['@' . $k] = $v;
        }

        //set the text
        if ($text === 0 || $text === '0') {
            $element['@'] = $text;
        } elseif ($text === [] || $text === '') {
            return $element;
        } elseif (is_string($text) || is_numeric($text)) {
            $element['@'] = $text;
        } elseif (is_array($text)) {
            $element = array_merge($element, $text);
        }

        return $element;
    }

    /**
     * Element based on the defaults outlined in the DTD
     *
     * @param string $element
     * @return array|bool
     */
    public function _getElementDefaults($element)
    {
        if (!$this->_isValidElement($element)) {
            return false;
        } elseif (is_int(filter_var($this->_lastElement($element), FILTER_VALIDATE_INT))) {
            $propertyConfig = [];
        } else {
            $propertyConfig = $this->dtdMap['elements'][$this->_lastElement($element)];
        }

        $element = [];
        if (isset($propertyConfig['attributes'])) {
            foreach ($propertyConfig['attributes'] as $attributeName => $attributeConfig) {
                if ($attributeConfig['defaults'] === '#REQUIRED') {
                    $element['@' . $attributeName] = $attributeConfig['opts'][0];
                }
            }
        }

        return $element;
    }

    /**
     * Determine if an element has children
     *
     * @param string $element
     * @return bool
     */
    public function _hasChildren($element)
    {
        if (isset($this->dtdMap['elements'][$element])) {
            if (isset($this->dtdMap['elements'][$element]['children'])) {
                if (!empty($this->dtdMap['elements'][$element]['children'])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get children of an element
     *
     * @param string $element
     * @return bool
     */
    public function _getChildren($element)
    {
        if ($this->_hasChildren($element)) {
            return $this->dtdMap['elements'][$element]['children'];
        } else {
            return false;
        }
    }

    /**
     * Determine if an element is valid
     *
     * @param string $element
     * @return bool
     */
    public function _isValidElement($element)
    {
        //standalone element
        if (isset($this->dtdMap['elements'][$element])) {
            return true;
        }

        //dot notation
        foreach ($this->dtdFlatList as $dtdPath) {
            if (stristr($dtdPath, $element)) {
                return true;
            }
        }

        /*
         * Special case scenario of '.value.' in the dot notation
         * The '.value.' node is a valid but is not explicitly defined in the DTD.
         * This is because 'value' can contain ANY data (including more elements).
         *
         * e.g. we want all of the following to be valid
         * 1) 'xpif.job-template-attributes.page-ranges.value.lower-bound'
         * 2) 'xpif.job-template-attributes.finishings.value'
         * 3) 'xpif.job-template-attributes.finishings.value.<integer>'
         * 4) 'xpif.job-template-attributes.insert-sheet.value.insert-after-page-number'
         * 5) 'xpif.job-template-attributes.insert-sheet.value.<integer>.insert-after-page-number'
         * 6) 'xpif.job-template-attributes.insert-sheet.value.media-col.media-size.x-dimension'
         * 7) 'xpif.job-template-attributes.insert-sheet.value.<integer>.media-col.media-size.x-dimension'
         *
         */
        if (stristr($element, '.value')) {

            $isLastElementValid = false;
            if (isset($this->dtdMap['elements'][$this->_lastElement($element)])) {
                $isLastElementValid = true;
            } elseif (is_int(filter_var($this->_lastElement($element), FILTER_VALIDATE_INT))) {
                $isLastElementValid = true;
            }

            $isFirstPartOfElementPathValid = false;
            $elementFirstPart = implode('.value.', explode('.value.', $element, -1)) . '.value';
            foreach ($this->dtdFlatList as $dtdPath) {
                if (stristr($dtdPath, $elementFirstPart)) {
                    $isFirstPartOfElementPathValid = true;
                }
            }

            if ($isFirstPartOfElementPathValid && $isLastElementValid) {
                return true;
            }

        }

        return false;
    }

    private function _lastElement($element)
    {
        $elements = explode(".", $element);
        $lastElement = array_pop($elements);
        return $lastElement;
    }

    private function _firstElement($element)
    {
        $elements = explode(".", $element);
        $firstElement = $elements[0];
        return $firstElement;
    }

    /**
     * Trace the heritage of an element
     *
     * @param $element
     * @param string $elementLimit
     * @return bool|string
     */
    public function _traceHeritage($element, $elementLimit = 'xpif')
    {
        if (!$this->_isValidElement($element)) {
            return false;
        }

        $heritageMap = $element;
        $parent = explode(".", $element)[0];
        $safetyLoop = 50;
        $safetyLoopCounter = 0;
        while ($parent != $elementLimit && $safetyLoopCounter <= $safetyLoop) {
            $parent = $this->_findParent($parent);
            if ($parent == false) {
                return $heritageMap;
            }
            $heritageMap = $parent . "." . $heritageMap;

            $safetyLoopCounter++;
        }

        return $heritageMap;

    }

    /**
     * Find the Nth parent of an element
     * for example, 'lower-bound' is a child of 'finishings-media-sheets-min-max' and 'page-ranges'
     *
     * @param $element
     * @param int $position
     * @return bool|int|string
     */
    public function _findParent($element, $position = 0)
    {
        $parents = $this->_findAllParents($element);
        if (isset($parents[$position])) {
            return $parents[$position];
        } else {
            return false;
        }
    }

    /**
     * Find all possible parents of an element
     * e.g. 'lower-bound' is a child of 'finishings-media-sheets-min-max' and 'page-ranges'
     *
     * @param $element
     * @return bool|int|string|array
     */
    public function _findAllParents($element)
    {
        $parents = [];

        foreach ($this->dtdMap['elements'] as $elementKey => $elementProperties) {
            if ($this->_hasChildren($elementKey)) {
                if (in_array($element, $elementProperties['children'])) {
                    $parents[] = $elementKey;
                }
            }
        }

        if ($parents == []) {
            return false;
        } else {
            return $parents;
        }
    }

    /**
     * Return the Version Map as an array or list
     *
     * @param string $format
     * @param string $listSeparator
     * @return array|mixed|string
     */
    private function _cpssMap($format = 'map', $listSeparator = ',')
    {
        $map = json_decode(
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR."XpifDtdMapping".DIRECTORY_SEPARATOR."_version.json"),
            JSON_OBJECT_AS_ARRAY);

        $list = array_keys($map);
        $list = implode($listSeparator, $list);

        if ($format == 'list') {
            return $list;
        } else {
            return $map;
        }
    }

    /**
     * Return the DTD Map
     *
     * @return mixed
     */
    private function _dtdMap()
    {
        $map = json_decode(
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR."XpifDtdMapping".DIRECTORY_SEPARATOR."{$this->cpss_version}.json"),
            JSON_OBJECT_AS_ARRAY);
        return $map;
    }

    /**
     * Expands the DTD into a tree structure
     *
     * @param $parent
     * @param $elements
     * @param array $dtdTree
     * @param string $currentPath
     * @return array
     */
    private function _dtdMapToDtdTree($parent, $elements, $dtdTree = [], $currentPath = '')
    {
        if (isset($elements[$parent]['children'])) {
            if ($elements[$parent]['children'] != []) {
                foreach ($elements[$parent]['children'] as $child) {
                    $currentPathWithChild = $currentPath . "." . $child;
                    $dtdTree = $this->_dtdMapToDtdTree($child, $elements, $dtdTree, $currentPathWithChild);
                }
            } else {
                $currentPath = explode(".", $currentPath);
                array_pop($currentPath);
                $currentPath = implode(".", $currentPath);

                $hashPath = $currentPath . "." . $parent;
                $dtdTree = Hash::insert($dtdTree, $hashPath, "");
            }
        }

        return $dtdTree;
    }

    /**
     * Returns a list of all element paths in dot-notation format
     * .e.g. [ 0 => 'xpif.xpif-operation-attributes.job-name' ...]
     *
     * @return array
     */
    private function _dtdMapToDtdFlatList()
    {
        $parent = 'xpif';
        $elements = $this->dtdMap['elements'];
        $dtdTree = $this->_dtdMapToDtdTree($parent, $elements, [], $parent);
        $dtdFlatList = array_keys(Hash::flatten($dtdTree));

        return $dtdFlatList;
    }


}