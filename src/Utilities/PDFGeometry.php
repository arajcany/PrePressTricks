<?php


namespace arajcany\PrePressTricks\Utilities;


class PDFGeometry
{
    /**
     * PDFGeometry constructor.
     */
    public function __construct()
    {

    }

    /**
     * Parse a string (or array) into a geometry array of 4 value [xll,yll,xur,yur].
     *
     * Regex is used to extract the coordinates so valid string formats include:
     *      "BleedBox[8.503937 18.108204 867.76184 630.6161]"
     *      "0,0,854,521"
     *
     * Can take an array and validate it. Arrays must either be:
     *      - exactly 4 numeric values
     *      - have the keys "left", "right", "top", "bottom" and they contain numeric values
     *
     * @param array|string $geometry
     * @param bool $named
     * @return int[]|false
     */
    public function parseGeometry($geometry, $named = false)
    {
        $naming = ['left', 'bottom', 'right', 'top'];

        if (is_string($geometry)) {
            $re = '/[0-9.]+/';
            preg_match_all($re, $geometry, $matches, PREG_SET_ORDER, 0);


            if (count($matches) == 4) {
                if (isset($matches[0][0]) && isset($matches[1][0]) && isset($matches[2][0]) && isset($matches[3][0])) {
                    $geo = [$matches[0][0], $matches[1][0], $matches[2][0], $matches[3][0]];
                    if ($named) {
                        return array_combine($naming, $geo);
                    } else {
                        return $geo;
                    }
                }
            } else {
                return false;
            }
        }

        if (is_array($geometry)) {
            if (count($geometry) == 4) {
                if (isset($geometry[0]) && isset($geometry[1]) && isset($geometry[2]) && isset($geometry[3])) {
                    if ((0 + $geometry[0]) != $geometry[0]) {
                        return false;
                    }
                    if ((0 + $geometry[1]) != $geometry[1]) {
                        return false;
                    }
                    if ((0 + $geometry[2]) != $geometry[2]) {
                        return false;
                    }
                    if ((0 + $geometry[3]) != $geometry[3]) {
                        return false;
                    }

                    $geo = [0 + $geometry[0], 0 + $geometry[1], 0 + $geometry[2], 0 + $geometry[3]];
                    if ($named) {
                        return array_combine($naming, $geo);
                    } else {
                        return $geo;
                    }
                }
            } elseif (isset($geometry['left']) && isset($geometry['bottom']) && isset($geometry['right']) && isset($geometry['top'])) {
                if ((0 + $geometry['left']) != $geometry['left']) {
                    return false;
                }
                if ((0 + $geometry['bottom']) != $geometry['bottom']) {
                    return false;
                }
                if ((0 + $geometry['right']) != $geometry['right']) {
                    return false;
                }
                if ((0 + $geometry['top']) != $geometry['top']) {
                    return false;
                }

                $geo = [0 + $geometry['left'], 0 + $geometry['bottom'], 0 + $geometry['right'], 0 + $geometry['top']];
                if ($named) {
                    return array_combine($naming, $geo);
                } else {
                    return $geo;
                }
            } else {
                return false;
            }
        }

        return false;
    }

    public function parseRotation($angle)
    {
        while ($angle >= 360) {
            $angle = $angle - 360;
        }

        while ($angle <= -360) {
            $angle = $angle + 360;
        }

        if ($angle !== 90 && $angle !== 180 && $angle !== 270) {
            $angle = 0;
        }

        return $angle;
    }


