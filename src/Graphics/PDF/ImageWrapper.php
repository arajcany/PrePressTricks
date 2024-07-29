<?php

namespace arajcany\PrePressTricks\Graphics\PDF;

use arajcany\PrePressTricks\Utilities\Boxes;
use arajcany\PrePressTricks\Utilities\ImageInfo;
use arajcany\PrePressTricks\Utilities\PDFGeometry;
use arajcany\ToolBox\Utility\Feedback\ReturnAlerts;
use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Exception;
use Com\Tecnick\Pdf\Tcpdf;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Imagick\Driver;
use function PHPUnit\Framework\stringContains;

class ImageWrapper
{
    use ReturnAlerts;

    private PDFGeometry $PDFGeometry;
    private Boxes $Boxes;
    private ImageInfo $ImageInfo;

    private null|Tcpdf $PDF = null;

    private string $tmpDir;
    private array|null $adaptiveResolutions;
    private float $resolutionTolerance = 0.15; //allow 15% higher resolution before you resize images

    /**
     * ImageWrapper constructor.
     */
    public function __construct()
    {
        $this->PDFGeometry = new PDFGeometry();
        $this->Boxes = new Boxes();
        $this->ImageInfo = new ImageInfo();

        if (defined('TMP')) {
            $this->tmpDir = TMP;
        } else {
            $this->tmpDir = (__DIR__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR;
            $this->mkdirWithCheck($this->tmpDir);
        }
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
     * @param string $unit
     * @param bool $isunicode
     * @param bool $subsetfont
     * @param bool $compress
     * @param string $mode
     * @param ObjEncrypt|null $objEncrypt
     * @return void
     */
    private function initiatePdf(string $unit = 'mm', bool $isunicode = true, bool $subsetfont = false, bool $compress = true, string $mode = '', ?ObjEncrypt $objEncrypt = null): void
    {
        $this->PDF = new Tcpdf($unit, $isunicode, $subsetfont, $compress, $mode, $objEncrypt);
        $this->PDF->setCreator('');
        $this->PDF->setAuthor('');
        $this->PDF->setSubject('');
        $this->PDF->setTitle('');
        $this->PDF->setKeywords('');
        $this->PDF->setPDFFilename('');
    }

    /**
     * @param $imagePath
     * @param array $imageProperties
     * @param array $pageProperties
     * @return Tcpdf
     */
    public function wrapImage($imagePath, array $imageProperties = [], array $pageProperties = []): Tcpdf
    {
        /*
         * https://github.com/tecnickcom/tc-lib-pdf/blob/main/examples/index.php
         * https://github.com/tecnickcom/TCPDF/blob/5fce932fcee4371865314ab7f6c0d85423c5c7ce/examples/example_060.php#L79
         *
         */

        $defaultImageProperties = [
            'format' => 'jpg',      //jpg of tif
            'quality' => 100,       //0 = min quality, 100 = max quality when using lossy compression
            'anchor' => 5,          //int 1-9. Corresponds to an anchor point based on keyboard numberpad (e.g. 7 = top-left)
            'fitting' => 'fit',     //fit, fill, stretch
            'resolution' => null,   //int=desired resolution || null=any resolution || @=adaptive resolution
            'clipping' => true,     //clip portions of the image outside the bleed
        ];
        $imageProperties = array_merge($defaultImageProperties, $imageProperties);

        $defaultPageProperties = [
            'unit' => 'mm',
            'page_width' => 210,
            'page_height' => 297,
            'crop_length' => 5,
            'crop_offset' => 5,
            'bleed' => 5,
            'slug' => 30,
            'info' => true,
        ];
        $pageProperties = array_merge($defaultPageProperties, $pageProperties);

        if ($pageProperties['page_width'] === 'auto' && $pageProperties['page_height'] === 'auto') {
            $pageProperties['page_width'] = $defaultPageProperties['page_width'];
            $pageProperties['page_height'] = $defaultPageProperties['page_height'];
            $this->addInfoAlerts("Page width and height set to auto, defaulting to {$pageProperties['page_width']}x{$pageProperties['page_height']}{$pageProperties['unit']}.");
        }

        $unit = $this->sanitiseUnit($pageProperties['unit']);

        if (empty($this->PDF)) {
            $this->initiatePdf($unit);
            $this->addInfoAlerts("Creating default PDF as none supplied.");
        }

        if (empty($imagePath) || !is_file($imagePath)) {
            $this->addDangerAlerts("Invalid image path {$imagePath} supplied.");
            $this->PDF = $this->addErrorPage($this->PDF);

            return $this->PDF;
        }

        $imageResource = $this->makeImage($imagePath);
        if (empty($imageResource)) {
            $this->addDangerAlerts("Invalid image {$imagePath} supplied.");
            $this->PDF = $this->addErrorPage($this->PDF);

            return $this->PDF;
        }

        //convert to JPG if required
        $mimeTypes = ["image/jpeg", /*"image/pjpeg", "image/jp2"*/];
        if (!in_array($imageResource->mime(), $mimeTypes)) {
            $this->addInfoAlerts("Converting {$imageResource->mime()} to JPG.");
        }

        $imagePixelWidth = $imageResource->getWidth();
        $imagePixelHeight = $imageResource->getHeight();

        if ($pageProperties['page_width'] === 'auto') {
            $pageProperties['page_width'] = ((($pageProperties['page_height'] + (2 * $pageProperties['bleed'])) / $imagePixelHeight) * $imagePixelWidth) - (2 * $pageProperties['bleed']);
            $this->addInfoAlerts("Page width set to auto, calculated as {$pageProperties['page_width']}{$pageProperties['unit']} based on image ratio.");
            $this->addInfoAlerts("Page height set as {$pageProperties['page_height']}{$pageProperties['unit']}.");
        }

        if ($pageProperties['page_height'] === 'auto') {
            $pageProperties['page_height'] = ((($pageProperties['page_width'] + (2 * $pageProperties['bleed'])) / $imagePixelWidth) * $imagePixelHeight) - (2 * $pageProperties['bleed']);
            $this->addInfoAlerts("Page width set as {$pageProperties['page_width']}{$pageProperties['unit']}.");
            $this->addInfoAlerts("Page height set to auto, calculated as {$pageProperties['page_height']}{$pageProperties['unit']} based on image ratio.");
        }


        /*
         * Safety check as PDF do not allow dimension >14,400 PDF units in each direction (200 inches or 5080mm)
         */
        $checkX = $pageProperties['page_width'] + (2 * max($pageProperties['slug'], $pageProperties['bleed'], ($pageProperties['crop_length'] + $pageProperties['crop_offset'])));
        $checkY = $pageProperties['page_height'] + (2 * max($pageProperties['slug'], $pageProperties['bleed'], ($pageProperties['crop_length'] + $pageProperties['crop_offset'])));
        $checkX = $this->PDFGeometry->convertUnit($checkX, $unit, 'pt');
        $checkY = $this->PDFGeometry->convertUnit($checkY, $unit, 'pt');
        if ($checkX > 14400 || $checkY > 14400) {
            $this->addDangerAlerts("Maximum PDF dimensions (including bleed, slug and trims) of 14,400pts exceeded.");
            $this->PDF = $this->addErrorPage($this->PDF);

            return $this->PDF;
        }


        /*
         * Calculate out everything so that we have easy references
         */

        $boxMedia_width = $this->PDFGeometry->convertUnit($pageProperties['page_width'] + (2 * $pageProperties['slug']), $unit, 'pt');
        $boxMedia_height = $this->PDFGeometry->convertUnit($pageProperties['page_height'] + (2 * $pageProperties['slug']), $unit, 'pt');
        $boxMedia_llx = $this->PDFGeometry->convertUnit(0, $unit, 'pt');
        $boxMedia_lly = $this->PDFGeometry->convertUnit(0, $unit, 'pt');
        $boxMedia_urx = $this->PDFGeometry->convertUnit($pageProperties['page_width'] + (2 * $pageProperties['slug']), $unit, 'pt');
        $boxMedia_ury = $this->PDFGeometry->convertUnit($pageProperties['page_height'] + (2 * $pageProperties['slug']), $unit, 'pt');


        $boxTrim_width = $this->PDFGeometry->convertUnit($pageProperties['page_width'], $unit, 'pt');
        $boxTrim_height = $this->PDFGeometry->convertUnit($pageProperties['page_height'], $unit, 'pt');
        $boxTrim_llx = $this->PDFGeometry->convertUnit($pageProperties['slug'], $unit, 'pt');
        $boxTrim_lly = $this->PDFGeometry->convertUnit($pageProperties['slug'], $unit, 'pt');
        $boxTrim_urx = $this->PDFGeometry->convertUnit($pageProperties['page_width'] + $pageProperties['slug'], $unit, 'pt');
        $boxTrim_ury = $this->PDFGeometry->convertUnit($pageProperties['page_height'] + $pageProperties['slug'], $unit, 'pt');


        $boxBleed_width = $this->PDFGeometry->convertUnit($pageProperties['page_width'] + (2 * $pageProperties['bleed']), $unit, 'pt');
        $boxBleed_height = $this->PDFGeometry->convertUnit($pageProperties['page_height'] + (2 * $pageProperties['bleed']), $unit, 'pt');
        $boxBleed_llx = $this->PDFGeometry->convertUnit($pageProperties['slug'] - $pageProperties['bleed'], $unit, 'pt');
        $boxBleed_lly = $this->PDFGeometry->convertUnit($pageProperties['slug'] - $pageProperties['bleed'], $unit, 'pt');
        $boxBleed_urx = $this->PDFGeometry->convertUnit($pageProperties['page_width'] + $pageProperties['slug'] + $pageProperties['bleed'], $unit, 'pt');
        $boxBleed_ury = $this->PDFGeometry->convertUnit($pageProperties['page_height'] + $pageProperties['slug'] + $pageProperties['bleed'], $unit, 'pt');


        if ($imageProperties['fitting'] === 'fill') {
            $imageDimensions = $this->Boxes->fillIntoBox($imagePixelWidth, $imagePixelHeight, $boxBleed_width, $boxBleed_height);
            $fittingType = 'fill';
            $this->addInfoAlerts("Using 'fill' as the image fitting.");
        } elseif ($imageProperties['fitting'] === 'fit') {
            $imageDimensions = $this->Boxes->fitIntoBox($imagePixelWidth, $imagePixelHeight, $boxBleed_width, $boxBleed_height);
            $fittingType = 'fit';
            $this->addInfoAlerts("Using 'fit' as the image fitting.");
        } else {
            $imageDimensions = $this->Boxes->stretchIntoBox($imagePixelWidth, $imagePixelHeight, $boxBleed_width, $boxBleed_height);
            $fittingType = 'stretch';
            $this->addInfoAlerts("Using 'stretch' as the image fitting.");
        }

        $imageDimensionShortest = min($imageDimensions);
        $imageDimensionLongest = min($imageDimensions);

        //place the image in the center of the page
        $imagePlacement_width = $imageDimensions['width'];
        $imagePlacement_height = $imageDimensions['height'];
        $imagePlacement_llx = ($boxMedia_width - $imagePlacement_width) / 2;
        $imagePlacement_lly = ($boxMedia_height - $imagePlacement_height) / 2;
        $imagePlacement_urx = $imagePlacement_llx + $imagePlacement_width;
        $imagePlacement_ury = $imagePlacement_lly + $imagePlacement_height;

        //TODO shift the image if anchor point does not equal 5
        if ($imageProperties['anchor'] !== 5) {
            $this->addWarningAlerts("Image shift not yet implemented.");
        }

        $imageResizeQuality = intval($imageProperties['quality']);

        $imagePlacement = [
            $imagePlacement_llx,
            $imagePlacement_lly,
            $imagePlacement_urx,
            $imagePlacement_ury,
        ];
        $imagePlacement = $this->convertGeometryToPlacement($imagePlacement, $unit, $boxMedia_height);

        $imageEffectiveResolutionX = $this->getEffectiveResolution($imagePixelWidth, $imagePlacement_width, 'pt');
        $imageEffectiveResolutionY = $this->getEffectiveResolution($imagePixelHeight, $imagePlacement_height, 'pt');
        $this->addInfoAlerts("Effective image resolution calculated as {$imageEffectiveResolutionX} x {$imageEffectiveResolutionY} dpi.");

        $targetResolutionMin = $imageProperties['resolution'];
        if (str_starts_with($targetResolutionMin, '@')) {
            $targetResolutionMin = $this->getTargetResolution($imageDimensionShortest, 'pt');
        }
        $targetResolutionMax = intval(ceil($targetResolutionMin * (1 + $this->resolutionTolerance)));

        if (is_numeric($targetResolutionMin)) {
            $this->addInfoAlerts("Minimum target resolution set to {$targetResolutionMin} dpi.");
            $this->addInfoAlerts("Maximum target resolution set to {$targetResolutionMax} dpi.");
        }

        if (in_array($targetResolutionMin, [null, 0, ''])) {
            $imageResizeWidth = null;
            $imageResizeHeight = null;
            $this->addInfoAlerts("Target resolution not specified, all image data will be used.");
        } elseif (is_numeric($targetResolutionMin)) {
            if ($imageEffectiveResolutionX > $targetResolutionMax && $imageEffectiveResolutionY > $targetResolutionMax) {
                $factorX = $targetResolutionMin / $imageEffectiveResolutionX;
                $factorY = $targetResolutionMin / $imageEffectiveResolutionY;
                $imageResizeWidth = intval(ceil($factorX * $imagePixelWidth));
                $imageResizeHeight = intval(ceil($factorY * $imagePixelHeight));
                $this->addInfoAlerts("Effective resolution is higher than the maximum target resolution, image will be scaled.");
                //calculate the new effective resolution
                $imageEffectiveResolutionX = $this->getEffectiveResolution($imageResizeWidth, $imagePlacement_width, 'pt');
                $imageEffectiveResolutionY = $this->getEffectiveResolution($imageResizeHeight, $imagePlacement_height, 'pt');
            } else {
                $imageResizeWidth = null;
                $imageResizeHeight = null;
                $this->addInfoAlerts("Effective resolution is lower than the minimum target resolution, all image data will be used.");
            }
        } else {
            $imageResizeWidth = null;
            $imageResizeHeight = null;
            $this->addInfoAlerts("Target resolution not specified. Will use all image data.");
        }

        //now that the final resolution and size has been determined, save out a new image if necessary
        if ($imageResizeWidth && $imageResizeHeight) {
            $imageResource
                ->resize($imageResizeWidth, $imageResizeHeight);
        }


        /*
         * Create all the arrays for TC-LIP-PDF
         */
        $boxMedia = [
            'llx' => $boxMedia_llx,
            'lly' => $boxMedia_lly,
            'urx' => $boxMedia_urx,
            'ury' => $boxMedia_ury,
            'bci' => [
                'color' => '#000000',
                'width' => 0.353,
                'style' => 'S',
                'dash' => [
                    0 => 3,
                ],
            ],
        ];

        $boxTrim = [
            'llx' => $boxTrim_llx,
            'lly' => $boxTrim_lly,
            'urx' => $boxTrim_urx,
            'ury' => $boxTrim_ury,
            'bci' => [
                'color' => '#000000',
                'width' => 0.353,
                'style' => 'S',
                'dash' => [
                    0 => 3,
                ],
            ],
        ];

        $boxBleed = [
            'llx' => $boxBleed_llx,
            'lly' => $boxBleed_lly,
            'urx' => $boxBleed_urx,
            'ury' => $boxBleed_ury,
            'bci' => [
                'color' => '#000000',
                'width' => 0.353,
                'style' => 'S',
                'dash' => [
                    0 => 3,
                ],
            ],
        ];

        $boxArt = [
            'llx' => $imagePlacement_llx,
            'lly' => $imagePlacement_lly,
            'urx' => $imagePlacement_urx,
            'ury' => $imagePlacement_ury,
            'bci' => [
                'color' => '#000000',
                'width' => 0.353,
                'style' => 'S',
                'dash' => [
                    0 => 3,
                ],
            ],
        ];

        $clipping = $this->calculateClipping(array_values(array_slice($boxBleed, 0, 4)), array_values(array_slice($boxArt, 0, 4)));
        $clippingPercent = round($clipping * 100, 2);
        if ($clippingPercent > 0) {
            $this->addInfoAlerts("{$clippingPercent}% of the image has been clipped.");
        } else {
            $this->addInfoAlerts("No image clipping.");
        }

        $orientation = ($boxMedia_width <= $boxMedia_height) ? "P" : "L";
        $this->addInfoAlerts("Page orientation set as '{$orientation}'");

        $propsRaw = [
            'group' => 0,
            'rotation' => 0,
            'zoom' => 1,
            'orientation' => $orientation,
            'format' => null,
            'pwidth' => $boxMedia_width,
            'pheight' => $boxMedia_height,
            'width' => $this->PDFGeometry->convertUnit($boxMedia_width, 'pt', $unit),
            'height' => $this->PDFGeometry->convertUnit($boxMedia_height, 'pt', $unit),
            'box' => [
                'MediaBox' => $boxMedia,
                'CropBox' => $boxMedia,
                'BleedBox' => $boxBleed,
                'TrimBox' => $boxTrim,
                'ArtBox' => $boxArt,
            ],
        ];

        $pageInfo = [];
        $pageInfo['filename'] = pathinfo($imagePath, PATHINFO_BASENAME);
        $pageInfo['unit'] = $unit;
        $pageInfo['media_box'] = ['unit' => $unit, 'width' => $this->PDFGeometry->convertUnit($boxMedia_width, 'pt', $unit), 'height' => $this->PDFGeometry->convertUnit($boxMedia_width, 'pt', $unit)];
        $pageInfo['trim_box'] = ['unit' => $unit, 'width' => $this->PDFGeometry->convertUnit($boxTrim_width, 'pt', $unit), 'height' => $this->PDFGeometry->convertUnit($boxTrim_height, 'pt', $unit)];
        $pageInfo['fitting'] = ['type' => $fittingType];
        $pageInfo['dpi_target'] = ['min' => $targetResolutionMin, 'max' => $targetResolutionMax];
        $pageInfo['dpi_effective'] = ['x' => $imageEffectiveResolutionX, 'y' => $imageEffectiveResolutionY];
        $pageInfo['clipping'] = ['float' => $clipping, 'percent' => $clippingPercent . "%"];
        $pageInfo['bleed'] = $pageProperties['bleed'];
        $pageInfo['crop_offset'] = $pageProperties['crop_offset'];

        try {
            $pdfPageProperties = $this->PDF->page->add($propsRaw);

            //convert $imageResource to a string
            $imageResourceString = "@" . $imageResource->encode('jpg', 100);

            $content = '';

            if ($imageProperties['clipping']) {
                //---------------------------------------------------------------------------------------------------------
                //there is a bug in tc-lib-pdf - when clipping insert crops/info before inserting the image
                if ($pageProperties['info']) {
                    $this->addPageInfoText($pageInfo);
                }
                $this->addCropMarks([$boxTrim_llx, $boxTrim_lly, $boxTrim_urx, $boxTrim_ury], $pageProperties['crop_length'], $pageProperties['crop_offset'], $unit);
                //---------------------------------------------------------------------------------------------------------

                $clippingBoxCoordinates = [
                    $boxBleed_llx,
                    $boxBleed_lly,
                    $boxBleed_urx,
                    $boxBleed_ury,
                ];

                //$this->addInfoAlerts("PageProperties");
                //$this->addInfoAlerts(json_encode($pageProperties));

                $clippingBoxCoordinates = $this->convertGeometryToPlacement($clippingBoxCoordinates, $unit);
                //$this->addInfoAlerts("ClippingBox");
                //$this->addInfoAlerts(json_encode($clippingBoxCoordinates));

                //$this->addInfoAlerts("Image");
                //$this->addInfoAlerts(json_encode($imagePlacement));

                $content .= $this->PDF->graph->getStartTransform();
                $content .= $this->PDF->graph->getRect($clippingBoxCoordinates['xpos'], $clippingBoxCoordinates['ypos'], $clippingBoxCoordinates['width'], $clippingBoxCoordinates['height'], 'CNZ');
                $imageId = $this->PDF->image->add($imageResourceString, $imageResizeWidth, $imageResizeHeight, false, $imageResizeQuality);
                $content .= $this->PDF->image->getSetImage($imageId, $imagePlacement['xpos'], $imagePlacement['ypos'], $imagePlacement['width'], $imagePlacement['height'], $pdfPageProperties['height']);
                $content .= $this->PDF->graph->getStopTransform();
                $this->PDF->page->addContent($content);
            } else {
                $imageId = $this->PDF->image->add($imageResourceString, $imageResizeWidth, $imageResizeHeight, false, $imageResizeQuality);
                $content .= $this->PDF->image->getSetImage($imageId, $imagePlacement['xpos'], $imagePlacement['ypos'], $imagePlacement['width'], $imagePlacement['height'], $pdfPageProperties['height']);
                $this->PDF->page->addContent($content);

                //---------------------------------------------------------------------------------------------------------
                //there is a bug in tc-lib-pdf - when not clipping insert crops/info after inserting the image
                if ($pageProperties['info']) {
                    $this->addPageInfoText($pageInfo);
                }
                $this->addCropMarks([$boxTrim_llx, $boxTrim_lly, $boxTrim_urx, $boxTrim_ury], $pageProperties['crop_length'], $pageProperties['crop_offset'], $unit);
                //---------------------------------------------------------------------------------------------------------
            }

        } catch (\Throwable $exception) {
            $this->addDangerAlerts($exception->getMessage());
        }

        return $this->PDF;
    }

    /**
     * Add crops marks to the defined geometric region on the current page
     *
     * @param array $geometry [xll,yll,xur,yur] the numbers (supplied as points)
     * @param float $cropLength how long to draw the crop mark
     * @param float $cropOffset haw far to offset the crop mark from the given geometry
     * @param string $cropUnit the unit of the given $length and $offset
     */
    private function addCropMarks(array $geometry, float $cropLength = 5, float $cropOffset = 5, string $cropUnit = 'mm', array $cropStyle = []): void
    {
        if (empty($this->PDF)) {
            return;
        }

        //graphing operations need to have the page height set
        try {
            $pdfPageProperties = $this->PDF->page->getPage();
            $this->PDF->graph->setPageWidth($pdfPageProperties['width']);
            $this->PDF->graph->setPageHeight($pdfPageProperties['height']);
        } catch (\Throwable) {
            return;
        }

        $defaultStyle = [
            'lineWidth' => 0.5,
            'lineCap' => 'butt',
            'lineJoin' => 'miter',
            'lineColor' => 'all',
            'fillColor' => null,
        ];
        $cropStyle = array_merge($defaultStyle, $cropStyle);

        $effectiveGeometry = $this->PDFGeometry->getEffectiveGeometry($geometry);

        $cropLengthPts = $this->PDFGeometry->convertUnit($cropLength, $cropUnit, 'pt');
        $cropOffsetPts = $this->PDFGeometry->convertUnit($cropOffset, $cropUnit, 'pt');


        $lines = [
            ['x' => $effectiveGeometry['anchors'][7][0], 'y' => $effectiveGeometry['anchors'][7][1], 'direction' => 'T'],
            ['x' => $effectiveGeometry['anchors'][7][0], 'y' => $effectiveGeometry['anchors'][7][1], 'direction' => 'L'],
            ['x' => $effectiveGeometry['anchors'][9][0], 'y' => $effectiveGeometry['anchors'][9][1], 'direction' => 'T'],
            ['x' => $effectiveGeometry['anchors'][9][0], 'y' => $effectiveGeometry['anchors'][9][1], 'direction' => 'R'],
            ['x' => $effectiveGeometry['anchors'][1][0], 'y' => $effectiveGeometry['anchors'][1][1], 'direction' => 'B'],
            ['x' => $effectiveGeometry['anchors'][1][0], 'y' => $effectiveGeometry['anchors'][1][1], 'direction' => 'L'],
            ['x' => $effectiveGeometry['anchors'][3][0], 'y' => $effectiveGeometry['anchors'][3][1], 'direction' => 'B'],
            ['x' => $effectiveGeometry['anchors'][3][0], 'y' => $effectiveGeometry['anchors'][3][1], 'direction' => 'R'],
        ];

        foreach ($lines as $line) {
            if ($line['direction'] === 'T') {
                $posx1 = $line['x'];
                $posy1 = $line['y'] + $cropOffsetPts;
                $posx2 = $line['x'];
                $posy2 = $line['y'] + $cropOffsetPts + $cropLengthPts;
            } elseif ($line['direction'] === 'R') {
                $posx1 = $line['x'] + $cropOffsetPts;
                $posy1 = $line['y'];
                $posx2 = $line['x'] + $cropOffsetPts + $cropLengthPts;
                $posy2 = $line['y'];
            } elseif ($line['direction'] === 'B') {
                $posx1 = $line['x'];
                $posy1 = $line['y'] - $cropOffsetPts;
                $posx2 = $line['x'];
                $posy2 = $line['y'] - $cropOffsetPts - $cropLengthPts;
            } elseif ($line['direction'] === 'L') {
                $posx1 = $line['x'] - $cropOffsetPts;
                $posy1 = $line['y'];
                $posx2 = $line['x'] - $cropOffsetPts - $cropLengthPts;
                $posy2 = $line['y'];
            } else {
                continue;
            }

            $xyStart = $this->convertGeometryPointToCoordinates([$posx1, $posy1], $cropUnit, $pdfPageProperties['pheight']);
            $xyStop = $this->convertGeometryPointToCoordinates([$posx2, $posy2], $cropUnit, $pdfPageProperties['pheight']);
            $mark = $this->PDF->graph->getLine($xyStart['xpos'], $xyStart['ypos'], $xyStop['xpos'], $xyStop['ypos'], $cropStyle);
            $this->PDF->page->addContent($mark);
        }

        $regos = [
            ['x' => $effectiveGeometry['anchors'][7][0], 'y' => $effectiveGeometry['anchors'][7][1], 'direction' => 'TL'],
            ['x' => $effectiveGeometry['anchors'][9][0], 'y' => $effectiveGeometry['anchors'][9][1], 'direction' => 'TR'],
            ['x' => $effectiveGeometry['anchors'][3][0], 'y' => $effectiveGeometry['anchors'][3][1], 'direction' => 'BR'],
            ['x' => $effectiveGeometry['anchors'][1][0], 'y' => $effectiveGeometry['anchors'][1][1], 'direction' => 'BL'],
        ];

        foreach ($regos as $rego) {
            if ($rego['direction'] === 'TL') {
                $posx1 = $rego['x'] - ($cropOffsetPts + $cropLengthPts / 2);
                $posy1 = $rego['y'] + ($cropOffsetPts + $cropLengthPts / 2);
            } elseif ($rego['direction'] === 'TR') {
                $posx1 = $rego['x'] + ($cropOffsetPts + $cropLengthPts / 2);
                $posy1 = $rego['y'] + ($cropOffsetPts + $cropLengthPts / 2);
            } elseif ($rego['direction'] === 'BR') {
                $posx1 = $rego['x'] + ($cropOffsetPts + $cropLengthPts / 2);
                $posy1 = $rego['y'] - ($cropOffsetPts + $cropLengthPts / 2);
            } elseif ($rego['direction'] === 'BL') {
                $posx1 = $rego['x'] - ($cropOffsetPts + $cropLengthPts / 2);
                $posy1 = $rego['y'] - ($cropOffsetPts + $cropLengthPts / 2);
            } else {
                continue;
            }

            try {
                $radius = 5;
                $pos = $this->convertGeometryPointToCoordinates([$posx1, $posy1], $cropUnit, $pdfPageProperties['pheight']);
                $registrationMark = $this->PDF->graph->getRegistrationMark($pos['xpos'], $pos['ypos'], $radius);
                $this->PDF->page->addContent($registrationMark);
            } catch (\Throwable $exception) {

            }
        }
    }

    /**
     * Add a slug text to the PDF
     *
     * @param array $pageInfo
     * @return void
     * @throws \Com\Tecnick\Pdf\Image\Exception
     */
    public function addPageInfoText(array $pageInfo): void
    {
        if (empty($this->PDF)) {
            return;
        }

        //graphing operations need to have the page height set
        try {
            $pdfPageProperties = $this->PDF->page->getPage();
            $this->PDF->graph->setPageWidth($pdfPageProperties['width']);
            $this->PDF->graph->setPageHeight($pdfPageProperties['height']);
        } catch (\Throwable) {
            return;
        }

        //sting together the info text
        $tmpW = intval(ceil($pageInfo['trim_box']['width']));
        $tmpH = intval(ceil($pageInfo['trim_box']['height']));
        $tmpFitting = strtoupper($pageInfo['fitting']['type']);
        $tmpClipping = strtoupper($pageInfo['clipping']['percent']);
        $infoText = "{$pageInfo['filename']} |";
        $infoText .= " Trim: {$tmpW} x {$tmpH} {$pageInfo['trim_box']['unit']} |";
        $infoText .= " Fitting: {$tmpFitting} |";
        $infoText .= " Clipping: {$tmpClipping} |";
        $infoText .= " Target Res: {$pageInfo['dpi_target']['min']} to {$pageInfo['dpi_target']['max']} dpi |";
        $infoText .= " Effective Res: {$pageInfo['dpi_effective']['x']} x {$pageInfo['dpi_effective']['y']} dpi";

        //calculate the width and height of the text
        $fontPath = __DIR__ . DIRECTORY_SEPARATOR . 'RobotoMono-Regular.ttf';
        $fontSize = 20;
        $textBox = imagettfbbox($fontSize, 0, $fontPath, $infoText);
        $textWidth = $textBox[2] - $textBox[0];
        $textHeight = $textBox[1] - $textBox[7];

        //generate an image based on the width and height of the text
        $imageWidth = $textWidth + 50;
        $imageHeight = $textHeight + 4;
        $image = imagecreatetruecolor($imageWidth, $imageHeight);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        $fontColor = imagecolorallocate($image, 0, 0, 0); // Black
        $textX = (($imageWidth - $textWidth) / 2);
        $textY = (($imageHeight - $textHeight) / 2) + $textHeight - 6;
        imagettftext($image, $fontSize, 0, $textX, $textY, $fontColor, $fontPath, $infoText);

        //save the image
        $imagePath = $this->tmpDir . mt_rand(1111, 9999) . '-info-mark.jpg';
        imagepng($image, $imagePath);

        $textFinalWidth = min(800, $pdfPageProperties['box']['TrimBox']['urx'] - $pdfPageProperties['box']['TrimBox']['llx']);
        $textFinalHeight = $imageHeight / $imageWidth * $textFinalWidth;
        $textOffsetY = $this->PDFGeometry->convertUnit($pageInfo['bleed'], $pageInfo['unit'], 'pt') + $this->PDFGeometry->convertUnit($pageInfo['crop_offset'], $pageInfo['unit'], 'pt');

        $imagePlacement = [
            $pdfPageProperties['box']['TrimBox']['llx'],
            $pdfPageProperties['box']['TrimBox']['lly'] - $textOffsetY,
            $pdfPageProperties['box']['TrimBox']['llx'] + $textFinalWidth,
            $pdfPageProperties['box']['TrimBox']['lly'] + $textFinalHeight - $textOffsetY,
        ];

        $imagePlacement = $this->convertGeometryToPlacement([$imagePlacement[0], $imagePlacement[1], $imagePlacement[2], $imagePlacement[3]], $pageInfo['unit'], $pdfPageProperties['pheight']);
        $imageId = $this->PDF->image->add($imagePath, null, null, false, 100);
        $content = $this->PDF->image->getSetImage($imageId, $imagePlacement['xpos'], $imagePlacement['ypos'], $imagePlacement['width'], $imagePlacement['height'], $pdfPageProperties['height']);
        $this->PDF->page->addContent($content);

        unlink($imagePath);
    }

    /**
     * @param Tcpdf $pdf
     * @return Tcpdf
     */
    private function addErrorPage(Tcpdf $pdf): Tcpdf
    {
        $pdf->page->add();

        return $pdf;
    }

    /**
     * @param Tcpdf $pdf
     * @param $savePath
     * @return bool
     */
    public function savePdf(Tcpdf $pdf, $savePath): bool
    {
        $dirPart = pathinfo($savePath, PATHINFO_DIRNAME);
        $this->mkdirWithCheck($dirPart);

        try {
            $pdfRawString = $pdf->getOutPDFString();
            $result = file_put_contents($savePath, $pdfRawString);
            if ($result) {
                $this->addSuccessAlerts("Wrote {$result} bytes to the file system.");
            } else {
                $this->addDangerAlerts("Unable to write to the file system.");
                return false;
            }
        } catch (\Throwable $exception) {
            $this->addDangerAlerts($exception->getMessage());
            return false;
        }

        return is_file($savePath);
    }

    /**
     * @param $imagePath
     * @return false|Image
     */
    private function makeImage($imagePath): bool|Image
    {
        $manager = new ImageManager(['driver' => 'imagick']);

        try {
            $image = $manager->make($imagePath);
            return $image;
        } catch (\Throwable $exception) {
            $this->addDangerAlerts($exception->getMessage());
        }

        $manager = new ImageManager(['driver' => 'gd']);

        try {
            $image = $manager->make($imagePath);
            return $image;
        } catch (\Throwable $exception) {
            $this->addDangerAlerts($exception->getMessage());
        }

        return false;
    }

    /**
     * Convert PDF geometry to Abscissa and Ordinate (x,y) coordinates.
     * PDF uses the bottom-left as the origin and points as the units e.g. [xll,yll,xur,yur].
     * TC-LIB-PDF drawing functions use the top-left as the origin and whatever the user defined as the unit.
     *
     * @param array $geometry [xll,yll,xur,yur] the numbers (supplied as points)
     * @param string $userUnit e.g. mm
     * @param float|string|null $canvasHeight TC-LIB-PDF needs to know the size of the canvas to re-coordinate. (supplied as points)
     * @return array
     */
    private function convertGeometryToPlacement(array $geometry, string $userUnit, float|string|null $canvasHeight = null): array
    {
        if ($canvasHeight === null) {
            try {
                $pdfPageProperties = $this->PDF->page->getPage();
                $canvasHeight = $pdfPageProperties['pheight'];
            } catch (\Throwable $exception) {
                $canvasHeight = 0;
            }
        }

        $llx = $geometry[0];
        $lly = $geometry[1];
        $urx = $geometry[2];
        $ury = $geometry[3];

        $width = $urx - $llx;
        $height = $ury - $lly;
        $xpos = $llx;
        $ypos = $canvasHeight - $ury;

        return [
            'xpos' => $this->PDFGeometry->convertUnit($xpos, 'pt', $userUnit),
            'ypos' => $this->PDFGeometry->convertUnit($ypos, 'pt', $userUnit),
            'width' => $this->PDFGeometry->convertUnit($width, 'pt', $userUnit),
            'height' => $this->PDFGeometry->convertUnit($height, 'pt', $userUnit),
        ];
    }

    /**
     * Convert PDF geometry to Abscissa and Ordinate (x,y) coordinates.
     * Bottom left [x-pts, y-pts] to upper left (x,y) coordinates.
     *
     * @param array $geometryPoint [x,y] the numbers (supplied as points)
     * @param string $userUnit e.g. mm
     * @param float|string|null $canvasHeight TC-LIB-PDF needs to know the size of the canvas to re-coordinate. (supplied as points)
     * @return array
     */
    private function convertGeometryPointToCoordinates(array $geometryPoint, string $userUnit, float|string|null $canvasHeight = null): array
    {
        if ($canvasHeight === null) {
            try {
                $pdfPageProperties = $this->PDF->page->getPage();
                $canvasHeight = $pdfPageProperties['pheight'];
            } catch (\Throwable $exception) {
                $canvasHeight = 0;
            }
        }

        $x = $geometryPoint[0];
        $y = $geometryPoint[1];

        $xpos = $x;
        $ypos = $canvasHeight - $y;

        return [
            'xpos' => $this->PDFGeometry->convertUnit($xpos, 'pt', $userUnit),
            'ypos' => $this->PDFGeometry->convertUnit($ypos, 'pt', $userUnit),
        ];
    }


    /**
     * @param array $geometryFrame [xll,yll,xur,yur] the numbers (supplied as points)
     * @param array $geometryContent [xll,yll,xur,yur] the numbers (supplied as points)
     * @return float percentage of the content that has been clipped 0=no-clipping 1=all-clipped
     */
    private function calculateClipping(array $geometryFrame, array $geometryContent, $round = 10): float
    {
        $areaFrame = $this->PDFGeometry->getArea($geometryFrame);
        $areaContent = $this->PDFGeometry->getArea($geometryContent);

        //geometry of where the frame and content overlap
        $geometryOverlapping = $this->PDFGeometry->getOverlappingGeometry($geometryFrame, $geometryContent);
        if (!$geometryOverlapping) {
            return 1;
        }
        $areaOverlapping = $this->PDFGeometry->getArea($geometryOverlapping);

        $clipping = ($areaContent - $areaOverlapping) / $areaContent;

        return round($clipping, $round);
    }

    /**
     * Make sure the $unit is in the format expected by TC-LIP-PDF
     *
     * @param string $unit
     * @param string $fallback
     * @return string
     */
    private function sanitiseUnit(string $unit, string $fallback = 'mm'): string
    {
        if (in_array(strtolower($unit), ['in', 'mm', 'pt', 'cm'])) {
            return strtolower($unit);
        }

        $sanitised = $this->PDFGeometry->getMasterKeyFromUnknown($this->PDFGeometry->getUnitsMap(), $unit, $fallback);

        if ($sanitised === $fallback) {
            return $fallback;
        } elseif ($sanitised === 'INCHES') {
            return 'in';
        } elseif ($sanitised === 'MILLIMETERS') {
            return 'mm';
        } elseif ($sanitised === 'POINTS') {
            return 'pt';
        } elseif ($sanitised === 'CENTIMETERS') {
            return 'cm';
        } else {
            return $fallback;
        }
    }

    /**
     * For the given dimension, get the target resolution.
     * The resolution is calculated from the Adaptive Resolution map
     * and will be the target dpi for the best printing resolution
     * at the given dimension.
     *
     * @param float|int $dimension the integer value of the dimension e.g. 300
     * @param string $dimensionUnit the unit type of the given dimension e.g. mm
     * @param int $fallbackResolution
     * @return integer The target resolution in dpi (e.g. 280dpi - without the dpi)
     */
    public function getTargetResolution(float|int $dimension, string $dimensionUnit, int $fallbackResolution = 300): int
    {
        if (empty($this->adaptiveResolutions)) {
            $this->adaptiveResolutions = $this->getDefaultAdaptiveResolutionsMap();
        }

        //shorthand some values
        $mapUnit = $this->adaptiveResolutions['unit'];
        $map = $this->adaptiveResolutions['map'];

        //convert the $dimension to the unit used by the map
        $dimension = $this->PDFGeometry->convertUnit($dimension, $dimensionUnit, $mapUnit);

        /*
         * Check if $dimension is below or above the range of dimensions in the map
         */
        $firstPoint = $map[array_key_first($map)];
        $lastPoint = $map[array_key_last($map)];
        if ($dimension <= $firstPoint['dimension']) {
            return intval($firstPoint['resolution']);
        } elseif ($dimension >= $lastPoint['dimension']) {
            return intval($lastPoint['resolution']);
        }

        /*
         * Extract the 2 points from the map the dimension is between
         */
        $target = $fallbackResolution;
        foreach ($map as $k => $pointA) {
            if ($dimension < $pointA['dimension']) {
                continue;
            }
            $pointB = $map[$k + 1];
            $target = $this->linearInterpolation($dimension, $pointA['dimension'], $pointA['resolution'], $pointB['dimension'], $pointB['resolution']);
            break;
        }

        $target = intval(ceil($target));
        return $target;
    }

    /**
     * For the given dimension, get the effective resolution.
     *
     * @param int $pixels
     * @param float|int $dimension the integer value of the dimension e.g. 300
     * @param string $dimensionUnit the unit type of the given dimension e.g. mm
     * @return integer The effective resolution in dpi (e.g. 280dpi - without the dpi)
     */
    public function getEffectiveResolution(int $pixels, float|int $dimension, string $dimensionUnit): int
    {
        //convert dimension to inches
        $dimensionInches = $this->PDFGeometry->convertUnit($dimension, $dimensionUnit, 'in');
        $dpi = intval(floor($pixels / $dimensionInches));

        return $dpi;
    }

    /**
     * Calculate the target between the two points
     *
     * @param $input
     * @param $input1
     * @param $target1
     * @param $input2
     * @param $target2
     * @return float|int|mixed
     */
    private function linearInterpolation($input, $input1, $target1, $input2, $target2)
    {
        if ($input1 == $input2) {
            return $target1; // Avoid division by zero
        }

        return $target1 + (($input - $input1) / ($input2 - $input1)) * ($target2 - $target1);
    }

    /**
     * @return array
     */
    private function getDefaultAdaptiveResolutionsMap(): array
    {
        return [
            'unit' => 'mm',
            'map' => [
                ['dimension' => 400, 'resolution' => 300],
                ['dimension' => 600, 'resolution' => 280],
                ['dimension' => 800, 'resolution' => 250],
                ['dimension' => 1200, 'resolution' => 200],
                ['dimension' => 2000, 'resolution' => 150],
                ['dimension' => 4000, 'resolution' => 90],
                ['dimension' => 8000, 'resolution' => 70],
                ['dimension' => 16000, 'resolution' => 40],
            ]
        ];
    }

    /**
     * Provide a user defined map.
     * Input only has the basic checks applied.
     * Refer to getDefaultAdaptiveResolutionsMap() for structure.
     *
     * @param array $adaptiveResolutions
     * @return bool
     */
    public function setAdaptiveResolutions(array $adaptiveResolutions): bool
    {
        if (!isset($adaptiveResolutions['unit'])) {
            return false;
        }

        if (!isset($adaptiveResolutions['map'])) {
            return false;
        }

        //must have at least 2 points in the map
        if (count($adaptiveResolutions['map']) < 2) {
            return false;
        }

        //make sure points 1 and 2 have the required 'dimension' and 'resolution'
        if (!isset($adaptiveResolutions['map'][0]['dimension']) || !isset($adaptiveResolutions['map'][0]['resolution'])) {
            return false;
        }

        //make sure points 1 and 2 have the required 'dimension' and 'resolution'
        if (!isset($adaptiveResolutions['map'][1]['dimension']) || !isset($adaptiveResolutions['map'][1]['resolution'])) {
            return false;
        }

        $this->adaptiveResolutions = $adaptiveResolutions;
        return true;
    }


}