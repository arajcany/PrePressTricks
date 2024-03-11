<?php


namespace arajcany\PrePressTricks\Graphics\Common;


use arajcany\PrePressTricks\Utilities\PDFGeometry;

class BaseCommands
{
    private $returnValue = 0;
    private $returnMessage = [];
    protected $tmpDir;

    /**
     * BaseCommands constructor.
     */
    public function __construct()
    {
        if (defined('TMP')) {
            $this->tmpDir = TMP;
        } else {
            $this->tmpDir = __DIR__ . '/../../../tmp/';
            $this->mkdirWithCheck($this->tmpDir);
        }
    }

    /**
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * @param mixed $returnValue
     */
    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;
    }

    /**
     * @return mixed
     */
    public function getReturnMessage()
    {
        return $this->returnMessage;
    }

    /**
     * @param mixed $returnMessage
     */
    public function setReturnMessage($returnMessage)
    {
        $this->returnMessage[] = $returnMessage;
    }

    /**
     * Wrapper function to make a directory - check first to avoid errors.
     *
     * @param string $directory
     * @param int $permissions
     * @param bool $recursive
     * @param $context
     * @return bool
     */
    public function mkdirWithCheck(string $directory, int $permissions = 0777, bool $recursive = true, $context = null): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        if ($context) {
            return mkdir($directory, $permissions, $recursive, $context);
        } else {
            return mkdir($directory, $permissions, $recursive);
        }
    }

    /**
     * Extract the Key from the nested arrays.
     *
     * e.g. consider the following array.
     * [
     *  'foo' => [a,b,c,d,e],
     *  'bar' => [f,g,h,i,j]
     * ]
     *  - 'e' would return 'foo'
     *  - 'g' would return 'bar'
     *  - 'xxx' would return $default
     *
     * @param array $masters
     * @param string|bool|null $unknown
     * @param string $default
     * @return string
     */
    protected function getMasterKeyFromUnknown(array $masters, $unknown, $default = '')
    {
        if (is_array($unknown)) {
            $unknown = implode("", $unknown);
        }

        foreach ($masters as $masterKey => $values) {

            //check if the $unknown is actually a masterKey
            if (is_string($unknown)) {
                if (strtolower($masterKey) === strtolower($unknown)) {
                    return $masterKey;
                }
            }

            foreach ($values as $value) {
                if ($unknown === true || $unknown === false || $unknown === null) {
                    if ($unknown === $value) {
                        return $masterKey;
                    }
                } elseif ($value === $unknown) {
                    return $masterKey;
                } elseif (is_string($value) && is_string($unknown)) {
                    if (strtolower($value) === strtolower($unknown)) {
                        return $masterKey;
                    }
                }
            }
        }

        return $default;
    }


    /**
     * Formats a Callas PDF Toolbox quickcheck JSON report as Page Size Groups report.
     * Return format is an array, be sure to json_encode() before saving this report to FSO.
     *
     * @param $reportStringOrPath
     * @return array[]|false
     */
    protected function convertQuickCheckReportToPageSizeGroupsReport($reportStringOrPath)
    {
        if (is_string($reportStringOrPath) && is_file($reportStringOrPath)) {
            $rawReport = file_get_contents($reportStringOrPath);
            $rawReport = json_decode($rawReport, JSON_OBJECT_AS_ARRAY);
        } elseif (is_string($reportStringOrPath)) {
            $rawReport = json_decode($reportStringOrPath, JSON_OBJECT_AS_ARRAY);
        } elseif (is_array($reportStringOrPath)) {
            $rawReport = $reportStringOrPath;
        } else {
            return false;
        }

        $pages = $rawReport['aggregated']['pages']['page'];

        $boxTypes = [
            'MediaBox', //MediaBox is first as it MUST be defined as per PDF specification
            'TrimBox',
            'BleedBox',
            'CropBox',
            'ArtBox'
        ];

        $pageGroups = [];

        foreach ($pages as $p => $page) {
            foreach ($boxTypes as $b => $boxType) {
                if (isset($page['geometry'][$boxType])) {
                    $pageNumber = $page['info']['pagenum'];
                    $eWidth = $page['geometry_eff'][$boxType]['width'];
                    $eHeight = $page['geometry_eff'][$boxType]['height'];
                    $pageGroups[$boxType]['pages_grouped_by_size'][$eWidth . '_' . $eHeight][] = $pageNumber;
                } else {
                    $pageGroups[$boxType]['pages_grouped_by_size']['0_0'][] = $pageNumber;
                }
            }
        }

        foreach ($boxTypes as $b => $boxType) {
            if (empty($pageGroups[$boxType]['pages_grouped_by_size'])) {
                continue;
            }
            $count = count($pageGroups[$boxType]['pages_grouped_by_size']);
            if ($count == 1) {
                $pageGroups[$boxType]['all_same_size'] = true;
            } else {
                $pageGroups[$boxType]['all_same_size'] = false;
            }
            $pageGroups[$boxType]['size_count'] = $count;
            $pageGroups[$boxType]['sizes'] = array_keys($pageGroups[$boxType]['pages_grouped_by_size']);

            $pbs = $pageGroups[$boxType]['pages_grouped_by_size'];
            unset($pageGroups[$boxType]['pages_grouped_by_size']);
            $pageGroups[$boxType]['pages_grouped_by_size'] = $pbs;
        }

        return $pageGroups;
    }

    /**
     * Populates the effective geometry for the given report.
     * Returns the report as an array, even if given a report FSO location.
     *
     * @param string|array $reportStringOrPath
     * @return array|false|mixed
     */
    protected function populateQuickCheckReportWithEffectiveGeometry($reportStringOrPath)
    {
        if (is_string($reportStringOrPath) && is_file($reportStringOrPath)) {
            $rawReport = file_get_contents($reportStringOrPath);
            $rawReport = json_decode($rawReport, JSON_OBJECT_AS_ARRAY);
        } elseif (is_string($reportStringOrPath)) {
            $rawReport = json_decode($reportStringOrPath, JSON_OBJECT_AS_ARRAY);
        } elseif (is_array($reportStringOrPath)) {
            $rawReport = $reportStringOrPath;
        } else {
            return false;
        }

        $boxTypes = [
            'MediaBox', //MediaBox is first as it MUST be defined as per PDF specification
            'TrimBox',
            'BleedBox',
            'CropBox',
            'ArtBox'
        ];

        $renders = [
            'eff' => ['Rotate' => null],
            'applied_0' => ['Rotate' => 0],
            'applied_90' => ['Rotate' => 90],
            'applied_180' => ['Rotate' => 180],
            'applied_270' => ['Rotate' => 270],
        ];

        $pdfGeometry = new PDFGeometry();

        $pages = $rawReport['aggregated']['pages']['page'];
        foreach ($pages as $pKey => $page) {
            foreach ($renders as $renderKey => $renderValue) {
                $geo = $page['geometry'];

                if ($renderKey == 'eff') {
                    $appliedRotation = $geo['Rotate'];
                    $appliedUserUnit = $geo['UserUnit'];
                } else {
                    $appliedRotation = $renderValue['Rotate'];
                    $appliedUserUnit = $geo['UserUnit'];
                }

                $boundingBox = $geo['MediaBox'];
                $pages[$pKey]['geometry_' . $renderKey] = [
                    "Applied_Rotate" => $appliedRotation,
                    "Applied_UserUnit" => $appliedUserUnit
                ];
                foreach ($boxTypes as $boxType) {
                    $pages[$pKey]['geometry_' . $renderKey][$boxType] = $pdfGeometry->getEffectiveGeometry($geo[$boxType], $appliedRotation, $appliedUserUnit, $boundingBox);

                    if (isset($pages[$pKey]['geometry'][$boxType]['width_eff'])) {
                        //todo need to implement next line once JS GUI is updated
                        unset($pages[$pKey]['geometry'][$boxType]['width_eff']);
                    }

                    if (isset($pages[$pKey]['geometry'][$boxType]['height_eff'])) {
                        //todo need to implement next line once JS GUI is updated
                        unset($pages[$pKey]['geometry'][$boxType]['height_eff']);
                    }
                }
            }
        }

        $rawReport['aggregated']['pages']['page'] = $pages;

        return $rawReport;
    }


    public function groupImagesByPage($images)
    {
        $grouped = [];
        foreach ($images as $image) {
            $re = '/\_[0-9]\((.*?)\)./m';
            preg_match($re, $image, $matches, PREG_OFFSET_CAPTURE, 0);
            if (isset($matches[0][0]) && isset($matches[1][0])) {
                $pageAndColour = $matches[0][0];
                $colour = $matches[1][0];

                $page = str_replace($colour, '', $pageAndColour);
                $page = preg_replace('/[^0-9]/', '', $page);

                $grouped[$page][$colour] = $image;
            } else {
                $re = '/\_[0-9]./m';
                preg_match($re, $image, $matches, PREG_OFFSET_CAPTURE, 0);
                if (isset($matches[0][0])) {
                    $page = (str_replace(['_', '.'], '', $matches[0][0])) + 0;
                    $grouped[$page]['All'] = $image;
                }
            }
        }

        return $grouped;
    }
}
