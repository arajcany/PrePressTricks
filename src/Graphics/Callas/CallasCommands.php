<?php


namespace arajcany\PrePressTricks\Graphics\Callas;


use arajcany\PrePressTricks\Graphics\Common\BaseCommands;
use arajcany\PrePressTricks\Utilities\Pages;
use Cake\Utility\Hash;
use Cake\Utility\Xml;

class CallasCommands extends BaseCommands
{
    private $callasPath = null;
    private $callasQuickCheckFilters = ['$' => []];
    private $tmpDir;

    /**
     * CallasCommands constructor.
     * @param null $callasPath
     */
    public function __construct($callasPath = null)
    {
        parent::__construct();

        if ($callasPath) {
            $this->callasPath = $callasPath;
        } else {
            $command = "where pdftoolbox";
            $output = [];
            $return_var = '';
            exec($command, $output, $return_var);
            if (isset($output[0])) {
                if (is_file($output[0])) {
                    $this->callasPath = $output[0];
                }
            }
        }

        if (defined('TMP')) {
            $this->tmpDir = TMP;
        } else {
            $this->tmpDir = __DIR__ . '/../../../tmp/';
            if (!is_dir($this->tmpDir)) {
                mkdir($this->tmpDir, 0777, true);
            }
        }

        $this->resetCallasQuickCheckFilters();
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

            foreach ($cliStatus as $status) {
                if (strpos($status, "Serialization") !== false) {
                    if (strpos($status, "Trial	Expired") !== false) {
                        return false;
                    } elseif (strpos($status, "No License") !== false) {
                        return false;
                    }
                }
            }

            return true;

        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @param null $callasPath
     * @return CallasCommands
     */
    public function setCallasPath($callasPath)
    {
        $this->callasPath = $callasPath;

        return $this;
    }

    /**
     * Set/reset the default QuickCheck filter
     * See https://help.callassoftware.com/m/pdftoolbox/l/1153811-aggregated-data-elements-and-output
     *
     * @param array $callasQuickCheckFilter
     * @return CallasCommands
     */
    public function resetCallasQuickCheckFilters()
    {
        $this->callasQuickCheckFilters = ['$' => []];
        $this->addCallasQuickCheckFilter('$.aggregated.env', true);
        $this->addCallasQuickCheckFilter('$.aggregated.file', true);
        $this->addCallasQuickCheckFilter('$.aggregated.doc', true);

        $this->addCallasQuickCheckFilter('$.aggregated.pages.length', true);
        $this->addCallasQuickCheckFilter('$.aggregated.pages.page.info.pagenum', true);
        $this->addCallasQuickCheckFilter('$.aggregated.pages.page.geometry', true);

        return $this;
    }

    public function addCallasQuickCheckFilter($path, $values)
    {
        $this->callasQuickCheckFilters = Hash::insert($this->callasQuickCheckFilters, $path, $values);
    }

    public function removeCallasQuickCheckFilter($path)
    {
        $this->callasQuickCheckFilters = Hash::remove($this->callasQuickCheckFilters, $path);
        $this->callasQuickCheckFilters = Hash::filter($this->callasQuickCheckFilters, function ($var) {
            return $var === true || $var === false || (is_array($var) && $var !== []);
        });
    }

    /**
     * Return the config as a string and optionally save to the FSO - must be fully qualified path + filename + extension.
     *
     * @param $savePath
     * @return string
     */
    public function getCallasQuickCheckFilters($savePath = null): string
    {
        $flattened = Hash::flatten($this->callasQuickCheckFilters);

        $compiled = '';
        $compiled .= '$.direct: false' . "\r\n";
        $compiled .= '$.aggregated: false' . "\r\n";

        foreach ($flattened as $k => $v) {

            if ($v === true) {
                $v = 'true';
            } elseif ($v === false) {
                $v = 'false';
            }

            $compiled .= $k . ": " . $v . "\r\n";
        }


        if ($savePath) {
            file_put_contents($savePath, $compiled);
        }

        return $compiled;
    }

    /**
     * Get the version string
     *
     * @return false|mixed
     */
    public function getCliVersion()
    {
        $version = $this->cli("--version");
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
            $this->callasPath,
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
     * @param $trialRegistrationName
     * @param $trialCompanyName
     * @param string $returnFormat
     * @return array|false|string
     */
    public function trialCallas($trialRegistrationName, $trialCompanyName, $returnFormat = 'array')
    {
        $options = [
            $this->callasPath,
            $trialRegistrationName,
            $trialCompanyName,
        ];

        $cmd = __('"{0}" -k "{1}" "{2}" trial', $options);
        exec($cmd, $out, $ret);

        if ($ret == 0) {
            $status = "OK";
        } else {
            $status = "KO";
        }

        $resp = [
            'status' => $status,
            'return' => $ret,
            'message' => $out,
        ];

        if (strtolower($returnFormat) == 'text' || strtolower($returnFormat) == 'txt') {
            $responseData = print_r($resp, true);
        } elseif (strtolower($returnFormat) == 'json') {
            $responseData = json_encode($resp, JSON_PRETTY_PRINT);
        } else {
            $responseData = $resp;
        }

        return $responseData;
    }

    /**
     * @param $activationPDF
     * @param string $returnFormat
     * @return array|false|string|true
     */
    public function registerCallas($activationPDF, $returnFormat = 'array')
    {
        if (!is_file($activationPDF)) {
            $resp = [
                'status' => 'KO',
                'return' => 1,
                'message' => 'Could not find the activation.pdf file.'
            ];
        } else {
            $options = [
                $this->callasPath,
                $activationPDF,
            ];
            $cmd = __('"{0}" --activate "{1}"', $options);
            exec($cmd, $out, $ret);
            //debug($cmd);
            //debug($out);
            //debug($ret);

            if ($ret == 0) {
                $status = "OK";
            } else {
                $status = "KO";
            }

            $resp = [
                'status' => $status,
                'return' => $ret,
                'message' => $out,
            ];
        }

        if (strtolower($returnFormat) == 'text' || strtolower($returnFormat) == 'txt') {
            $responseData = print_r($resp, true);
        } elseif (strtolower($returnFormat) == 'json') {
            $responseData = json_encode($resp, JSON_PRETTY_PRINT);
        } else {
            $responseData = $resp;
        }

        return $responseData;
    }


    /**
     * Get a PDF report in the CLI Applications native format.
     *
     * This is **really** basic report and only practical use is the page count.
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
            $this->callasPath,
            $pdfPath,
        ];
        $command = __('"{0}" --quickpdfinfo "{1}" ', $args);
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
     * Get a PDF report in Callas Quick Check JSON format.
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

        //The Callas CLI saves the report to disk as opposed to returning the report in the console
        $rnd1 = sha1(mt_rand());
        $tmpOutputFile = $this->tmpDir . $rnd1 . ".report.json";
        $tmpQuickCheckConfigFile = $this->tmpDir . $rnd1 . ".quickcheck.cfg";
        $this->getCallasQuickCheckFilters($tmpQuickCheckConfigFile);

        $args = [
            $this->callasPath,
            $tmpOutputFile,
            $tmpQuickCheckConfigFile,
            $pdfPath,
        ];
        $command = __('"{0}" --quickcheck -w -o="{1}" "{2}" "{3}"', $args);
        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);

        //retrieve the report from the tmp output location - delete tmp files
        $report = file_get_contents($tmpOutputFile);
        $report = json_decode($report, JSON_OBJECT_AS_ARRAY);
        unlink($tmpOutputFile);
        unlink($tmpQuickCheckConfigFile);

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

        $this->setReturnMessage('PDF JSON report generated');
        $this->setReturnValue($return_var);

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
     * Report on the page separations in the PDF.
     * Handy for when you need to get the spot colours.
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

        //we can't rely on a cached report as it may not contain the spotcolors
        $this->addCallasQuickCheckFilter('$.aggregated.pages.page.resources.color.spotcolors', true);
        $report = $this->getQuickCheckReport($pdfPath, false);
        $this->removeCallasQuickCheckFilter('$.aggregated.pages.page.resources.color.spotcolors');
        if (empty($report)) {
            return false;
        }

        $report = $this->convertCallasJsonReportToSeparationsJsonReport($report);

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


    public function getImagesReport($pdfPath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".images.json";

        if ($useCached) {
            //try and find existing report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached Images JSON report');
                $this->setReturnValue(0);
                return json_decode(file_get_contents($defaultSavePath), JSON_OBJECT_AS_ARRAY);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached Images JSON report');
                $this->setReturnValue(0);
                return json_decode(file_get_contents($saveReport), JSON_OBJECT_AS_ARRAY);
            }
        }


        $report = $this->getImagesReportXml($pdfPath, $useCached);
        if (empty($report)) {
            return false;
        }

        $report = $this->convertCallasPreflightXmlReportToJsonImagesReport($report);

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

        $this->setReturnMessage('Images report generated');
        $this->setReturnValue(0);

        return $report;
    }

    /**
     * Report on the images in the PDF.
     *
     * Use it to get the DPI of images. This report my ba a little hard to read as the native XML output
     * has nested entries. use $this->getImagesReport() to get a JSON version of this report that has been flattened.
     *
     * @param $pdfPath
     * @param bool $useCached
     * @param bool $saveReport
     * @return array|false
     */
    public function getImagesReportXml($pdfPath, $useCached = true, $saveReport = false)
    {
        $preflightProfile = __DIR__ . DIRECTORY_SEPARATOR."ImagesHigher.kfp";
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".images_raw.xml";

        if ($useCached) {
            //try and find existing report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached XML report');
                $this->setReturnValue(0);
                return file_get_contents($defaultSavePath);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached XML report');
                $this->setReturnValue(0);
                return file_get_contents($saveReport);
            }
        }

        //The Callas CLI saves the report to disk as opposed to returning the report in the console
        $rnd1 = sha1(mt_rand());
        $tmpOutputFile = $this->tmpDir . $rnd1 . ".report.xml";

        $args = [
            $this->callasPath,
            $preflightProfile,
            $pdfPath,
            $tmpOutputFile
        ];
        $command = __('"{0}" -r=xml,PATH="{3}" "{1}" "{2}" ', $args);
        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);

        //retrieve the report from the tmp output location - delete tmp files
        $report = file_get_contents($tmpOutputFile);
        //$report = json_decode($report, JSON_OBJECT_AS_ARRAY);
        unlink($tmpOutputFile);

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

        $this->setReturnMessage('Images XML report generated');
        $this->setReturnValue($return_var);

        return $report;
    }