    public function getEffectiveGeometry($boxGeometry, $rotation = 0, $scaling = 1, $boundingBoxGeometry = null)
    {
        if ($boundingBoxGeometry == null) {
            $boundingBoxGeometry = $boxGeometry;
        }
        $boxGeometry = $this->parseGeometry($boxGeometry, true);
        $boundingBoxGeometry = $this->parseGeometry($boundingBoxGeometry, true);
        $rotation = $this->parseRotation($rotation);

        //apply scaling
        $boxGeometry = $this->scaleGeometry($boxGeometry, $scaling);
        $boundingBoxGeometry = $this->scaleGeometry($boundingBoxGeometry, $scaling);

        //after rotation, the object needs to sit in the same left-bottom position
        $originalLeft = $boundingBoxGeometry['left'];
        $originalBottom = $boundingBoxGeometry['bottom'];

        if ($rotation == 90) {
            $rotationOrigin = [
                'x' => $boundingBoxGeometry['left'],
                'y' => $boundingBoxGeometry['top']
            ];
        } elseif ($rotation == 180) {
            $rotationOrigin = [
                'x' => $boundingBoxGeometry['right'],
                'y' => $boundingBoxGeometry['top']
            ];
        } elseif ($rotation == 270) {
            $rotationOrigin = [
                'x' => $boundingBoxGeometry['right'],
                'y' => $boundingBoxGeometry['bottom']
            ];
        } else {
            $rotationOrigin = [
                'x' => $boundingBoxGeometry['left'],
                'y' => $boundingBoxGeometry['bottom']
            ];
        }

        //rotate the **box** around the specified origin
        $boxCorner1 = [
            'x' => $boxGeometry['left'],
            'y' => $boxGeometry['top']
        ];
        $boxCorner2 = [
            'x' => $boxGeometry['right'],
            'y' => $boxGeometry['top']
        ];
        $boxCorner3 = [
            'x' => $boxGeometry['left'],
            'y' => $boxGeometry['bottom']
        ];
        $boxCorner4 = [
            'x' => $boxGeometry['right'],
            'y' => $boxGeometry['bottom']
        ];
        $boxCorner1 = $this->rotatePointAroundOrigin($boxCorner1, $rotation, $rotationOrigin);
        $boxCorner2 = $this->rotatePointAroundOrigin($boxCorner2, $rotation, $rotationOrigin);
        $boxCorner3 = $this->rotatePointAroundOrigin($boxCorner3, $rotation, $rotationOrigin);
        $boxCorner4 = $this->rotatePointAroundOrigin($boxCorner4, $rotation, $rotationOrigin);
        $boxLeft = min($boxCorner1['x'], $boxCorner2['x'], $boxCorner3['x'], $boxCorner4['x']);
        $boxBottom = min($boxCorner1['y'], $boxCorner2['y'], $boxCorner3['y'], $boxCorner4['y']);
        $boxRight = max($boxCorner1['x'], $boxCorner2['x'], $boxCorner3['x'], $boxCorner4['x']);
        $boxTop = max($boxCorner1['y'], $boxCorner2['y'], $boxCorner3['y'], $boxCorner4['y']);


        //rotate the **bounding box** around the specified origin
        $boundingBoxCorner1 = [
            'x' => $boundingBoxGeometry['left'],
            'y' => $boundingBoxGeometry['top']
        ];
        $boundingBoxCorner2 = [
            'x' => $boundingBoxGeometry['right'],
            'y' => $boundingBoxGeometry['top']
        ];
        $boundingBoxCorner3 = [
            'x' => $boundingBoxGeometry['left'],
            'y' => $boundingBoxGeometry['bottom']
        ];
        $boundingBoxCorner4 = [
            'x' => $boundingBoxGeometry['right'],
            'y' => $boundingBoxGeometry['bottom']
        ];
        $boundingBoxCorner1 = $this->rotatePointAroundOrigin($boundingBoxCorner1, $rotation, $rotationOrigin);
        $boundingBoxCorner2 = $this->rotatePointAroundOrigin($boundingBoxCorner2, $rotation, $rotationOrigin);
        $boundingBoxCorner3 = $this->rotatePointAroundOrigin($boundingBoxCorner3, $rotation, $rotationOrigin);
        $boundingBoxCorner4 = $this->rotatePointAroundOrigin($boundingBoxCorner4, $rotation, $rotationOrigin);
        $boundingBoxLeft = min($boundingBoxCorner1['x'], $boundingBoxCorner2['x'], $boundingBoxCorner3['x'], $boundingBoxCorner4['x']);
        $boundingBoxBottom = min($boundingBoxCorner1['y'], $boundingBoxCorner2['y'], $boundingBoxCorner3['y'], $boundingBoxCorner4['y']);
        $boundingBoxRight = max($boundingBoxCorner1['x'], $boundingBoxCorner2['x'], $boundingBoxCorner3['x'], $boundingBoxCorner4['x']);
        $boundingBoxTop = max($boundingBoxCorner1['y'], $boundingBoxCorner2['y'], $boundingBoxCorner3['y'], $boundingBoxCorner4['y']);

        //figure out how much everything need to shift by to bring it back into the left-bottom position
        $moveX = $originalLeft - $boundingBoxLeft;
        $moveY = $originalBottom - $boundingBoxBottom;
        $boxLeft = $boxLeft + $moveX;
        $boxBottom = $boxBottom + $moveY;
        $boxRight = $boxRight + $moveX;
        $boxTop = $boxTop + $moveY;
        $boundingBoxLeft = $boundingBoxLeft + $moveX;
        $boundingBoxBottom = $boundingBoxBottom + $moveY;
        $boundingBoxRight = $boundingBoxRight + $moveX;
        $boundingBoxTop = $boundingBoxTop + $moveY;

        $boxGeometry = [
            'left' => $boxLeft,
            'bottom' => $boxBottom,
            'right' => $boxRight,
            'top' => $boxTop,
            'width' => $boxRight - $boxLeft,
            'height' => $boxTop - $boxBottom,
            'anchors' => [
                7 => [$boxLeft, $boxTop],
                8 => [($boxLeft + $boxRight) / 2, $boxTop],
                9 => [$boxRight, $boxTop],
                4 => [$boxLeft, ($boxBottom + $boxTop) / 2],
                5 => [($boxLeft + $boxRight) / 2, ($boxBottom + $boxTop) / 2],
                6 => [$boxRight, ($boxBottom + $boxTop) / 2],
                1 => [$boxLeft, $boxBottom],
                2 => [($boxLeft + $boxRight) / 2, $boxBottom],
                3 => [$boxRight, $boxBottom],
            ]
        ];

        return $boxGeometry;
    }

