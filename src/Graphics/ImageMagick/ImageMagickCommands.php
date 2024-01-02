<?php


namespace arajcany\PrePressTricks\Graphics\ImageMagick;


use arajcany\PrePressTricks\Graphics\Common\GetCommands;
use Imagick;
use ImagickException;
use ImagickPixel;
use Intervention\Image\ImageManager;
use Throwable;

class ImageMagickCommands
{
    private $imPath = null;
    private $returnValue = null;
    private $returnMessage = null;

    /**
     * ImageMagickCommands constructor.
     * @param null $imPath
     */
    public function __construct($imPath = null)
    {
        if ($imPath) {
            $this->imPath = $imPath;
        } else {
            $command = "where magick";
            $output = [];
            $return_var = '';
            exec($command, $output, $return_var);
            if (isset($output[0])) {
                if (is_file($output[0])) {
                    $this->imPath = $output[0];
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
     * @param null $imPath
     * @return ImageMagickCommands
     */
    public function setImPath($imPath)
    {
        $this->imPath = $imPath;

        return $this;
    }

    /**
     * @return bool
     * @throws ImagickException
     */
    public function isImExtension()
    {
        try {
            $image = new Imagick();
            $image->newImage(1, 1, new ImagickPixel('#ffffff'));
            $image->setImageFormat('png');
            $pngData = $image->getImagesBlob();
        } catch (Throwable $exception) {
            $pngData = '';
        }

        return strpos($pngData, "\x89PNG\r\n\x1a\n") === 0 ? true : false;
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
     * Generic function to run a command
     *
     * @param $cliCommand
     * @return false|mixed
     */
    private function cli($cliCommand)
    {
        $options = [
            $this->imPath,
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
     * Get a IMAGE report in the Native PHP Extension format.
     *
     * @param $imagePath //path to the IMAGE file
     * @param bool $useCached //if true, will try and use a local save copy
     * @param bool|string $saveReport //if true, will save with .identify.json as the extension. If a path, save it to that path - must be fully qualified path + filename + extension.
     * @return array|false
     */
    public function getIdentifyReportViaExtension($imagePath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($imagePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($imagePath, PATHINFO_FILENAME) . ".identify.json";

        if ($useCached) {
            //try and find existing report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached IDENTIFY report');
                $this->setReturnValue(0);
                return file_get_contents($defaultSavePath);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached IDENTIFY report');
                $this->setReturnValue(0);
                return file_get_contents($saveReport);
            }
        }

        try {
            $imageReport = new Imagick($imagePath);
            $output = $imageReport->identifyImage(true);
        } catch (Throwable $throwable) {
            $this->setReturnValue(1);
            $this->setReturnMessage('Failed to use the Imagick extension to create a report');
            return false;
        }

        if (isset($output['rawOutput'])) {
            $raw = $output['rawOutput'];
            $raw = (new IdentifyParser())->parse($raw)->toArray();
            $output['rawOutput'] = $raw;
        }

        $report = json_encode($output, JSON_PRETTY_PRINT);

        $this->setReturnValue(0);
        $this->setReturnMessage($output);

        if ($saveReport) {
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $report);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                $this->mkdirWithCheck($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $report);
                }
            }
        }

        $this->setReturnMessage('Identify report generated');

        return $report;
    }


    /**
     * Get a IMAGE report in the CLI Applications native format.
     *
     * @param $imagePath //path to the IMAGE file
     * @param bool $useCached //if true, will try and use a local save copy
     * @param bool|string $saveReport //if true, will save with .identify.txt as the extension. If a path, save it to that path - must be fully qualified path + filename + extension.
     * @return string
     */
    public function getIdentifyReportViaCli($imagePath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($imagePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($imagePath, PATHINFO_FILENAME) . ".identify.txt";

        if ($useCached) {
            //try and find existing report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached IDENTIFY report');
                $this->setReturnValue(0);
                return file_get_contents($defaultSavePath);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached IDENTIFY report');
                $this->setReturnValue(0);
                return file_get_contents($saveReport);
            }
        }

        $verbosity = '-verbose';

        $args = [
            $this->imPath,
            'identify',
            $verbosity,
            $imagePath,
        ];

        $command = __('"{0}" {1} {2} "{3}" ', $args);

        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);
        $report = implode("\r\n", $output);

        $this->setReturnValue($return_var);
        $this->setReturnMessage($output);

        if ($saveReport) {
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $report);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                $this->mkdirWithCheck($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $report);
                }
            }
        }

        $this->setReturnMessage('Identify report generated');
        $this->setReturnValue($return_var);

        return $report;
    }

