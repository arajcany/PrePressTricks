<?php


namespace arajcany\PrePressTricks\Graphics\ImageMagick;


use Imagick;
use ImagickPixel;

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
     * @throws \ImagickException
     */
    public function isImExtension()
    {
        try {
            $image = new Imagick();
            $image->newImage(1, 1, new ImagickPixel('#ffffff'));
            $image->setImageFormat('png');
            $pngData = $image->getImagesBlob();
        } catch (\Throwable $exception) {
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
        } catch (\Throwable $throwable) {
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
                @mkdir($savePath);
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
                @mkdir($savePath);
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
        //$print_size = $identifyReport['Print size'];
        //$units = $identifyReport['Units'];

        $histogramReport = [];

        foreach ($identifyReport['Histogram'] as $colourValues => $pixelCount) {
            $colourValues = explode(" ", $colourValues);

            $histogramReport[] = [
                'colour_space' => $colour_space,
                'geometry' => $geometry,
                'resolution' => $resolution,
                //'print_size' => $print_size,
                //'units' => $units,
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
                @mkdir($savePath);
                if (is_dir($savePath)) {
                    file_put_contents($saveReport, $report);
                }
            }
        }

        $this->setReturnMessage('Histogram report generated');
        $this->setReturnValue($report);

        return $report;
    }
}