    private function scaleGeometry($parsedGeometry, $scalingFactor)
    {
        $parsedGeometry['left'] = $parsedGeometry['left'] * $scalingFactor;
        $parsedGeometry['bottom'] = $parsedGeometry['bottom'] * $scalingFactor;
        $parsedGeometry['right'] = $parsedGeometry['right'] * $scalingFactor;
        $parsedGeometry['top'] = $parsedGeometry['top'] * $scalingFactor;

        return $parsedGeometry;
    }

    private function rotateGeometry($parsedGeometry, $parsedRotation)
    {
        if ($parsedRotation == 0 || $parsedRotation == 180) {
            $width = $parsedGeometry['right'] - $parsedGeometry['left'];
            $height = $parsedGeometry['top'] - $parsedGeometry['bottom'];
        } else {
            $width = $parsedGeometry['top'] - $parsedGeometry['bottom'];
            $height = $parsedGeometry['right'] - $parsedGeometry['left'];
        }

        if ($parsedRotation == 90) {
            //left takes value from top
            //bottom takes value from left
            //right takes value from bottom
            //top takes value from right
            $tmpGeo = [
                'left' => $parsedGeometry['top'] - $width,
                'bottom' => $parsedGeometry['left'],
                'right' => $parsedGeometry['bottom'] + $width,
                'top' => $parsedGeometry['right'],
            ];
        } elseif ($parsedRotation == 180) {
            //left takes value from right
            //bottom takes value from top
            //right takes value from left
            //top takes value from bottom
            $tmpGeo = [
                'left' => $parsedGeometry['right'] - $width,
                'bottom' => $parsedGeometry['top'] - $height,
                'right' => $parsedGeometry['left'] + $width,
                'top' => $parsedGeometry['bottom'] + $height,
            ];
        } elseif ($parsedRotation == 270) {
            //left takes value from bottom
            //bottom takes value from right
            //right takes value from top
            //top takes value from left
            $tmpGeo = [
                'left' => $parsedGeometry['bottom'],
                'bottom' => $parsedGeometry['right'] - $height,
                'right' => $parsedGeometry['top'],
                'top' => $parsedGeometry['left'] + $height,
            ];
        } else {
            //no rotation
            $tmpGeo = [
                'left' => $parsedGeometry['left'],
                'bottom' => $parsedGeometry['bottom'],
                'right' => $parsedGeometry['right'],
                'top' => $parsedGeometry['top'],
            ];
        }

        return $tmpGeo;
    }

    private function rotateTranslation($parsedTranslation, $ccwRotation)
    {

        while ($ccwRotation >= 360) {
            $ccwRotation = $ccwRotation - 360;
        }

        while ($ccwRotation <= -360) {
            $ccwRotation = $ccwRotation + 360;
        }

        if ($ccwRotation == 90) {
            $tmpTranslation = [
                'x' => -1 * $parsedTranslation['y'],
                'y' => $parsedTranslation['x'],
            ];
        } elseif ($ccwRotation == 180) {
            $tmpTranslation = [
                'x' => -1 * $parsedTranslation['x'],
                'y' => -1 * $parsedTranslation['y'],
            ];
        } elseif ($ccwRotation == 270) {
            $tmpTranslation = [
                'x' => $parsedTranslation['y'],
                'y' => -1 * $parsedTranslation['x'],
            ];
        } else {
            //no rotation
            $tmpTranslation = [
                'x' => $parsedTranslation['x'],
                'y' => $parsedTranslation['y'],
            ];
        }

        return $tmpTranslation;
    }

    public function getWidth($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['width'];
    }

    public function getHeight($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['height'];
    }