    /**
     * Get a HISTOGRAM report in the CLI Applications native format.
     * To some extent this report is redundant as the histogram is included in $this->getIdentifyReportViaCli()
     *
     * @param $imagePath
     * @param bool $useCached
     * @param false $saveReport
     * @return false|string
     */
    public function getHistogramJson($imagePath, $useCached = true, $saveReport = false)
    {
        $defaultSavePath = pathinfo($imagePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($imagePath, PATHINFO_FILENAME) . ".histogram.json";

        if ($useCached) {
            //try and find existing report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached HISTOGRAM report');
                $this->setReturnValue(0);
                return file_get_contents($defaultSavePath);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached HISTOGRAM report');
                $this->setReturnValue(0);
                return file_get_contents($saveReport);
            }
        }

        $identifyReport = $this->getIdentifyReportViaCli($imagePath, $useCached, $saveReport);
        $identifyReport = (new IdentifyParser())->parse($identifyReport)->toArray();

        if (!isset($identifyReport['Histogram'])) {
            return false;
        }

        $geometry = explode("+", str_replace("x", "+", $identifyReport['Geometry']));
        $geometry = [$geometry[0], $geometry[1]];
        $resolution = explode("x", $identifyReport['Resolution']);
        $colour_space = $identifyReport['Colorspace'];
        $units = $identifyReport['Units'];

        $histogramReport = [];

        foreach ($identifyReport['Histogram'] as $colourValues => $pixelCount) {
            $colourValues = explode(" ", $colourValues);

            $histogramReport[] = [
                'colour_space' => $colour_space,
                'geometry' => $geometry,
                'resolution' => $resolution,
                'units' => $units,
                'pixels' => $pixelCount,
                'colour_value' => explode(',', trim($colourValues[0], '()')),
                'hex' => $colourValues[1],
                'name' => $colourValues[2],
            ];
        }

        $report = json_encode($histogramReport, JSON_PRETTY_PRINT);

        if ($saveReport) {
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $report);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                $this->mkdirWithCheck($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $report);
                }
            }
        }

        $this->setReturnMessage('Histogram report generated');
        $this->setReturnValue($report);

        return $report;
    }

    /**
     * Analyse PDF Separations toner/ink usage.
     *
     * @param $pdfPath
     * @param bool $useCached
     * @param false $saveReport
     * @param array $ripOptions
     * @param array $analysisOptions
     * @return false|mixed|string
     */
    public function analysePdfSeparations($pdfPath, $useCached = true, $saveReport = false, $ripOptions = [], $analysisOptions = [])
    {
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".separations_analysis.json";

        if ($useCached) {
            //try and find existing report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached ANALYSIS report');
                $this->setReturnValue(0);
                return file_get_contents($defaultSavePath);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached ANALYSIS report');
                $this->setReturnValue(0);
                return file_get_contents($saveReport);
            }
        }

        $prepressCommands = GetCommands::getPrepressCommands();

        $ripOptionsDefault = [
            'format' => 'tiff',
            'colorspace' => 'tiffsep',
            'resolution' => '72',
            'smoothing' => false,
            'outputfolder' => null,
        ];

        $ripOptions = array_merge($ripOptionsDefault, $ripOptions);

        if (is_null($ripOptions['outputfolder'])) {
            $ripOptions['outputfolder'] = pathinfo($pdfPath, PATHINFO_DIRNAME) . '/analysis/';
        }

        //produce separation images
        $allImages = $prepressCommands->savePdfAsSeparations($pdfPath, $ripOptions);
        //make sure images are PNG (GS makes TIF images)
        foreach ($allImages as $k => $image) {
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            if (!in_array($ext, ['png', 'PNG'])) {
                $newFile = str_replace("." . $ext, ".png", $image);
                $Intervention = new ImageManager(['driver' => 'imagick']);
                $Intervention->make($image)->save($newFile);
                unlink($image);
                $allImages[$k] = $newFile;
            }
        }

        //compile what to analyse based on whitelist/blacklist
        $report = $prepressCommands->getPageSeparationsReport($pdfPath, true, false);
        $allSeparations = [];
        foreach ($report as $page) {
            $allSeparations = array_merge($allSeparations, array_values($page));
        }
        $allSeparations = array_values(array_unique($allSeparations));
        $allSeparations = array_map('strtolower', $allSeparations);

        if (isset($analysisOptions['whitelist'])) {
            $sepsToAnalyse = array_map('strtolower', $analysisOptions['whitelist']);
            $sepsToAnalyse = array_intersect($allSeparations, $sepsToAnalyse);
            $sepsToAnalyse = array_values($sepsToAnalyse);
        } elseif (isset($analysisOptions['blacklist'])) {
            $sepsToAnalyse = array_map('strtolower', $analysisOptions['blacklist']);
            $sepsToAnalyse = array_diff($allSeparations, $sepsToAnalyse);
            $sepsToAnalyse = array_values($sepsToAnalyse);
        } else {
            $sepsToAnalyse = $allSeparations;
        }

        //group the folder of images by their page number
        $imagesByPages = $prepressCommands->groupImagesByPage($allImages);
        $pdfSeparationImagesJsonOutput = $ripOptions['outputfolder'] . pathinfo($pdfPath, PATHINFO_FILENAME) . '.separation_images.json';
        file_put_contents($pdfSeparationImagesJsonOutput, json_encode($imagesByPages, JSON_PRETTY_PRINT));

        //make the report
        $histograms = [];
        $report = [];
        foreach ($imagesByPages as $page => $pageOfImages) {
            foreach ($pageOfImages as $image) {
                foreach ($sepsToAnalyse as $sepToAnalyse) {
                    $haystack = strtolower($image);
                    $needle = __("({0})", $sepToAnalyse);
                    if (strpos($haystack, $needle) === false) {
                        continue;
                    }

                    $ext = pathinfo($image, PATHINFO_EXTENSION);
                    $saveHistogramLocation = str_replace($ext, "histogram.json", $image);
                    $histograms[] = $saveHistogramLocation;
                    $histogram = $this->getHistogramJson($image, false, $saveHistogramLocation);
                    $histogram = json_decode($histogram, JSON_OBJECT_AS_ARRAY);

                    $re = '/\_[0-9]\((.*?)\)./m';
                    preg_match($re, $saveHistogramLocation, $matches, PREG_OFFSET_CAPTURE, 0);

                    if (isset($matches[0][0]) && isset($matches[1][0])) {
                        $pageAndColour = $matches[0][0];
                        $colour = $matches[1][0];

                        $report[$page]['thumbnail_resolution'] = $ripOptions['resolution'];
                        $report[$page]['thumbnail_paths'] = $pageOfImages;
                        $report[$page]['separations'][$colour]['histogram_unc'] = $saveHistogramLocation;
                        $report[$page]['separations'][$colour]['image_unc'] = $image;

                        if (isset($analysisOptions['base_unc']) && isset($analysisOptions['base_url'])) {
                            $report[$page]['separations'][$colour]['histogram_url'] = str_replace($analysisOptions['base_unc'], $analysisOptions['base_url'], $saveHistogramLocation);
                            $report[$page]['separations'][$colour]['image_url'] = str_replace($analysisOptions['base_unc'], $analysisOptions['base_url'], $image);
                        }

                        foreach ($histogram as $entry) {
                            $inkTint = ((255 - $entry['colour_value'][0]) / 255);

                            //FYI: IM resolution in histogram is PixelsPerCentimeter
                            $pixelWidthMm = 1 / ($entry['resolution'][0] / 10);
                            $pixelHeightMm = 1 / ($entry['resolution'][1] / 10);
                            $coverageSquareMm = ($pixelWidthMm * $pixelHeightMm) * $entry['pixels'];

                            $report[$page]['separations'][$colour]['calculations'][] = [
                                'ink_tint_percent' => ($inkTint * 100) . "%",
                                'ink_tint' => $inkTint,
                                'ink_coverage_square_mm' => $coverageSquareMm,
                            ];
                        }

                    }

                }
            }
        }


        if ($saveReport) {
            $reportJson = json_encode($report, JSON_PRETTY_PRINT);
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $reportJson);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                $this->mkdirWithCheck($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $reportJson);
                }
            }
        }

        $this->setReturnMessage('Analysis report generated');
        $this->setReturnValue(0);

        return $report;
    }

    /**
     * Analyse the SDI.
     *
     *
     *
     * @param $pdfPath
     * @param bool $useCached
     * @param false $saveReport
     * @param array $ripOptions
     * @param $search
     * @param $replace
     * @return false|mixed|string
     */
    public function analyseSpecialtyDryInks($pdfPath, $useCached = true, $saveReport = false, $ripOptions = [], $search = '', $replace = '')
    {
        $defaultSavePath = pathinfo($pdfPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".sdi_analysis.json";

        if ($useCached) {
            //try and find existing report files
            if (is_file($defaultSavePath)) {
                $this->setReturnMessage('Using cached ANALYSIS report');
                $this->setReturnValue(0);
                return file_get_contents($defaultSavePath);
            }

            if ($saveReport !== true && $saveReport !== false && is_file($saveReport)) {
                $this->setReturnMessage('Using cached ANALYSIS report');
                $this->setReturnValue(0);
                return file_get_contents($saveReport);
            }
        }


        $prepressCommands = GetCommands::getPrepressCommands();

        $ripOptionsDefault = [
            'format' => 'tiff',
            'colorspace' => 'tiffsep',
            'resolution' => '72',
            'smoothing' => false,
            'outputfolder' => null,
        ];

        $ripOptions = array_merge($ripOptionsDefault, $ripOptions);

        if (is_null($ripOptions['outputfolder'])) {
            $ripOptions['outputfolder'] = pathinfo($pdfPath, PATHINFO_DIRNAME) . '/analysis/';
        }

        $allImages = $prepressCommands->savePdfAsSeparations($pdfPath, $ripOptions);
        $imagesByPages = $prepressCommands->groupImagesByPage($allImages);
        $pdfSeparationImagesJsonOutput = $ripOptions['outputfolder'] . pathinfo($pdfPath, PATHINFO_FILENAME) . '.separation_images.json';
        file_put_contents($pdfSeparationImagesJsonOutput, json_encode($imagesByPages, JSON_PRETTY_PRINT));

        $histograms = [];
        $report = [];
        foreach ($imagesByPages as $pageOfImages) {
            foreach ($pageOfImages as $image) {
                //next loop if this colour is CMYK - we only need SDI
                $baseSeps = ['Cyan', 'Magenta', 'Yellow', 'Black'];
                foreach ($baseSeps as $baseSep) {
                    if (strpos($image, $baseSep) !== false) {
                        continue 2;
                    }
                }

                //next loop if this is not a spot colour separation
                if (strpos($image, "(") === false && strpos($image, ")") === false) {
                    continue;
                }

                $ext = pathinfo($image, PATHINFO_EXTENSION);
                $saveHistogramLocation = str_replace($ext, "histogram.json", $image);
                $histograms[] = $saveHistogramLocation;
                $histogram = $this->getHistogramJson($image, false, $saveHistogramLocation);
                $histogram = json_decode($histogram, JSON_OBJECT_AS_ARRAY);

                $re = '/\_[0-9]\((.*?)\)./m';
                preg_match($re, $saveHistogramLocation, $matches, PREG_OFFSET_CAPTURE, 0);

                if (isset($matches[0][0]) && isset($matches[1][0])) {
                    $pageAndColour = $matches[0][0];
                    $colour = $matches[1][0];

                    $page = str_replace($colour, '', $pageAndColour);
                    $page = preg_replace('/[^0-9]/', '', $page);

                    $report[$page]['thumbnail_resolution'] = $ripOptions['resolution'];
                    $report[$page]['thumbnail_paths'] = $pageOfImages;
                    //$report[$page]['separations'][$colour]['histogram_unc'] = $saveHistogramLocation;
                    $report[$page]['separations'][$colour]['histogram_url'] = $saveHistogramLocation;
                    //$report[$page]['separations'][$colour]['image_unc'] = $image;
                    $report[$page]['separations'][$colour]['image_url'] = $image;
                    $report[$page]['separations'][$colour]['sdi_units_total'] = 0;

                    foreach ($histogram as $entry) {
                        $inkTint = ((255 - $entry['colour_value'][0]) / 255);

                        $a4PixelWidth = (210 / 25.4) * $entry['resolution'][0];
                        $a4PixelHeight = (297 / 25.4) * $entry['resolution'][1];
                        $a4PixelCount = $a4PixelWidth * $a4PixelHeight;

                        $a4Coverage = ($entry['pixels'] / $a4PixelCount);

                        $sdiCoverage = ($inkTint * $a4Coverage);
                        $sdiUnits = ($inkTint * $a4Coverage) / 0.075;

                        $report[$page]['separations'][$colour]['sdi_units_total'] += $sdiUnits;

                        $report[$page]['separations'][$colour]['calculations'][] = [
                            'ink_tint_percent' => ($inkTint * 100) . "%",
                            'ink_tint' => $inkTint,
                            'a4_coverage_percentage' => $a4Coverage,
                            'sdi_coverage' => $sdiCoverage,
                            'sdi_units' => $sdiUnits,
                        ];
                    }

                }

            }
        }

        $report = $this->str_replace_multidimensional($search, $replace, $report);

        if ($saveReport) {
            $reportJson = json_encode($report, JSON_PRETTY_PRINT);
            if ($saveReport === true) {
                file_put_contents($defaultSavePath, $reportJson);
            } else {
                $savePath = pathinfo($saveReport, PATHINFO_DIRNAME);
                $this->mkdirWithCheck($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $reportJson);
                }
            }
        }

        $this->setReturnMessage('Analysis report generated');
        $this->setReturnValue(0);

        return $report;
    }

    private function str_replace_multidimensional($search, $replace, $multidimentionalArray, &$count = null)
    {
        array_walk_recursive($multidimentionalArray, function (&$element, $index) use ($search, $replace) {
            $element = str_replace($search, $replace, $element, $count);
        });

        return $multidimentionalArray;
    }

}
