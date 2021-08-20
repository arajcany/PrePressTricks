<?php


namespace arajcany\PrePressTricks\Graphics\Common;


use arajcany\PrePressTricks\Utilities\PDFGeometry;

class BaseCommands
{
    private $returnValue = 0;
    private $returnMessage = [];

    /**
     * BaseCommands constructor.
     */
    public function __construct()
    {

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

        $pdfGeometry = new PDFGeometry();

        $pages = $rawReport['aggregated']['pages']['page'];
        foreach ($pages as $pKey => $page) {
            $geo = $page['geometry'];
            $boundingBox = $geo['MediaBox'];
            $pages[$pKey]['geometry_eff'] = [
                "Applied_Rotate" => $geo['Rotate'],
                "Applied_UserUnit" => $geo['UserUnit']
            ];
            foreach ($boxTypes as $boxType) {
                $pages[$pKey]['geometry_eff'][$boxType] = $pdfGeometry->getEffectiveGeometry($geo[$boxType], $geo['Rotate'], $geo['UserUnit'], $boundingBox);

                if (isset($pages[$pKey]['geometry'][$boxType]['width_eff'])) {
                    //todo need to implement next line once JS GUI is updated
                    //unset($pages[$pKey]['geometry'][$boxType]['width_eff']);
                }

                if (isset($pages[$pKey]['geometry'][$boxType]['height_eff'])) {
                    //todo need to implement next line once JS GUI is updated
                    //unset($pages[$pKey]['geometry'][$boxType]['height_eff']);
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