    public function getAnchorTopLeft($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][7];
    }

    public function getAnchorTopCenter($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][8];
    }

    public function getAnchorTopRight($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][9];
    }

    public function getAnchorLeftCenter($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][4];
    }

    public function getAnchorCenter($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][5];
    }

    public function getAnchorRightCenter($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][6];
    }

    public function getAnchorBottomLeft($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][1];
    }

    public function getAnchorBottomCenter($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][2];
    }

    public function getAnchorBottomRight($geometry, $rotation = 0, $scaling = 1)
    {
        $effectiveGeo = $this->getEffectiveGeometry($geometry, $rotation, $scaling);
        return $effectiveGeo['anchors'][3];
    }


    public function getAnchorCoordinates($geometry, $rotation = 0, $scaling = 1)
    {
        $masters = [
            '2' => ['AnchorPoint.BOTTOM_CENTER_ANCHOR', 'BOTTOM_CENTER_ANCHOR', 1095656035, 'ANbc', 2, 'bc', '2', 'bottom center'],
            '1' => ['AnchorPoint.BOTTOM_LEFT_ANCHOR', 'BOTTOM_LEFT_ANCHOR', 1095656044, 'ANbl', 1, 'bl', '1', 'bottom left'],
            '3' => ['AnchorPoint.BOTTOM_RIGHT_ANCHOR', 'BOTTOM_RIGHT_ANCHOR', 1095656050, 'ANbr', 3, 'br', '3', 'bottom right'],
            '5' => ['AnchorPoint.CENTER_ANCHOR', 'CENTER_ANCHOR', 1095656308, 'ANct', 5, 'ct', '5', 'center'],
            '4' => ['AnchorPoint.LEFT_CENTER_ANCHOR', 'LEFT_CENTER_ANCHOR', 1095658595, 'ANlc', 4, 'lc', '4', 'left center'],
            '6' => ['AnchorPoint.RIGHT_CENTER_ANCHOR', 'RIGHT_CENTER_ANCHOR', 1095660131, 'ANrc', 6, 'rc', '6', 'right center'],
            '8' => ['AnchorPoint.TOP_CENTER_ANCHOR', 'TOP_CENTER_ANCHOR', 1095660643, 'ANtc', 8, 'tc', '8', 'top center'],
            '7' => ['AnchorPoint.TOP_LEFT_ANCHOR', 'TOP_LEFT_ANCHOR', 1095660652, 'ANtl', 7, 'tl', '7', 'top left'],
            '9' => ['AnchorPoint.TOP_RIGHT_ANCHOR', 'TOP_RIGHT_ANCHOR', 1095660658, 'ANtr', 9, 'tr', '9', 'top right']
        ];

        //$anchorPoint = $this->getMasterKeyFromUnknown($masters, $anchorPoint, 5);
    }

    /**
     * Extract the Key from the nested arrays.
     *
     * e.g. consider the following array.
     * [
     *  'foo' => [a,b,c,d,e],
     *  'bar' => [f,g,h,i,j]
     * ]
     *  - 'e' would return 'foo'
     *  - 'g' would return 'bar'
     *  - 'xxx' would return $default
     *
     * @param array $masters
     * @param string|bool|null $unknown
     * @param string $default
     * @return string
     */
    private function getMasterKeyFromUnknown(array $masters, $unknown, $default = '')
    {
        if (is_array($unknown)) {
            $unknown = implode("", $unknown);
        }

        foreach ($masters as $masterKey => $values) {

            //check if the $unknown is actually a masterKey
            if (is_string($unknown)) {
                if (strtolower($masterKey) === strtolower($unknown)) {
                    return $masterKey;
                }
            }

            foreach ($values as $value) {
                if ($unknown === true || $unknown === false || $unknown === null) {
                    if ($unknown === $value) {
                        return $masterKey;
                    }
                } elseif ($value === $unknown) {
                    return $masterKey;
                } elseif (is_string($value) && is_string($unknown)) {
                    if (strtolower($value) === strtolower($unknown)) {
                        return $masterKey;
                    }
                }
            }
        }

        return $default;
    }

    public function rotateGeometryAroundOrigin($parsedGeometry, $angle, $xyOrigin)
    {

    }


    /**
     * Wrapper function
     *
     * @param $xyCoordinates
     * @param $angle
     * @param $xyOrigin
     * @return array
     */
    function rotatePointAroundOrigin($xyCoordinates, $angle, $xyOrigin)
    {
        $c = $this->_rotatePoint($xyCoordinates['x'], $xyCoordinates['y'], $angle, $xyOrigin['x'], $xyOrigin['y']);
        return ['x' => $c[0], 'y' => $c[1]];
    }

    /**
     * Rotate a point around the given origin.
     *
     * @param $xPoint
     * @param $yPoint
     * @param $angle
     * @param int $xOrigin
     * @param int $yOrigin
     * @param int $precision
     * @return array
     */
    private function _rotatePoint($xPoint, $yPoint, $angle, $xOrigin = 0, $yOrigin = 0, $precision = 10)
    {
        $radians = deg2rad($angle);
        $xNew = $xOrigin + (cos($radians) * ($xPoint - $xOrigin) - sin($radians) * ($yPoint - $yOrigin));
        $yNew = $yOrigin + (sin($radians) * ($xPoint - $xOrigin) + cos($radians) * ($yPoint - $yOrigin));

        $xNew = round($xNew, $precision);
        $yNew = round($yNew, $precision);

        return [$xNew, $yNew];
    }


}