<?php

namespace arajcany\PrePressTricks\Utilities;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Imagick;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

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
            $reflection = new \ReflectionClass("\Imagick");
            $this->imagickColourSpaces = array_flip(
                array_filter(
                    $reflection->getConstants(),
                    function ($k) {
                        return mb_strpos($k, "COLORSPACE") !== false;
                    },
                    ARRAY_FILTER_USE_KEY
                )
            );
        } catch (\Throwable) {

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


        try {
            $imageManager = new ImageManager(['driver' => 'imagick']);
        } catch (\Throwable $exception) {
            $imageManager = new ImageManager(['driver' => 'gd']);
        }
        $image = $imageManager->make($imageFilePath);

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

        try {
            $exif = $image->exif();
            $exif = $this->cleanExifData($exif);
            if (!$exif) {
                $exif = $this->getExifViaExifTool($imageFilePath);
            }
        } catch (\Throwable $exception) {
            $exif = null;
        }

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

    private function getExifViaExifTool($pathToImage)
    {
        if (!$this->exifToolPath) {
            return null;
        }

        $command = "\"{$this->exifToolPath}\" \"{$pathToImage}\"";

        $output = [];
        $return_var = '';
        exec($command, $output, $return_var);
        if ($return_var !== 0) {
            return null;
        }

        $compiled = [];
        foreach ($output as $property) {
            $property = explode(':', $property);
            if (isset($property[0]) && isset($property[1])) {
                $key = str_replace(" ", "", trim($property[0]));
                $value = trim($property[1]);
                $compiled[$key] = $value;
            }
        }

        return $compiled;
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