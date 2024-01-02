<?php

namespace arajcany\PrePressTricks\Utilities;

class Boxes
{

    /**
     * Wrapper function for scaleInBox
     *
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param bool|int $rounding
     * @return array|bool
     */
    public function fitIntoBox(float|int $inWidth, float|int $inHeight, float|int $boxWidth, float|int $boxHeight, bool|int $rounding = false)
    {
        return $this->sizeIntoBox($inWidth, $inHeight, $boxWidth, $boxHeight, 'fit', $rounding);
    }

    /**
     * Wrapper function for sizeIntoBoxFactor
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param bool|int $rounding
     * @return array|bool
     */
    public function fitIntoBoxFactor(float|int $inWidth, float|int $inHeight, float|int $boxWidth, float|int $boxHeight, bool|int $rounding = false)
    {
        return $this->sizeIntoBoxFactor($inWidth, $inHeight, $boxWidth, $boxHeight, 'fit', $rounding);
    }

    /**
     * Wrapper function for scaleInBox
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param bool|int $rounding
     * @return array|bool
     */
    public function fillIntoBox(float|int $inWidth, float|int $inHeight, float|int $boxWidth, float|int $boxHeight, bool|int $rounding = false)
    {
        return $this->sizeIntoBox($inWidth, $inHeight, $boxWidth, $boxHeight, 'fill', $rounding);
    }

    /**
     * Wrapper function for sizeIntoBoxFactor
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param bool|int $rounding
     * @return array|bool
     */
    public function fillIntoBoxFactor(float|int $inWidth, float|int $inHeight, float|int $boxWidth, float|int $boxHeight, bool|int $rounding = false)
    {
        return $this->sizeIntoBoxFactor($inWidth, $inHeight, $boxWidth, $boxHeight, 'fill', $rounding);
    }

    /**
     * Wrapper function for sizeIntoBox
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param bool|int $rounding
     * @return array|bool
     */
    public function stretchIntoBox(float|int $inWidth, float|int $inHeight, float|int $boxWidth, float|int $boxHeight, bool|int $rounding = false)
    {
        return $this->sizeIntoBox($inWidth, $inHeight, $boxWidth, $boxHeight, 'stretch', $rounding);
    }

    /**
     * Wrapper function for sizeIntoBoxFactor
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param bool|int $rounding
     * @return array|bool
     */
    public function stretchIntoBoxFactor(float|int $inWidth, float|int $inHeight, float|int $boxWidth, float|int $boxHeight, bool|int $rounding = false)
    {
        return $this->sizeIntoBoxFactor($inWidth, $inHeight, $boxWidth, $boxHeight, 'stretch', $rounding);
    }

