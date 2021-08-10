<?php


namespace arajcany\PrePressTricks\Graphics\Ghostscript;


use arajcany\PrePressTricks\Graphics\Common\BaseCommands;
use arajcany\PrePressTricks\Utilities\Boxes;
use arajcany\PrePressTricks\Utilities\Pages;

class GhostscriptCommands extends BaseCommands
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
        parent::__construct();

        if ($gsPath) {
            $this->gsPath = $gsPath;
            $this->setPdfInfoPath();
        } else {
            $command = "where gswin64c";
            $output = [];
            $return_var = '';
            exec($command, $output, $return_var);
            if (isset($output[0])) {
                if (is_file($output[0])) {
                    $this->gsPath = $output[0];
                    $this->setPdfInfoPath();
                }
            }
        }

    }

    /**
     * Check if Callas is alive and working.
     *
     * @return bool
     */
    public function isAlive()
    {
        try {
            $cliVersion = $this->getCliVersion();
            $cliStatus = $this->getCliStatus();

            if (!$cliVersion) {
                return false;
            }

            if (!$cliStatus) {
                return false;
            }

            return true;

        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @param null $gsPath
     * @return GhostscriptCommands
     */
    public function setGsPath($gsPath)
    {
        $this->gsPath = $gsPath;
        $this->setPdfInfoPath();

        return $this;
    }

    /**
     * @return GhostscriptCommands
     */
    public function setPdfInfoPath()
    {
        $gsBinPath = pathinfo($this->gsPath, PATHINFO_DIRNAME);
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
        } else {
            return false;
        }
    }

    /**
     * Get the info string
     *
     * @return false|mixed
     */
    public function getCliStatus()
    {
        return $this->cli("--help");
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
            //try and find existing report files
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
    public function getQuickCheckReport($pdfPath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".report.json";

        if ($useCached) {
            //try and find existing report files
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

        $report = $this->populateQuickCheckReportWithEffectiveGeometry($report);

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
            //try and find existing report files
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


        $report = $this->getQuickCheckReport($pdfPath, $useCached);
        if (empty($report)) {
            return false;
        }

        if (!isset($report['aggregated']['pages']['page'])) {
            return false;
        }
        $report = $this->convertQuickCheckReportToPageSizeGroupsReport($report);


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
            //try and find existing report files
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
            'UserUnit' => 1,
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

                        //todo need to get rid of _eff once the JS GUI is updated
                        if ($rotation == 0 || $rotation == 180) {
                            $pageCompiledData['geometry_eff'][$boxType]['width'] = $pageCompiledData['geometry'][$boxType]['width'];
                            $pageCompiledData['geometry_eff'][$boxType]['height'] = $pageCompiledData['geometry'][$boxType]['height'];
                        } elseif ($rotation == 90 || $rotation == 270) {
                            $pageCompiledData['geometry_eff'][$boxType]['width'] = $pageCompiledData['geometry'][$boxType]['height'];
                            $pageCompiledData['geometry_eff'][$boxType]['height'] = $pageCompiledData['geometry'][$boxType]['width'];
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
        $options = $this->fixSaveAsImageOptions($pdfPath, $options);

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

                $intersectingPages = (new Pages())->rangeExpand($options['pagelist'], ['returnFormat' => 'array']);
                $intersectingPages = array_intersect($intersectingPages, $pages);
                $intersectingPages = implode(',', $intersectingPages);

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
     * Wrapper function to convert a PDF to Separations.
     *
     * A wrapper function is used because gs is not the best at ripping multi page size documents.
     * This wrapper splits such documents into chunks for conversion - based on same page size.
     *
     * NOTE: at this stage GS can only output TIFF separations, so format will always be overridden as TIFF
     *
     * For documentation on $options, see $this->getDefaultSaveOptions().
     *
     * @param $pdfPath
     * @param array $options
     * @return array
     */
    public function savePdfAsSeparations($pdfPath, $options = [])
    {
        $options = $this->fixSaveAsImageOptions($pdfPath, $options);

        $options['format'] = 'tif';
        $options['colorspace'] = 'tiffsep';

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

                $intersectingPages = (new Pages())->rangeExpand($options['pagelist'], ['returnFormat' => 'array']);
                $intersectingPages = array_intersect($intersectingPages, $pages);
                $intersectingPages = implode(',', $intersectingPages);

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
        //we use a tmp image name gs still numbers sequentially 1-n even if you ask for pages 4,7,8 etc.
        $outputFilenameTmp = '_' . substr(sha1(mt_rand()), 0, 16);

        $gsPath = $this->gsPath;
        $switches = '-dNOSAFER -dBATCH -dNOPAUSE -dNOPROMPT';

        if (strpos($options['resolution'], 'x') !== false) {
            $res = __('-dPDFFitPage -g{0} -r{1}', $options['resolution'], $options['outputdpi']);
        } else {
            $res = __('-r{0}', $options['resolution']);
        }

        $pagebox = __('-dUse{0}', $options['pagebox']);

        $pagelist = __('-sPageList={0}', $options['pagelist']);

        if ($options['format'] == 'jpg') {
            $extension = "jpg";
            $deviceOpts = [
                $options['colorspace'],
                intval($options['quality']),
            ];
            $device = __('-sDEVICE={0} -dJPEGQ={1}', $deviceOpts);
        } elseif ($options['format'] == 'png') {
            $extension = "png";
            $deviceOpts = [
                $options['colorspace'],
            ];
            $device = __('-sDEVICE={0}', $deviceOpts);
        } elseif ($options['format'] == 'tif') {
            $extension = "tif";
            $deviceOpts = [
                $options['colorspace'],
            ];
            $device = __('-sDEVICE={0}', $deviceOpts);
        }

        $vals = [$options['smoothing'], $options['smoothing']];
        $smoothing = __('-dTextAlphaBits={0} -dGraphicsAlphaBits={1}', $vals);

        $outputFolder = $options['outputfolder'];

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
                //$foundSeparationNames[] = '_sep_' . $matches[0][1] . '';
            }
        }

        $gsSequentialPageNumber = 1;
        $imagePaths = [];
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
                    //str_pad($realPageNumber, 4, '0', STR_PAD_LEFT), //pad page number to 4 digits like Callas
                    //str_replace(")", "", str_replace("(", "_sep_", $compiledSeparationName)), //separation naming like Callas
                    $realPageNumber,
                    $compiledSeparationName,
                    $extension,
                ];
                $realOutputFile = __('{0}\{1}_{2}{3}.{4}', $realOutputOpts);

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
     * Default options for PDF->Image
     *
     * @return array
     */
    private function getDefaultSaveAsImageOptions()
    {
        /**
         * Formats and Compression (compression a.k.a quality)
         *
         * Callas = 'compression' [JPEG_minimum = high quality] [JPEG_maximum = low quality]
         * Ghostscript = 'quality' of 0-100 [0 = low quality] [100 = high quality]
         *
         * JPEG: JPEG_minimum, JPEG_low, JPEG_medium, JPEG_high, JPEG_maximum (default: JPEG_medium)
         * PDF:  JPEG_minimum, JPEG_low, JPEG_medium, JPEG_high, JPEG_maximum, PDF_Flate (default: JPEG_medium)
         * TIFF: TIFF_None, TIFF_LZW, TIFF_FLATE (default: TIFF_LZW)
         * PNG: n/a
         * ANY OF ABOVE: 0 -100 (will be translated to string)
         *
         * Formats and Colourspaces
         * JPEG: RGB, Gray, CMYK (default: RGB)
         * PDF:  RGB, Gray, CMYK (default: RGB)
         * TIFF: RGB, Gray, CMYK, Multichannel (default: RGB)
         * PNG:  RGB, RGBA, Gray (default: RGB)
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

    /**
     * Take the standard $options and converts them to Ghostscript flavoured $options for ripping a PDF
     *
     * @param string $pdfPath
     * @param array $options
     * @return array
     */
    private function fixSaveAsImageOptions($pdfPath, $options)
    {
        $defaultOptions = $this->getDefaultSaveAsImageOptions();
        $options = array_merge($defaultOptions, $options);

        //fix resolution
        $options['resolution'] = strtolower($options['resolution']);
        $options['resolution'] = str_replace(['*', "_", "-", "/", "+"], 'x', $options['resolution']);

        //fix output dpi
        if (strpos($options['resolution'], 'x') === false) {
            //set the output DPI to the ripping DPI (needed for Ghostscript only but here for compatibility)
            $options['outputdpi'] = $options['resolution'];
        } else {
            //Callas automatically calculates the output DPI when NxN is used so we can leave it null
            $options['outputdpi'] = null;
        }

        //fix pagelist
        $Pages = new Pages();
        $pageList = $Pages->rangeExpand($options['pagelist'], ['returnFormat' => 'array']);
        $pageList = $this->getValidatedPageList($pdfPath, $pageList);
        $pageList = $Pages->rangeCompact($pageList);
        if (empty($pageList)) {
            $options['pagelist'] = null;
        } else {
            $options['pagelist'] = $pageList;
        }

        //fix box
        $masterBoxes = [
            'MediaBox' => ['media box', 'mediabox', 'media'],
            'TrimBox' => ['trim box', 'trimbox', 'trim'],
            'BleedBox' => ['bleed box', 'bleedbox', 'bleed'],
            'CropBox' => ['crop box', 'cropbox', 'crop'],
            'ArtBox' => ['art box', 'artbox', 'art']
        ];
        $options['pagebox'] = $this->getMasterKeyFromUnknown($masterBoxes, $options['pagebox'], 'MediaBox');

        //fix format
        $masterFormats = [
            'jpg' => ['jpeg', 'jpg'],
            'tif' => ['tif', 'tiff'],
            'png' => ['png'],
        ];
        $options['format'] = $this->getMasterKeyFromUnknown($masterFormats, $options['colorspace'], 'png');

        //fix colorspace
        /*
         * gs devices for JPG
         *
         * jpeg      JPEG format, RGB output
         * jpeggray  JPEG format, gray output
         */
        $masterColorspaceJpg = [
            'jpeg' => ['color', 'colour', 'col', 'c', 'rgb', 'rgba', 'process', 'cmyk'],
            'jpeggray' => ['grey', 'gray', 'greyscale', 'grayscale', 'g', 'k', 'mono', 'monochrome', 'm'],
        ];
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
        $masterColorspacePng = [
            'pngmono' => ['mono', 'monochrome', 'm'],
            'pnggray' => ['grey', 'gray', 'greyscale', 'grayscale', 'g', 'k',],
            'png16' => [16, 'web'],
            'png256' => [256, 'dither'],
            'png16m' => ['16m', 'color', 'colour', 'col', 'c', 'rgb', 'process', 'cmyk',],
            'pngalpha' => ['rgba'],
        ];
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
        $masterColorspaceTif = [
            'tiffgray' => ['grey', 'gray', 'greyscale', 'grayscale', 'g', 'k', 'mono', 'monochrome', 'm'],
            'tiff12nc' => [12,],
            'tiff24nc' => [24, 'color', 'colour', 'col', 'c', 'rgb', 'rgba',],
            'tiff48nc' => [48],
            'tiff32nc' => [32, 'process', 'cmyk'],
            'tiff64nc' => [64],
            'tiffsep' => ['sep', 'separations'],
        ];
        if ($options['format'] == 'jpg') {
            $options['colorspace'] = $this->getMasterKeyFromUnknown($masterColorspaceJpg, $options['colorspace'], 'jpeg');
        } elseif ($options['format'] == 'png') {
            $options['colorspace'] = $this->getMasterKeyFromUnknown($masterColorspacePng, $options['colorspace'], 'png16m');
        } elseif ($options['format'] == 'tif') {
            $options['colorspace'] = $this->getMasterKeyFromUnknown($masterColorspaceTif, $options['colorspace'], 'tiff24nc');
        }

        //fix quality
        $masterCompressionJpg = [
            'JPEG_minimum' => ['min', 'minimum'] + range(90, 100),
            'JPEG_low' => ['low'] + range(51, 90),
            'JPEG_medium' => ['med', ' medium'] + range(21, 50),
            'JPEG_maximum' => ['max', 'maximum'] + range(0, 20),
        ];
        $masterCompressionTif = [
            'none' => ['none', null],
            'lzw' => ['lzw', 'min', 'minimum', 'med', ' medium', 'max', 'maximum'] + range(0, 100),
            'crle' => ['crle'],
            'g3' => ['g3'],
            'g4' => ['g4'],
            'pack' => ['pack'],
        ];
        if ($options['format'] == 'jpg') {
            $options['quality'] = $this->getMasterKeyFromUnknown($masterCompressionJpg, $options['quality'], 'jpeg');
        } elseif ($options['format'] == 'png') {
            $options['quality'] = null; //PNG does not have compression options
        } elseif ($options['format'] == 'tif') {
            $options['quality'] = $this->getMasterKeyFromUnknown($masterCompressionTif, $options['quality'], 'lzw');
        }

        //fix smoothing
        $masterSmoothing = [
            '1' => ['1', false],
            '2' => ['2'],
            '3' => ['3'],
            '4' => ['4', true],
        ];
        $options['smoothing'] = $this->getMasterKeyFromUnknown($masterSmoothing, $options['smoothing'], '1');

        //fix output folder
        if ($options['outputfolder']) {
            $options['outputfolder'] = rtrim($options['outputfolder'], " \t\n\r\0\x0B\\/");
            if (!is_dir($options['outputfolder'])) {
                mkdir($options['outputfolder'], 0777, true);
            }
        } else {
            $options['outputfolder'] = pathinfo($pdfPath, PATHINFO_DIRNAME);
        }

        return $options;
    }

    /**
     * Compares the requested page range to actual pdf and only returns a valid range.
     *
     * @param string $pdfPath
     * @param array $pageList
     * @return array
     */
    private function getValidatedPageList($pdfPath, $pageList)
    {
        $report = $this->getQuickCheckReport($pdfPath, true, false);
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