    /**
     * Formats a quickcheck report as a colour separation report.
     * Return format is an array, be sure to json_encode() before saving this report to FSO.
     *
     * @param $reportStringOrPath
     * @return array[]|false
     */
    private function convertCallasJsonReportToSeparationsJsonReport($reportStringOrPath)
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

        $sepReport = [];
        $currentPageNumber = null;
        $baseSeps = ['Cyan', 'Magenta', 'Yellow', 'Black'];
        foreach ($rawReport['aggregated']['pages']['page'] as $k => $page) {
            $pageNumber = $page['info']['pagenum'];
            $sepReport[$pageNumber] = $baseSeps;
            foreach ($page['resources']['color']['spotcolors']['spotcolor'] as $spotcolor) {
                $sepReport[$pageNumber][] = $spotcolor['name'];
            }
        }

        return $sepReport;
    }

    /**
     * Formats a preflight XML report as an easy to read JSON report.
     * Return format is an array, be sure to json_encode() before saving this report to FSO.
     *
     * @param $reportStringOrPath
     * @return array[]|false
     */
    private function convertCallasPreflightXmlReportToJsonImagesReport($reportStringOrPath)
    {
        if (is_string($reportStringOrPath) && is_file($reportStringOrPath)) {
            $rawReport = file_get_contents($reportStringOrPath);
        } elseif (is_string($reportStringOrPath)) {
            $rawReport = $reportStringOrPath;
        } elseif (is_array($reportStringOrPath)) {
            $rawReport = $reportStringOrPath;
        } else {
            return false;
        }

        $reportHashed = Xml::toArray(Xml::build($rawReport));

        //map the colour spaces
        $colorspaces = Hash::extract($reportHashed, "report.document.resources.colorspaces.colorspace");
        if (array_keys($colorspaces) !== range(0, count($colorspaces) - 1)) {
            $colorspaces = [$colorspaces];
        }
        $colourspaceMap = [];
        foreach ($colorspaces as $colorspace) {
            $colourspaceMap[$colorspace['@id']] = $colorspace;
        }

        //map the images
        $images = Hash::extract($reportHashed, "report.document.resources.images.image");
        if (array_keys($images) !== range(0, count($images) - 1)) {
            $images = [$images];
        }
        $imageMap = [];
        foreach ($images as $image) {
            $imageMap[$image['@id']] = $image;
        }


        $hits = Hash::extract($reportHashed, 'report.results.hits');
        $hits = Hash::flatten(Hash::insert([], 'report.results.hits', $hits));

        //look at all the hits and extract paths that relates to Image hits
        $imagePaths = [];
        foreach ($hits as $imagePath => $value) {
            if (in_array($value, array_keys($imageMap))) {
                $pathTrimmed = explode('.', $imagePath);
                $pathTrimmed = array_splice($pathTrimmed, 0, -2);
                $pathTrimmed = implode('.', $pathTrimmed);
                $imagePaths[$value] = $pathTrimmed;
            }
        }

        //extract those paths and compile the final JSON
        $extractedImages = [];
        foreach ($imagePaths as $imageId => $imagePath) {
            $imageMeta = array_merge(['@image_id' => $imageId], $imageMap[$imageId], Hash::get($reportHashed, $imagePath));
            $colorspace = $colourspaceMap[$imageMeta['@colorspace']];
            $imageMeta['@colorspace'] = $colorspace;
            $extractedImages[$imageId] = $imageMeta;
        }

        ksort($extractedImages);
        return $extractedImages;
    }


    /**
     * Convert a PDF to Images.
     *
     * This function compiles the options into a CMD.
     * The error checking of $options is handed off to  $this->fixSaveAsImageOptions()
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

        $callasPath = $this->callasPath;

        $switches = '--saveasimg -w --digits=0';

        $resolution = __('--resolution={0}', $options['resolution']);

        $pagebox = __('--pagebox={0}', $options['pagebox']);

        if ($options['pagelist']) {
            $pagelist = __('--pagerange={0}', $options['pagelist']);
        } else {
            $pagelist = '';
        }

        $format = __('--imgformat={0}', $options['format']);

        if ($options['quality']) {
            $quality = __('--compression={0}', $options['quality']);
        } else {
            $quality = "";
        }

        $colourspace = __('--colorspace={0}', $options['colorspace']);

        $smoothing = __('--smoothing={0}', $options['smoothing']);

        $outputFolder = __('--outputfolder="{0}"', $options['outputfolder']);

        $args = [
            $callasPath,
            $switches,
            $resolution,
            $format,
            $quality,
            $colourspace,
            $pagebox,
            $pagelist,
            $smoothing,
            $outputFolder,
            $pdfPath,
        ];
        $command = __('"{0}" {1} {2} {3} {4} {5} {6} {7} {8} {9} "{10}"  2>&1 ', $args);

        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);

        $this->setReturnValue($return_var);
        $this->setReturnMessage($output);

        $imagePaths = [];
        foreach ($output as $line) {
            if (strpos($line, "Output") === 0) {
                $imagePath = trim(str_replace("Output", "", $line));
                $imagePaths[] = $imagePath;
            }
        }

        return $imagePaths;
    }


    /**
     * Convert a PDF to Colour Separations.
     *
     * This function compiles the options into a CMD.
     * The error checking of $options is handed off to  $this->fixSaveAsImageOptions()
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

        $callasPath = $this->callasPath;

        $switches = '--visualizer --part=sep_process,sep_spot -w';

        $resolution = __('--resolution={0}', $options['resolution']);

        //pagebox not supported in visualiser, maybe in the future so leaving stub coded here
        $pagebox = __('--pagebox={0}', $options['pagebox']);
        $pagebox = "";

        //pagelist like 1,3,7-8 not properly supported in visualiser, maybe in the future so leaving stub code here
        //see below how we loop the pagelist
        //if ($options['pagelist']) {
        //    $pagelist = __('--pagerange={0}', $options['pagelist']);
        //} else {
        //    $pagelist = '';
        //}
        $pagelist = "";

        $format = __('--imgformat={0}', $options['format']);

        if ($options['quality']) {
            $quality = __('--compression={0}', $options['quality']);
        } else {
            $quality = "";
        }

        $colourspace = __('--colorspace={0}', $options['colorspace']);

        //smoothing not supported in visualiser, maybe in the future so leaving stub coded here
        $smoothing = __('--smoothing={0}', $options['smoothing']);
        $smoothing = "";

        $outputFolder = __('--outputfolder="{0}"', $options['outputfolder']);

        //pagelist is supported by breaking into min-max parts e.g. 1-2,5,7-8 must be processed in 3 loops
        $output = [];
        $ranges = (new Pages())->rangeCompact($options['pagelist'], ['returnFormat' => 'array', 'duplicateStringSingles' => true]);
        foreach ($ranges as $range) {
            $range = $range['lower'] . "-" . $range['upper'];
            $pagelist = __('--pagerange={0}', $range);

            $args = [
                $callasPath,
                $switches,
                $resolution,
                $format,
                $quality,
                $colourspace,
                $pagebox,
                $pagelist,
                $smoothing,
                $outputFolder,
                $pdfPath,
            ];
            $command = __('"{0}" {1} {2} {3} {4} {5} {6} {7} {8} {9} "{10}"  2>&1 ', $args);

            $return_var = '';
            exec($command, $output, $return_var);

            $this->setReturnValue($return_var);
            $this->setReturnMessage($output);
        }

        $imagePaths = [];
        foreach ($output as $line) {
            if (strpos($line, "Output") === 0) {
                $imagePath = trim(str_replace("Output", "", $line));
                $imagePaths[] = $imagePath;
            }
        }

        //rename files like Ghostscript because it's nicer.
        foreach ($imagePaths as $k => $imagePath) {
            $pdfFileName = pathinfo($pdfPath, PATHINFO_FILENAME);
            $tmpImageDir = explode($pdfFileName, $imagePath)[0];
            $tmpImageName = explode("_sep_", explode($pdfFileName, $imagePath)[1]);
            $tmpPageNumber = intval(trim($tmpImageName[0], "_"));
            $tmpColour = explode(".", $tmpImageName[1])[0];
            $tmpExtension = explode(".", $tmpImageName[1])[1];
            $renameTo = [$tmpImageDir, $pdfFileName, "_", $tmpPageNumber, "(", $tmpColour, ").", $tmpExtension];
            $renameTo = implode("", $renameTo);

            rename($imagePath, $renameTo);
            $imagePaths[$k] = $renameTo;
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
     * Take the standard $options and converts them to Callas flavoured $options for ripping a PDF
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
            'MEDIABOX' => ['media box', 'mediabox', 'media'],
            'TRIMBOX' => ['trim box', 'trimbox', 'trim'],
            'BLEEDBOX' => ['bleed box', 'bleedbox', 'bleed'],
            'CROPBOX' => ['crop box', 'cropbox', 'crop'],
            'ARTBOX' => ['art box', 'artbox', 'art']
        ];
        $options['pagebox'] = $this->getMasterKeyFromUnknown($masterBoxes, $options['pagebox'], 'MEDIABOX');

        //fix format
        $masterFormats = [
            'JPEG' => ['jpg', 'jpeg'],
            'PDF' => ['pdf'],
            'TIFF' => ['tif', 'tiff'],
            'PNG' => ['png'],
        ];
        $options['format'] = $this->getMasterKeyFromUnknown($masterFormats, $options['format'], 'PNG');

        //fix colourspace
        $masterColorspace = [
            'RGB' => ['color', 'colour', 'col', 'c', 'rgb',],
            'RGBA' => ['rgba'],
            'Gray' => ['grey', 'gray', 'greyscale', 'grayscale', 'g', 'k', 'mono', 'monochrome', 'm'],
            'CMYK' => ['process', 'cmyk',]
        ];
        $options['colorspace'] = $this->getMasterKeyFromUnknown($masterColorspace, $options['colorspace'], 'RGB');

        //fix quality
        $masterCompressionJpg = [
            'JPEG_minimum' => ['min', 'minimum'] + range(90, 100),
            'JPEG_low' => ['low'] + range(51, 90),
            'JPEG_medium' => ['med', ' medium'] + range(21, 50),
            'JPEG_maximum' => ['max', 'maximum'] + range(0, 20),
        ];
        $masterCompressionPdf = [
            'JPEG_minimum' => ['min', 'minimum'] + range(90, 100),
            'JPEG_low' => ['low'] + range(51, 90),
            'JPEG_medium' => ['med', ' medium'] + range(21, 50),
            'JPEG_maximum' => ['max', 'maximum'] + range(0, 20),
            'PDF_Flate' => ['flate'],
        ];
        $masterCompressionTif = [
            'TIFF_None' => ['none'] + range(0, 100),
            'TIFF_LZW' => ['lzw', 'min', 'minimum', 'med', ' medium', 'max', 'maximum'],
            'TIFF_FLATE' => ['flate'],
        ];
        if ($options['format'] === "PNG") {
            $options['quality'] = null; //PNG does not have compression options
        } elseif ($options['format'] === "JPEG") {
            $options['quality'] = $this->getMasterKeyFromUnknown($masterCompressionJpg, $options['quality'], 'JPEG_medium');
        } elseif ($options['format'] === "TIFF") {
            $options['quality'] = $this->getMasterKeyFromUnknown($masterCompressionTif, $options['quality'], 'TIFF_LZW');
        } elseif ($options['format'] === "PDF") {
            $options['quality'] = $this->getMasterKeyFromUnknown($masterCompressionPdf, $options['quality'], 'JPEG_medium');
        }

        //fix smoothing
        $masterSmoothing = [
            'NONE' => ['none', 1, false],
            'ALL' => ['all', 2, 3, 4, true],
            'LINES' => ['lines'],
            'IMAGES' => ['images'],
            'TEXT' => ['text'],
            'NTLH' => ['ntlh']
        ];
        $options['smoothing'] = $this->getMasterKeyFromUnknown($masterSmoothing, $options['smoothing'], 'NONE');

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