    /**
     * Wrapper function for scaleInBox
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param bool|int $rounding
     * @return array|bool
     */
    public function bestFitFactor(float|int $inWidth, float|int $inHeight, float|int $boxWidth, float|int $boxHeight, bool|int $rounding = false)
    {
        $normal = $this->fitIntoBoxFactor($inWidth, $inHeight, $boxWidth, $boxHeight, $rounding);
        $rotated = $this->fitIntoBoxFactor($inHeight, $inWidth, $boxWidth, $boxHeight, $rounding);

        //pick the largest scaling factor of $normal and $rotated
        if ($normal['scale_width'] >= $rotated['scale_width'] && $normal['scale_height'] >= $rotated['scale_height']) {
            $result = [
                'scale_width' => $normal['scale_width'],
                'scale_height' => $normal['scale_height'],
                'rotate' => true,
            ];
        } elseif ($normal['scale_width'] < $rotated['scale_width'] && $normal['scale_height'] < $rotated['scale_height']) {
            $result = [
                'scale_width' => $rotated['scale_width'],
                'scale_height' => $rotated['scale_height'],
                'rotate' => true,
            ];
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Calculate how to size a "Box A" into "Box B".
     *
     * Commonly used when you need to fit a photo into a frame and the photo and the frame are not the same ratio
     * Usually you would size the photo to one of the following rules:
     *  'fit' - Keep shrinking/expanding the image till you can see all of the image inside the frame. There might be white space inside the frame.
     *  'fill' - Make the image large/small enough so that it fills the entire are of the frame. Some of the image will be cropped.
     *  'stretch' - Fills the entire frame. The image will look distorted.
     *
     * Delivers back the new size of Box A (i.e. the photo) as an associated array of 'width' and 'height'
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param string $mode fit|fill|stretch
     * @param bool|int $rounding
     * @return array|bool
     */
    private function sizeIntoBox(float|int $inWidth = 1, float|int $inHeight = 1, float|int $boxWidth = 1, float|int $boxHeight = 1, string $mode = '', bool|int $rounding = false): bool|array
    {
        //scale using $boxWidth
        $outWidthUsingBoxWidth = $boxWidth;
        $outHeightUsingBoxWidth = ($boxWidth / $inWidth) * $inHeight;
        $outAreaUsingBoxWidth = $outWidthUsingBoxWidth * $outHeightUsingBoxWidth;
        if ($rounding) {
            if (is_int($rounding)) {
                $roundToDigits = $rounding;
            } else {
                $roundToDigits = 0;
            }
            $outHeightUsingBoxWidth = round($outHeightUsingBoxWidth, $roundToDigits);
        }

        //scale using $boxHeight
        $outWidthUsingBoxHeight = ($boxHeight / $inHeight) * $inWidth;
        $outHeightUsingBoxHeight = $boxHeight;
        $outAreaUsingBoxHeight = $outWidthUsingBoxHeight * $outHeightUsingBoxHeight;
        if ($rounding) {
            if (is_int($rounding)) {
                $roundToDigits = $rounding;
            } else {
                $roundToDigits = 0;
            }
            $outWidthUsingBoxHeight = round($outWidthUsingBoxHeight, $roundToDigits);
        }

        if (strtolower($mode) == 'fit') {
            //select based on min area
            if ($outAreaUsingBoxWidth <= $outAreaUsingBoxHeight) {
                return ['width' => $outWidthUsingBoxWidth, 'height' => $outHeightUsingBoxWidth];
            } else {
                return ['width' => $outWidthUsingBoxHeight, 'height' => $outHeightUsingBoxHeight];
            }
        } elseif (strtolower($mode) == 'fill') {
            //select based on max area
            if ($outAreaUsingBoxWidth >= $outAreaUsingBoxHeight) {
                return ['width' => $outWidthUsingBoxWidth, 'height' => $outHeightUsingBoxWidth];
            } else {
                return ['width' => $outWidthUsingBoxHeight, 'height' => $outHeightUsingBoxHeight];
            }
        } elseif (strtolower($mode) == 'stretch') {
            return ['width' => $boxWidth, 'height' => $boxHeight];
        } else {
            return false;
        }
    }

    /**
     * Similar to $this->sizeIntoBoxFactor() but the result is and associated array of 'scale_width' and 'scale_height'
     *
     * @param float|int $inWidth
     * @param float|int $inHeight
     * @param float|int $boxWidth
     * @param float|int $boxHeight
     * @param string $mode
     * @param bool|int $rounding
     * @return bool|array
     */
    private function sizeIntoBoxFactor(float|int $inWidth = 1, float|int $inHeight = 1, float|int $boxWidth = 1, float|int $boxHeight = 1, string $mode = '', bool|int $rounding = false): bool|array
    {
        $newSize = $this->sizeIntoBox($inWidth, $inHeight, $boxWidth, $boxHeight, $mode, false);
        $percentWidth = ($newSize['width'] / $inWidth);
        $percentHeight = ($newSize['height'] / $inHeight);

        if ($rounding) {
            if (is_int($rounding)) {
                $roundToDigits = $rounding;
            } else {
                $roundToDigits = 4;
            }
            $percentWidth = round($percentWidth, $roundToDigits);
            $percentHeight = round($percentHeight, $roundToDigits);
        }

        return [
            'scale_width' => $percentWidth,
            'scale_height' => $percentHeight,
        ];
    }


}