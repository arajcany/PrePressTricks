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

        $imageResolution = null;
        $colourSpace = '';

        if (!$imageResolution) {
            try {
                $image = new Imagick($imageFilePath);
                $imageResolution = $image->getImageResolution();
                $colourSpace = $this->imageColourSpaceConstantToWord($image->getImageColorspace());
                dump('Imagick');
            } catch (\Throwable $exception) {
            }
        }

        if (!$imageResolution) {
            try {
                $image = imagecreatefromstring(file_get_contents($imageFilePath));
                $imageResolution = imageresolution($image);
                $imageResolution = [
                    'x' => $imageResolution[0],
                    'y' => $imageResolution[1],
                ];
                $colourSpace = '';
            } catch (\Throwable $exception) {
            }
        }

        if (!$imageResolution) {
            $imageResolution = ['x' => 0, 'y' => 0];
        }

        $imageManager = new ImageManager();
        $image = $imageManager->make($imageFilePath);
        $width = $image->getWidth();
        $height = $image->getHeight();
        $exif = $image->exif();

        if ($height >= $width) {
            $orientation = 'portrait';
        } else {
            $orientation = 'landscape';
        }

        $core = $image->getCore();

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


}