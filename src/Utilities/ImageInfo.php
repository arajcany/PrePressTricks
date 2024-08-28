<?php

namespace arajcany\PrePressTricks\Utilities;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Imagick;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use ReflectionClass;
use Throwable;

class ImageInfo
{

    private $imageCache = [];
    private $imagickColourSpaces = [];
    private $mimeDetector;
    private $exifToolPath = null;

    /**
     * ImageGeometry constructor
     */
    public function __construct()
    {
        try {
            $reflection = new ReflectionClass("\Imagick");
            $this->imagickColourSpaces = array_flip(
                array_filter(
                    $reflection->getConstants(),
                    function ($k) {
                        return mb_strpos($k, "COLORSPACE") !== false;
                    },
                    ARRAY_FILTER_USE_KEY
                )
            );
        } catch (Throwable) {

        }

        //populate the path
        $this->setExifToolPath();

        $this->mimeDetector = new FinfoMimeTypeDetector();
    }

    /**
     * Get information about an image and store it in the cache to save multiple loads from FSO
     *
     * @param $imageFilePath
     * @return array|false
     */
    public function getImageMeta($imageFilePath)
    {
        if (!is_file($imageFilePath)) {
            return false;
        }

        $imageDataChecksum = sha1_file($imageFilePath);
        $imagePathChecksum = sha1($imageFilePath);

        if (isset($this->imageCache[$imageDataChecksum])) {
            return $this->imageCache[$imageDataChecksum];
        }

        if (isset($this->imageCache[$imagePathChecksum])) {
            return $this->imageCache[$imagePathChecksum];
        }

        $imageMeta = [];


        //try the IMAGICK driver first
        try {
            $imageManager = new ImageManager(['driver' => 'imagick']);
            $image = $imageManager->make($imageFilePath);
        } catch (Throwable $exception) {
            $image = false;
        }

        //fallback to the GD driver
        if (!$image) {
            try {
                $imageManager = new ImageManager(['driver' => 'gd']);
                $image = $imageManager->make($imageFilePath);
            } catch (Throwable $exception) {
                $image = false;
            }
        }

        if (!$image) {
            return false;
        }

        $colourSpace = '';

        if ($image->getDriver()->getDriverName() === 'Imagick') {
            $core = $image->getCore();
            $imageResolution = $core->getImageResolution();
            $colourSpace = $this->imageColourSpaceConstantToWord($core->getImageColorspace());
        } else if ($image->getDriver()->getDriverName() === 'Gd') {
            $core = $image->getCore();
            $imageResolution = imageresolution($core);
            $imageResolution = [
                'x' => $imageResolution[0],
                'y' => $imageResolution[1],
            ];
            $colourSpace = '';
        } else {
            $imageResolution = ['x' => 0, 'y' => 0];
        }
        $width = $image->getWidth();
        $height = $image->getHeight();

        $exif = $this->getExif($imageFilePath);

        if ($height >= $width) {
            $orientation = 'portrait';
        } else {
            $orientation = 'landscape';
        }

        //build the meta array
        $imageMeta['width'] = $width;
        $imageMeta['height'] = $height;
        $imageMeta['orientation'] = $orientation;
        $imageMeta['resolution_x'] = $imageResolution['x'];
        $imageMeta['resolution_y'] = $imageResolution['y'];
        $imageMeta['exif'] = $exif;
        $imageMeta['sha1'] = $imageDataChecksum;
        $imageMeta['colour_space'] = $colourSpace;
        $imageMeta['mime_type'] = $this->mimeDetector->detectMimeTypeFromPath($imageFilePath);

        //cache the meta array
        $this->imageCache[$imagePathChecksum] = $imageMeta;
        $this->imageCache[$imageDataChecksum] = $imageMeta;

        return $imageMeta;
    }


    private function imageColourSpaceConstantToWord($constant)
    {
        if (isset($this->imagickColourSpaces[$constant])) {
            $cs = $this->imagickColourSpaces[$constant];
            return str_replace("COLORSPACE_", "", $cs);
        } else {
            return '';
        }
    }

    /**
     * Populate the ExifTool path
     */
    private function setExifToolPath()
    {
        $command = "where exiftool";
        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);
        if (isset($output[0])) {
            if (is_file($output[0])) {
                $this->exifToolPath = $output[0];
            }
        }
    }

    /**
     * @return null
     */
    public function getExifToolPath()
    {
        return $this->exifToolPath;
    }


    /**
     * @return null
     */
    public function getExifToolVersion()
    {
        if ($this->exifToolPath) {
            $command = "\"{$this->exifToolPath}\" -ver";

            $output = [];
            $return_var = '';
            exec($command, $output, $return_var);
            if (intval($return_var) !== 0) {
                return false;
            }

            if (isset($output[0]) && is_numeric($output[0])) {
                return $output[0];
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param string $path
     * @return bool|array
     */
    public function getExif($path): bool|array
    {
        if ($this->exifToolPath) {
            $command = "\"{$this->exifToolPath}\" \"{$path}\"";

            $output = [];
            $return_var = '';
            exec($command, $output, $return_var);
            if (intval($return_var) !== 0) {
                return false;
            }

            $compiled = [];
            foreach ($output as $property) {
                $property = explode(':', $property, 2);
                if (isset($property[0]) && isset($property[1])) {
                    $key = trim($property[0]);
                    $key = str_replace([" ", "/", "\\"], "_", $key);
                    $value = trim($property[1]);
                    $compiled[$key] = $value;
                }
            }
            return $compiled;
        }

        try {
            $exif = exif_read_data($path);
            return $this->cleanExifData($exif);
        } catch (Throwable $exception) {
        }

        try {
            $im = new Imagick($path);
            $exif = $im->getImageProperties();
            return $this->cleanExifData($exif);
        } catch (Throwable $exception) {
        }

        return false;
    }

    private function cleanExifData($dirtyExif)
    {
        $cleanExif = $dirtyExif;
        array_walk_recursive($cleanExif, function (&$element, $index) {
            $element = trim(mb_convert_encoding($element, 'UTF-8', 'UTF-8'));
        });

        return $cleanExif;
    }
}
