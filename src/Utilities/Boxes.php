<?php

namespace arajcany\PrePressTricks\Utilities;


class Boxes
{

    /**
     * Wrapper function for scaleInBox
     *
     * @param $inWidth
     * @param $inHeight
     * @param $boxWidth
     * @param $boxHeight
     * @return array|bool
     */
    public function fitIntoBox($inWidth, $inHeight, $boxWidth, $boxHeight)
    {
        $result = $this->scaleIntoBox($inWidth, $inHeight, $boxWidth, $boxHeight, 'fit');
        return $result;
    }


    /**
     * Wrapper function for scaleInBox
     *
     * @param $inWidth
     * @param $inHeight
     * @param $boxWidth
     * @param $boxHeight
     * @return array|bool
     */
    public function fillIntoBox($inWidth, $inHeight, $boxWidth, $boxHeight)
    {
        $result = $this->scaleIntoBox($inWidth, $inHeight, $boxWidth, $boxHeight, 'fill');
        return $result;
    }


    /**
     * Calculate how to scale an image to 'fit' or 'fill' given box dimensions
     *
     * @param int $inWidth
     * @param int $inHeight
     * @param int $boxWidth
     * @param int $boxHeight
     * @param string $mode
     * @return array|bool
     */
    public function scaleIntoBox($inWidth = 1, $inHeight = 1, $boxWidth = 1, $boxHeight = 1, $mode = '')
    {
        $boxArea = $boxWidth * $boxHeight;

        //scale using $boxWidth
        $outWidthUsingBoxWidth = $boxWidth;
        $outHeightUsingBoxWidth = ($boxWidth / $inWidth) * $inHeight;
        $outAreaUsingBoxWidth = $outWidthUsingBoxWidth * $outHeightUsingBoxWidth;

        //scale using $boxHeight
        $outWidthUsingBoxHeight = ($boxHeight / $inHeight) * $inWidth;
        $outHeightUsingBoxHeight = $boxHeight;
        $outAreaUsingBoxHeight = $outWidthUsingBoxHeight * $outHeightUsingBoxHeight;

        if ($mode == 'fit') {
            //select based on min area
            if ($outAreaUsingBoxWidth <= $outAreaUsingBoxHeight) {
                return ['width' => $outWidthUsingBoxWidth, 'height' => $outHeightUsingBoxWidth];
            } else {
                return ['width' => $outWidthUsingBoxHeight, 'height' => $outHeightUsingBoxHeight];
            }
        } elseif ($mode == 'fill') {
            //select based on max area
            if ($outAreaUsingBoxWidth >= $outAreaUsingBoxHeight) {
                return ['width' => $outWidthUsingBoxWidth, 'height' => $outHeightUsingBoxWidth];
            } else {
                return ['width' => $outWidthUsingBoxHeight, 'height' => $outHeightUsingBoxHeight];
            }
        } else {
            return false;
        }

    }

}