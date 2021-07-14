<?php


namespace arajcany\PrePressTricks\Graphics\Ghostscript;


use arajcany\PrePressTricks\Utilities\Boxes;
use arajcany\PrePressTricks\Utilities\Pages;

class GhostscriptCommands
{
    private $gsPath = null;
    private $returnValue = null;
    private $returnMessage = null;
    private $pdfInfoFile = null;

    /**
     * GhostscriptCommands constructor.
     * @param null $gsPath
     */
    public function __construct($gsPath = null)
    {
        if ($gsPath) {
            $this->gsPath = $gsPath;
        } else {
            $command = "where gswin64c";
            $output = [];
            $return_var = '';
            exec($command, $output, $return_var);
            if (isset($output[0])) {
                if (is_file($output[0])) {
                    $this->gsPath = $output[0];
                }
            }
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
        $this->returnMessage = $returnMessage;
    }

    /**
     * @param null $gsPath
     * @return GhostscriptCommands
     */
    public function setGsPath($gsPath)
    {
        $this->gsPath = $gsPath;

        $gsBinPath = pathinfo($gsPath, PATHINFO_DIRNAME);
        $gsLibPath = str_replace("bin", "lib", $gsBinPath) . DIRECTORY_SEPARATOR;
        $this->pdfInfoFile = $gsLibPath . "pdf_info.ps";

        return $this;
    }

    /**
     * Get the version string
     *
     * @return false|mixed
     */
    public function getCliVersion()
    {
        $version = $this->cli("-version");
        if (isset($version[0])) {
            return $version[0];
        }
    }

    /**
     * Get the info string
     *
     * @return false|mixed
     */
    public function getCliStatus()
    {
        return $this->cli("--status");
    }

    /**
     * Get the info string
     *
     * @return false|mixed
     */
    public function getCliHelp()
    {
        return $this->cli("--help");
    }

    /**
     * Generic function to run a command
     *
     * @param $cliCommand
     * @return false|mixed
     */
    private function cli($cliCommand)
    {
        $options = [
            $this->gsPath,
            $cliCommand
        ];

        $cmd = __('"{0}" {1}', $options);
        exec($cmd, $out, $ret);

        if ($ret == 0) {
            if (isset($out[0])) {
                $version = $out;
            } else {
                $version = false;
            }
        } else {
            $version = false;
        }

        return $version;
    }


    /**
     * Get a PDF report in the CLI Applications native format.
     *
     * @param $pdfPath //path to the PDF file
     * @param bool $useCached //if true, will try and use a local save copy
     * @param bool|string $saveReport //if true, will save with .report.txt as the extension. If a path, save it to that path - must be fully qualified path + filename + extension.
     * @return string
     */
    public function getPdfReport($pdfPath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".report.txt";

        if ($useCached) {
            //try and find exiting report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached TEXT report');
                $this->setReturnValue(0);
                return file_get_contents($defaultSavePath);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached TEXT report');
                $this->setReturnValue(0);
                return file_get_contents($saveReport);
            }
        }


        $args = [
            $this->gsPath,
            $pdfPath,
            $this->pdfInfoFile,
        ];
        $command = __('"{0}" -q -dQUIET -dNOSAFER -dBATCH -dNOPAUSE -dNOPROMPT -dNODISPLAY -sFile="{1}" -dDumpMediaSizes=true "{2}" ', $args);
        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);
        $report = implode("\r\n", $output);


        if ($saveReport) {
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $report);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                @mkdir($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $report);
                }
            }
        }

        $this->setReturnMessage('PDF Info report generated');
        $this->setReturnValue($return_var);

        return $report;
    }


    /**
     * Get a PDF report in Callas JSON format.
     *
     * @param $pdfPath //path to the PDF file
     * @param bool $useCached //if true, will try and use a local save copy
     * @param bool|string $saveReport //if true, will save with .report.txt as the extension. If a path, save it to that path - must be fully qualified path + filename + extension.
     * @return array|false
     */
    public function getCallasReport($pdfPath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".report.json";

        if ($useCached) {
            //try and find exiting report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached JSON report');
                $this->setReturnValue(0);
                return json_decode(file_get_contents($defaultSavePath), JSON_OBJECT_AS_ARRAY);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached JSON report');
                $this->setReturnValue(0);
                return json_decode(file_get_contents($saveReport), JSON_OBJECT_AS_ARRAY);
            }
        }


        $report = $this->getPdfReport($pdfPath, $useCached);
        if (empty($report)) {
            return false;
        }
        $report = $this->convertPdfReportToCallasJsonReport($report);


        if ($saveReport) {
            $reportString = json_encode($report, JSON_PRETTY_PRINT);
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $reportString);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                @mkdir($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $reportString);
                }
            }
        }

        $this->setReturnMessage('Callas report generated');
        $this->setReturnValue(0);

        return $report;
    }

    /**
     * Report on the page sizes in the PDF.
     * Handy for when you need to RIP a PDF as images.
     *
     * @param $pdfPath
     * @param bool $useCached
     * @param bool $saveReport
     * @return array|false
     */
    public function getPageSizeGroupsReport($pdfPath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".pagesizegroups.json";

        if ($useCached) {
            //try and find exiting report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached PageSizeGroups JSON report');
                $this->setReturnValue(0);
                return json_decode(file_get_contents($defaultSavePath), JSON_OBJECT_AS_ARRAY);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached PageSizeGroups JSON report');
                $this->setReturnValue(0);
                return json_decode(file_get_contents($saveReport), JSON_OBJECT_AS_ARRAY);
            }
        }


        $report = $this->getCallasReport($pdfPath, $useCached);
        if (empty($report)) {
            return false;
        }

        if (!isset($report['aggregated']['pages']['page'])) {
            return false;
        }
        $report = $this->convertCallasJsonReportToPageSizeGroupsReport($report);


        if ($saveReport) {
            $reportString = json_encode($report, JSON_PRETTY_PRINT);
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $reportString);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                @mkdir($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $reportString);
                }
            }
        }

        $this->setReturnMessage('Page Size Groups report generated');
        $this->setReturnValue(0);

        return $report;
    }

    /**
     * Report on the page sizes in the PDF.
     * Handy for when you need to RIP a PDF as images.
     *
     * @param $pdfPath
     * @param bool $useCached
     * @param bool $saveReport
     * @return array|false
     */
    public function getPageSeparationsReport($pdfPath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".pageseparations.json";

        if ($useCached) {
            //try and find exiting report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached PageSeparations JSON report');
                $this->setReturnValue(0);
                return json_decode(file_get_contents($defaultSavePath), JSON_OBJECT_AS_ARRAY);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached PageSeparations JSON report');
                $this->setReturnValue(0);
                return json_decode(file_get_contents($saveReport), JSON_OBJECT_AS_ARRAY);
            }
        }


        $report = $this->getPdfReport($pdfPath, $useCached);
        if (empty($report)) {
            return false;
        }

        $report = $this->convertPdfReportToSeparationsJsonReport($report);


        if ($saveReport) {
            $reportString = json_encode($report, JSON_PRETTY_PRINT);
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $reportString);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                @mkdir($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $reportString);
                }
            }
        }

        $this->setReturnMessage('Page Separations report generated');
        $this->setReturnValue(0);

        return $report;
    }


    /**
     * Formats a Callas PDF Toolbox quickcheck JSON report as Page Size Groups report.
     * Return format is an array, be sure to json_encode() before saving this report to FSO.
     *
     * @param $reportStringOrPath
     * @return array[]|false
     */
    private function convertCallasJsonReportToPageSizeGroupsReport($reportStringOrPath)
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
                    $eWidth = $page['geometry'][$boxType]['width_eff'];
                    $eHeight = $page['geometry'][$boxType]['height_eff'];
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


    /**
     * Formats a raw Ghostscript pdf_info.ps report as a colour separation report.
     * Return format is an array, be sure to json_encode() before saving this report to FSO.
     *
     * @param $reportStringOrPath
     * @return array[]|false
     */
    private function convertPdfReportToSeparationsJsonReport($reportStringOrPath)
    {
        if (is_string($reportStringOrPath) && is_file($reportStringOrPath)) {
            $rawReport = file_get_contents($reportStringOrPath);
            $rawReport = str_replace("\r\n", "\r", $rawReport);
            $rawReport = str_replace("\r", "\n", $rawReport);
            $rawReport = explode("\n", $rawReport);
        } elseif (is_string($reportStringOrPath)) {
            $rawReport = str_replace("\r\n", "\r", $reportStringOrPath);
            $rawReport = str_replace("\r", "\n", $rawReport);
            $rawReport = explode("\n", $rawReport);
        } elseif (is_array($reportStringOrPath)) {
            $rawReport = $reportStringOrPath;
        } else {
            return false;
        }

        $sepReport = [];
        $currentPageNumber = null;
        $isInsidePageSpotColour = false;
        $baseSeps = ['Cyan', 'Magenta', 'Yellow', 'Black'];
        foreach ($rawReport as $line) {
            if (substr($line, 0, 5) === 'Page ') {
                //extract page number
                $re = '/Page (.*?) /m';
                preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
                if (isset($matches[0][1])) {
                    $currentPageNumber = $matches[0][1];
                    $sepReport[$currentPageNumber] = $baseSeps;
                }
            }

            $tmpTagOpen = "    Page Spot colors:";
            $tmpTagInside = "        '";
            $tmpTagClose = "";
            if (substr($line, 0, strlen($tmpTagOpen)) === $tmpTagOpen) {
                $isInsidePageSpotColour = true;
            }

            if ($isInsidePageSpotColour) {
                if (substr($line, 0, strlen($tmpTagInside)) === $tmpTagInside) {
                    //extract spot colour
                    $re = '/\'(.*?)\'/m';
                    preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
                    if (isset($matches[0][1])) {
                        $currentSpotColour = $matches[0][1];
                        $sepReport[$currentPageNumber][] = $currentSpotColour;
                    }
                }
            }

            if ($isInsidePageSpotColour) {
                if (trim($line) == $tmpTagClose) {
                    $isInsidePageSpotColour = false;
                }
                if ($line != $tmpTagOpen && strpos($line, ":") !== false) {
                    $isInsidePageSpotColour = false;
                }
            }
        }

        return $sepReport;
    }


    /**
     * Formats a raw Ghostscript pdf_info.ps report as Callas PDF Toolbox quickcheck JSON report.
     * Return format is an array, be sure to json_encode() before saving this report to FSO.
     *
     * @param $reportStringOrPath
     * @return array[]|false
     */
    private function convertPdfReportToCallasJsonReport($reportStringOrPath)
    {
        if (is_string($reportStringOrPath) && is_file($reportStringOrPath)) {
            $rawReport = file_get_contents($reportStringOrPath);
            $rawReport = str_replace("\r\n", "\r", $rawReport);
            $rawReport = str_replace("\r", "\n", $rawReport);
            $rawReport = explode("\n", $rawReport);
        } elseif (is_string($reportStringOrPath)) {
            $rawReport = str_replace("\r\n", "\r", $reportStringOrPath);
            $rawReport = str_replace("\r", "\n", $rawReport);
            $rawReport = explode("\n", $rawReport);
        } elseif (is_array($reportStringOrPath)) {
            $rawReport = $reportStringOrPath;
        } else {
            return false;
        }

        $pages = ['length' => 0, 'page' => []];
        $data = [];
        $dataTypes = [
            'Title:' => 'Title',
            'Author:' => 'Author',
            'Subject:' => 'Subject',
            'Keywords:' => 'Keywords',
            'Creator:' => 'Creator',
            'Producer:' => 'Producer',
            'CreationDate:' => 'CreationDate',
            'ModDate:' => 'ModDate',
            'Trapped:' => 'Trapped',
        ];

        $boxTypes = [
            'MediaBox', //MediaBox is first as it MUST be defined as per PDF specification
            'TrimBox',
            'BleedBox',
            'CropBox',
            'ArtBox'
        ];

        //how Callas formats the reports
        $callPdfToolboxBoxOrder = [
            'BleedBox' => [],
            'TrimBox' => [],
            'ArtBox' => [],
            'CropBox' => [],
            'MediaBox' => [],
            'Rotate' => 0,
            'UserUnit' => 1, //TODO figure out what a UserUnit is???
        ];

        //extract page info
        foreach ($rawReport as $line) {
            if (substr($line, 0, 5) === 'Page ') {
                $pages['length']++;

                $pageCompiledData = [];

                //extract page number
                $re = '/Page (.*?) /m';
                preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
                if (isset($matches[0][1])) {
                    $pageNumber = intval($matches[0][1]);
                } else {
                    $pageNumber = 0;
                }

                //extract rotation
                $re = '/Rotate = (.*) /';
                preg_match_all($re, $line . " ", $matches, PREG_SET_ORDER, 0);
                if (isset($matches[0][1])) {
                    $rotation = intval($matches[0][1]);
                } else {
                    $rotation = 0;
                }

                $pageCompiledData['info'] = ['pagenum' => $pageNumber];
                $pageCompiledData['geometry'] = $callPdfToolboxBoxOrder;
                $pageCompiledData['geometry']['Rotate'] = $rotation;

                foreach ($boxTypes as $boxType) {

                    $re = '/' . $boxType . ': \[(.*?)\]/m';
                    preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);

                    //var_dump($matches);
                    if (isset($matches[0][1])) {
                        $coordinates = $matches[0][1];
                        $coordinates = explode(" ", $coordinates);

                        $pageCompiledData['geometry'][$boxType]['present'] = true;
                        $pageCompiledData['geometry'][$boxType]['left'] = $coordinates[0] * 1;
                        $pageCompiledData['geometry'][$boxType]['bottom'] = $coordinates[1] * 1;
                        $pageCompiledData['geometry'][$boxType]['right'] = $coordinates[2] * 1;
                        $pageCompiledData['geometry'][$boxType]['top'] = $coordinates[3] * 1;

                        $pageCompiledData['geometry'][$boxType]['width'] = $pageCompiledData['geometry'][$boxType]['right'] - $pageCompiledData['geometry'][$boxType]['left'];
                        $pageCompiledData['geometry'][$boxType]['height'] = $pageCompiledData['geometry'][$boxType]['top'] - $pageCompiledData['geometry'][$boxType]['bottom'];

                        if ($rotation == 0 || $rotation == 180) {
                            $pageCompiledData['geometry'][$boxType]['width_eff'] = $pageCompiledData['geometry'][$boxType]['width'];
                            $pageCompiledData['geometry'][$boxType]['height_eff'] = $pageCompiledData['geometry'][$boxType]['height'];
                        } elseif ($rotation == 90 || $rotation == 270) {
                            $pageCompiledData['geometry'][$boxType]['width_eff'] = $pageCompiledData['geometry'][$boxType]['height'];
                            $pageCompiledData['geometry'][$boxType]['height_eff'] = $pageCompiledData['geometry'][$boxType]['width'];
                        }

                    } else {
                        //clone the MediaBox geometry but set the presence to false
                        $pageCompiledData['geometry'][$boxType] = $pageCompiledData['geometry']['MediaBox'];
                        $pageCompiledData['geometry'][$boxType]['present'] = false;
                    }
                }

                $pages['page'][] = $pageCompiledData;
            } else {
                $pageNumber = null;
            }

            //extract other data
            foreach ($dataTypes as $dataKey => $boxType) {
                if (substr($line, 0, strlen($dataKey)) === $dataKey) {
                    $data[$boxType] = trim(str_replace($dataKey, "", $line));
                }
            }
        }

        $env = [
            'verb' => 'pdf_info.ps',
            'pdft_uuid' => null,
            'timestamp' => date("Y-m-d H:i:s"),
            'timestamp_hour' => date("H"),
            'timestamp_month' => date("m"),
            'timestamp_day' => date("d"),
            'timestamp_weekday' => null,
            'process_id' => getmypid(),
            'program_name' => 'ghostscript',
            'program_version' => $this->getCliVersion(),
            'platform' => null,
            'machine_name' => gethostname(),
            'job_id' => null,
        ];

        //try and extract the PDF filepath from the report
        $txtReport = implode("\r\n", $rawReport);
        $fileSearch = explode(" has ", $txtReport);
        $pdfPath = null;
        if (isset($fileSearch[0])) {
            $fileSearch = trim($fileSearch[0]);
            if (is_file($fileSearch)) {
                $pdfPath = trim($fileSearch);
            }
        }

        if (is_file($pdfPath)) {
            $file = [
                'bytes' => filesize($pdfPath),
                'created' => date("Y-m-d H:i:s", filectime($pdfPath)),
                'modified' => date("Y-m-d H:i:s", filemtime($pdfPath)),
                'name' => pathinfo($pdfPath, PATHINFO_BASENAME),
                'path' => pathinfo($pdfPath, PATHINFO_DIRNAME),
                'filepath' => $pdfPath,
            ];
        } else {
            $file = [
                'bytes' => null,
                'created' => null,
                'modified' => null,
                'name' => null,
                'path' => null,
                'filepath' => null,
            ];
        }


        $report = ['aggregated' =>
            [
                'env' => $env,
                'file' => $file,
                'pages' => $pages,
                'doc' => $data,
            ]
        ];

        return $report;
    }


    /**
     * Wrapper function to convert a PDF to Images.
     *
     * A wrapper function is used because gs is not the best at ripping multi page size documents.
     * This wrapper splits such documents into chunks for conversion - based on same page size.
     *
     * For documentation on $options, see $this->getDefaultSaveOptions().
     *
     * @param $pdfPath
     * @param array $options
     * @return array
     */
    public function savePdfAsImages($pdfPath, $options = [])
    {
        $defaultOptions = $this->getDefaultSaveOptions();
        $options = array_merge($defaultOptions, $options);

        $options['resolution'] = strtolower($options['resolution']);
        $options['resolution'] = str_replace(['*', "_", "-", "/", "+"], 'x', $options['resolution']);

        $Pages = new Pages();
        $pageList = $Pages->rangeExpand($options['pagelist'], ['returnFormat' => 'array']);
        $pageList = $this->getValidatedPageList($pdfPath, $pageList);
        if (empty($pageList)) {
            return [];
        } else {
            $options['pagelist'] = $pageList;
        }

        $compiledReturns = [];
        if (strpos($options['resolution'], 'x') === false) {
            //pass straight through as no need to fit a specific dimension
            $compiledReturns = array_merge($compiledReturns, $this->convertPdfToImages($pdfPath, $options));
            return $compiledReturns;
        } else {
            //reformat the resolution as there could be mixed Box sizes
            $boxes = new Boxes();
            $requestedPageBox = $options['pagebox'];
            $pageSizeGroups = $this->getPageSizeGroupsReport($pdfPath);

            foreach ($pageSizeGroups[$requestedPageBox]['pages_grouped_by_size'] as $pageSize => $pages) {
                $imageWidth = explode("_", $pageSize)[0];
                $imageHeight = explode("_", $pageSize)[1];
                $requestedMaxWidth = explode("x", $options['resolution'])[0];
                $requestedMaxHeight = explode("x", $options['resolution'])[1];
                $newResolution = $boxes->fitIntoBox($imageWidth, $imageHeight, $requestedMaxWidth, $requestedMaxHeight, true);
                $newResolutionString = $newResolution['width'] . "x" . $newResolution['height'];

                $imageWidthInches = ($imageWidth / 72);
                $imageHeightInches = ($imageHeight / 72);
                $outputDpi = floor(min(($newResolution['width'] / $imageWidthInches), ($newResolution['height'] / $imageHeightInches)));

                $intersectingPages = array_intersect($options['pagelist'], $pages);

                $newOptions = ['resolution' => $newResolutionString, 'pagelist' => $intersectingPages, 'outputdpi' => $outputDpi];
                $newOptions = array_merge($options, $newOptions);
                $compiledReturns = array_merge($compiledReturns, $this->convertPdfToImages($pdfPath, $newOptions));
            }
        }

        $compiledReturns = array_unique($compiledReturns);
        $compiledReturns = array_values($compiledReturns);
        return $compiledReturns;
    }


    /**
     * The workhorse function that converts a PDF to Images.
     * This function is private because it expects that the $pdfPath and $options are 100% correct and valid.
     * It would be a good idea to employ an wrapper function that does all the error checking prior to calling this function.
     *
     * @param $pdfPath
     * @param array $options
     * @return array of image paths
     */
    private function convertPdfToImages($pdfPath, $options = [])
    {

        $boxTypes = [
            'MediaBox', //MediaBox is first as it MUST be defined as per PDF specification
            'TrimBox',
            'BleedBox',
            'CropBox',
            'ArtBox'
        ];

        $colourArray = ['color', 'colour', 'col', 'c', 'rgb', 'cmyk',];
        $grayArray = ['grey', 'gray', 'greyscale', 'grayscale', 'g',];
        $monoArray = ['mono', 'monochrome', 'm',];

        //we use a tmp image name gs still numbers sequentially 1-n even if you ask for pages 4,7,8 etc.
        $outputFilenameTmp = '_' . substr(sha1(mt_rand()), 0, 16);

        $gsPath = $this->gsPath;
        $switches = '-q -dQUIET -dNOSAFER -dBATCH -dNOPAUSE -dNOPROMPT';
        $switches = '-dNOSAFER -dBATCH -dNOPAUSE -dNOPROMPT';

        if (strpos($options['resolution'], 'x') !== false) {
            $res = __('-dPDFFitPage -g{0} -r{1}', $options['resolution'], $options['outputdpi']);
        } else {
            $res = __('-r{0}', $options['resolution']);
        }

        if ($options['pagebox'] == 'MediaBox') {
            $pagebox = '';
        } elseif (in_array($options['pagebox'], $boxTypes)) {
            $pagebox = __('-dUse{0}', $options['pagebox']);
        } else {
            $pagebox = '';
        }

        if ($options['pagelist']) {
            if (is_array($options['pagelist'])) {
                $pagelist = __('-sPageList={0}', implode(",", $options['pagelist']));
            } elseif (is_string($options['pagelist'])) {
                $pagelist = __('-sPageList={0}', $options['pagelist']);
            }
        } else {
            $pagelist = '';
        }

        if (in_array(strtolower($options['format']), ['jpg', 'jpeg'])) {
            $extension = "jpg";

            /*
             * gs devices for JPG
             *
             * jpeg      JPEG format, RGB output
             * jpeggray  JPEG format, gray output
             */
            $jpegDevice = 'jpeg';
            if (in_array(strtolower($options['colorspace']), $colourArray)) {
                $jpegDevice = 'jpeg';
            } elseif (in_array(strtolower($options['colorspace']), $grayArray)) {
                $jpegDevice = 'jpeggray';
            } elseif (in_array(strtolower($options['colorspace']), $monoArray)) {
                $jpegDevice = 'jpeggray';
            }

            $deviceOpts = [
                $jpegDevice,
                intval($options['quality']),
            ];
            $device = __('-sDEVICE={0} -dJPEGQ={1}', $deviceOpts);
        } elseif (in_array(strtolower($options['format']), ['png'])) {
            $extension = "png";

            /*
             * gs devices for PNG
             *
             * pngmono      Monochrome
             * pnggray      8-bit gray
             * png16        4-bit color
             * png256       8-bit color
             * png16m       24-bit color
             * pngalpha     32-bit RGBA color with transparency indicating pixel coverage
             */
            $pngDevice = 'png16m';
            if (in_array(strtolower($options['colorspace']), $colourArray)) {
                $pngDevice = 'png16m';
            } elseif (in_array(strtolower($options['colorspace']), $grayArray)) {
                $pngDevice = 'pnggray';
            } elseif (in_array(strtolower($options['colorspace']), $monoArray)) {
                $pngDevice = 'pngmono';
            } elseif (in_array(strtolower($options['colorspace']), [16, '16'])) {
                $pngDevice = 'png16';
            } elseif (in_array(strtolower($options['colorspace']), [256, '256'])) {
                $pngDevice = 'png256';
            } elseif (in_array(strtolower($options['colorspace']), ['pngalpha', 'alpha', 'rgba'])) {
                $pngDevice = 'pngalpha';
            }

            $deviceOpts = [
                $pngDevice,
            ];
            $device = __('-sDEVICE={0}', $deviceOpts);
        } elseif (in_array(strtolower($options['format']), ['tiff', 'tif'])) {
            $extension = "tif";

            /*
             * gs devices for TIFF
             *
             * tiffgray Produces 8-bit gray output.
             * tiff12nc Produces 12-bit RGB output (4 bits per component).
             * tiff24nc Produces 24-bit RGB output (8 bits per component).
             * tiff48nc Produces 48-bit RGB output (16 bits per component).
             * tiff32nc Produces 32-bit CMYK output (8 bits per component).
             * tiff64nc Produces 64-bit CMYK output (16 bits per component).
             * tiffsep The tiffsep device creates multiple output files
             */
            $tifDevice = 'tiff24nc';
            if (in_array(strtolower($options['colorspace']), $colourArray)) {
                $tifDevice = 'tiff24nc';
            } elseif (in_array(strtolower($options['colorspace']), $grayArray)) {
                $tifDevice = 'tiffgray';
            } elseif (in_array(strtolower($options['colorspace']), $monoArray)) {
                $tifDevice = 'tiffgray';
            } elseif (in_array(strtolower($options['colorspace']), ['tiffgray', 'tiff12nc', 'tiff24nc', 'tiff48nc', 'tiff32nc', 'tiff64nc'])) {
                $tifDevice = $options['colorspace'];
            } elseif (in_array(strtolower($options['colorspace']), ['tiffsep', 'sep', 'separations', 'separation',])) {
                $tifDevice = 'tiffsep';
            }

            $deviceOpts = [
                $tifDevice,
            ];
            $device = __('-sDEVICE={0}', $deviceOpts);
        } else {
            $extension = "jpg";
            $deviceOpts = [
                'jpeg',
                $options['quality'],
            ];
            $device = __('-sDEVICE={0} -dJPEGQ={1}', $deviceOpts);
        }

        if ($options['smoothing'] === true) {
            $smoothing = '-dTextAlphaBits=4 -dGraphicsAlphaBits=4';
        } elseif ($options['smoothing'] === false) {
            $smoothing = '-dTextAlphaBits=1 -dGraphicsAlphaBits=1';
        } elseif ($options['smoothing'] >= 1 && $options['smoothing'] <= 4) {
            $vals = [$options['smoothing'], $options['smoothing']];
            $smoothing = __('-dTextAlphaBits={0} -dGraphicsAlphaBits={1}', $vals);
        } else {
            $smoothing = '-dTextAlphaBits=4 -dGraphicsAlphaBits=4';
        }

        if ($options['outputfolder']) {
            $outputFolder = rtrim($options['outputfolder'], " \t\n\r\0\x0B\\/");
            if (!is_dir($outputFolder)) {
                mkdir($outputFolder, 0777, true);
            }
        } else {
            $outputFolder = pathinfo($pdfPath, PATHINFO_DIRNAME);
        }

        $outputOpts = [
            $outputFolder,
            $outputFilenameTmp,
            $extension,
        ];
        $outputFiles = __('-sOutputFile="{0}\{1}-%d.{2}"', $outputOpts);

        $args = [
            $gsPath,
            $switches,
            $res,
            $pagebox,
            $pagelist,
            $device,
            $smoothing,
            $outputFiles,
            $pdfPath,
        ];
        $command = __('"{0}" {1} {2} {3} {4} {5} {6} {7} "{8}"  2>&1 ', $args);

        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);

        $this->setReturnValue($return_var);
        $this->setReturnMessage($output);


        $foundSeparationNames = [];
        $realPageNumbers = [];
        $gsDefinedPageNumbers = null;
        foreach ($output as $line) {
            //add a space at the end, easier for regex.
            $line = $line . " ";

            //extract real page list
            $re = '/Processing pages (.*?)\./m';
            preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
            if (isset($matches[0][1])) {
                $gsDefinedPageNumbers = $matches[0][1];
                $gsDefinedPageNumbers = explode(',', $gsDefinedPageNumbers);
            }

            //extract real page number
            $re = '/Page (.*?) /m';
            preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
            if (isset($matches[0][1])) {
                $realPageNumbers[] = $matches[0][1];
            }

            //extract separation name
            $re = '/\%\%SeparationName\: (.*?) CMYK/m';
            preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
            if (isset($matches[0][1])) {
                $foundSeparationNames[] = '(' . $matches[0][1] . ')';
            }
        }

        $gsSequentialPageNumber = 1;
        foreach ($realPageNumbers as $k => $realPageNumber) {

            $compiledSeparationNames = ['', '(Cyan)', '(Magenta)', '(Yellow)', '(Black)'];
            $compiledSeparationNames = array_merge($compiledSeparationNames, $foundSeparationNames);

            foreach ($compiledSeparationNames as $s => $compiledSeparationName) {
                $gsOutputOpts = [
                    $outputFolder,
                    $outputFilenameTmp,
                    $gsSequentialPageNumber,
                    $compiledSeparationName,
                    $extension,
                ];
                $gsOutputFile = __('{0}\{1}-{2}{3}.{4}', $gsOutputOpts);

                $realOutputOpts = [
                    $outputFolder,
                    pathinfo($pdfPath, PATHINFO_FILENAME),
                    $realPageNumber,
                    $compiledSeparationName,
                    $extension,
                ];
                $realOutputFile = __('{0}\{1}-{2}{3}.{4}', $realOutputOpts);

                if (is_file($gsOutputFile)) {
                    rename($gsOutputFile, $realOutputFile);
                }

                if (is_file($realOutputFile)) {
                    $imagePaths[] = $realOutputFile;
                }
            }

            $gsSequentialPageNumber++;
        }

        return $imagePaths;
    }

    /**
     * Default options for gs PDF->Image
     *
     * @return array
     */
    private function getDefaultSaveOptions()
    {
        /**
         * format
         * Output format of either PNG or JPG
         *
         * resolution (N or NxN)
         * Resolution in ppi or width x height in pixel, e.g. 72 or 300 or 1024x800 or 256x256
         * If supplied as NxN, the resulting image will fit inside the pixel dimensions of NxN
         *
         * quality
         * 1 - 100. Only applies to JPG image format.
         *
         * pagebox
         * Convert the portion of the defined as either the 'MediaBox', 'TrimBox', 'BleedBox', 'CropBox', 'ArtBox'
         *
         * smoothing
         * true|false|1|2|3|4 (1=antialiasing-off, 4=antialiasing-max)
         *
         * outputfolder
         * If the folder is not supplied, the images will be placed in the same folder as the PDF
         */

        return [
            'format' => 'jpg',
            'resolution' => 72,
            'outputdpi' => 72,
            'quality' => '90',
            'colorspace' => 'rgb',
            'pagebox' => 'MediaBox',
            'pagelist' => null,
            'smoothing' => true,
            'outputfolder' => null,
        ];
    }

    private function getValidatedPageList($pdfPath, $pageList)
    {
        $report = $this->getCallasReport($pdfPath, true, false);
        if (!isset($report['aggregated']['pages']['length'])) {
            return [];
        }
        $pdfPages = range(1, $report['aggregated']['pages']['length']);

        if (empty($pageList)) {
            return $pdfPages;
        }

        return array_intersect($pageList, $pdfPages);
    }

}