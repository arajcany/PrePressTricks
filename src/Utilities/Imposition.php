<?php

namespace arajcany\PrePressTricks\Utilities;

/**
 * This class helps to calculate an imposition layout & packaging data
 */
class Imposition
{
    public function __construct($properties = [])
    {

    }

    /**
     * Auto-calculate an imposition layout based on the given parameters.
     *
     * Accepted parameters (with defaults from getDefaultImpositionParameters()):
     *  - sheet_width         float
     *  - sheet_height        float
     *  - page_width          float
     *  - page_height         float
     *  - gutters_horizontal  float   space between columns
     *  - gutters_vertical    float   space between rows
     *  - margin_top          float
     *  - margin_bottom       float
     *  - margin_left         float
     *  - margin_right        float
     *  - auto_rotation       false|'sheet'|'page'
     *
     *  auto_rotation:
     *      false   = no auto-rotation allowed
     *      'sheet' = may rotate the sheet 90° and pick the better fit
     *      'page'  = may rotate the page 90° and pick the better fit
     *
     * The returned 'auto_rotation' value indicates what was actually applied:
     *      false   = no rotation used
     *      'sheet' = the sheet was rotated 90°
     *      'page'  = the page was rotated 90°
     */
    public function calculateColumnsAndRows($parameters = []): array
    {
        $defaultParameters = $this->getDefaultImpositionParameters();
        $parameters = array_merge($defaultParameters, $parameters);

        // Convenience locals
        $sheetWidth = (float)$parameters['sheet_width'];
        $sheetHeight = (float)$parameters['sheet_height'];
        $pageWidth = (float)$parameters['page_width'];
        $pageHeight = (float)$parameters['page_height'];

        $autoRotationMode = $parameters['auto_rotation']; // false|'sheet'|'page'

        // Helper: given specific orientations, compute how many rows/columns fit
        $computeLayout = function (
            float $sWidth,
            float $sHeight,
            float $pWidth,
            float $pHeight
        ) use ($parameters) {
            $marginLeft = (float)$parameters['margin_left'];
            $marginRight = (float)$parameters['margin_right'];
            $marginTop = (float)$parameters['margin_top'];
            $marginBottom = (float)$parameters['margin_bottom'];

            $gutterX = max(0.0, (float)$parameters['gutters_horizontal']);
            $gutterY = max(0.0, (float)$parameters['gutters_vertical']);

            // Usable area after margins
            $usableWidth = $sWidth - $marginLeft - $marginRight;
            $usableHeight = $sHeight - $marginTop - $marginBottom;

            // Guard against degenerate cases
            if ($usableWidth <= 0 || $usableHeight <= 0 || $pWidth <= 0 || $pHeight <= 0) {
                return [
                    'columns' => 0,
                    'rows' => 0,
                    'sheet_width' => $sWidth,
                    'sheet_height' => $sHeight,
                    'page_width' => $pWidth,
                    'page_height' => $pHeight,
                    'capacity' => 0,
                ];
            }

            // Formula: col * page + (col - 1) * gutter <= usable
            // => col <= (usable + gutter) / (page + gutter)
            $columns = (int)floor(($usableWidth + $gutterX) / ($pWidth + $gutterX));
            $rows = (int)floor(($usableHeight + $gutterY) / ($pHeight + $gutterY));

            if ($columns < 0) {
                $columns = 0;
            }
            if ($rows < 0) {
                $rows = 0;
            }

            return [
                'columns' => $columns,
                'rows' => $rows,
                'sheet_width' => $sWidth,
                'sheet_height' => $sHeight,
                'page_width' => $pWidth,
                'page_height' => $pHeight,
                'capacity' => $columns * $rows,
            ];
        };

        // Base orientation (no rotation)
        $best = $computeLayout($sheetWidth, $sheetHeight, $pageWidth, $pageHeight);
        $bestRotation = false;

        // If allowed, test rotating sheet
        if ($autoRotationMode === 'sheet') {
            $rot = $computeLayout($sheetHeight, $sheetWidth, $pageWidth, $pageHeight);
            if ($rot['capacity'] > $best['capacity']) {
                $best = $rot;
                $bestRotation = 'sheet';
            }
        }

        // If allowed, test rotating page
        if ($autoRotationMode === 'page') {
            $rot = $computeLayout($sheetWidth, $sheetHeight, $pageHeight, $pageWidth);
            if ($rot['capacity'] > $best['capacity']) {
                $best = $rot;
                $bestRotation = 'page';
            }
        }

        // Build final result array
        return [
            'columns' => $best['columns'],
            'rows' => $best['rows'],
            'sheet_width' => $best['sheet_width'],
            'sheet_height' => $best['sheet_height'],
            'page_width' => $best['page_width'],
            'page_height' => $best['page_height'],
            'gutters_horizontal' => (float)$parameters['gutters_horizontal'],
            'gutters_vertical' => (float)$parameters['gutters_vertical'],
            'margin_top' => (float)$parameters['margin_top'],
            'margin_bottom' => (float)$parameters['margin_bottom'],
            'margin_left' => (float)$parameters['margin_left'],
            'margin_right' => (float)$parameters['margin_right'],
            // What we *actually* ended up doing:
            'auto_rotation' => $bestRotation, // false|sheet|page
        ];
    }


    /**
     * Feed this function with the results of $this->calculateColumnsAndRows() + add to the array the following
     * - plex               int     1 or 2 sided.
     * - pp                 int     The number of unique pages in the document.
     * - qty                int     The quantity you would like printed.
     * - mode               str     repeated|sequential
     *
     * @param array $impositionParameters
     * @param bool $mergeWithImpositionParameter
     * @return array
     */
    public function calculateTotalSheets(array $impositionParameters, bool $mergeWithImpositionParameter = true): array
    {
        $nup = (int)($impositionParameters['columns'] * $impositionParameters['rows']);
        $plex = (int)$impositionParameters['plex'];
        $pp = (int)$impositionParameters['pp'];
        $qty = (int)$impositionParameters['qty'];
        $mode = strtolower((string)$impositionParameters['mode']);

        if ($nup <= 0 || $plex <= 0) {
            $sheets = false;
        } elseif ($mode === 'repeated') {
            // Each page is run as its own form, repeated on the sheet
            $sheets = ceil($qty / $nup) * ceil($pp / $plex);
        } elseif ($mode === 'sequential') {
            // Pages are laid out sequentially across the form
            $sheets = ceil((($pp / $plex) * $qty) / $nup);
        } else {
            $sheets = false;
        }

        $sheets = [
            'total_sheets' => $sheets,
        ];

        if ($mergeWithImpositionParameter) {
            return array_merge($impositionParameters, $sheets);
        } else {
            return $sheets;
        }
    }

    /**
     * Calculate the packing data that will be used in postage calculations
     *
     * @param array $stockProperties
     * @param array $impositionProperties
     * @return array
     */
    public function calculatePackingData(array $stockProperties = [], array $impositionProperties = []): array
    {
        $defaultStockProperties = $this->getDefaultStockProperties();
        $stockProperties = array_merge($defaultStockProperties, $stockProperties);

        $plex = (int)$impositionProperties['plex'];
        if ($plex <= 0) {
            return [
                'total_weight_kg' => 0,
                'width_mm' => $impositionProperties['page_width'],
                'height_mm' => $impositionProperties['page_height'],
                'depth_mm' => 0,
            ];
        }

        // Area of one finished leaf (one physical sheet in the product), in m²
        $paperArea = ($impositionProperties['page_width'] / 1000) * ($impositionProperties['page_height'] / 1000);

        // Number of leaves (finished sheets) in the product stack
        $leafCount = ceil($impositionProperties['pp'] / $plex) * $impositionProperties['qty'];

        // Total area of the finished product stack, in m²
        $totalPaperArea = $leafCount * $paperArea;

        // Calculate the depth of the stack
        $leafThicknessMM = $stockProperties['ream_depth_mm'] / $stockProperties['sheets_per_ream'];
        $depthMM = $leafCount * $leafThicknessMM;

        // Calculate the weight of a SqM of stock from ream data
        $stockSheetArea = ($stockProperties['width_mm'] / 1000) * ($stockProperties['height_mm'] / 1000);
        $stockSheetsPerSquareMeter = $stockSheetArea > 0 ? 1 / $stockSheetArea : 0;
        $kgPerSheet = $stockProperties['ream_weight_kg'] / $stockProperties['sheets_per_ream'];
        $kgPerSquareMeter = $kgPerSheet * $stockSheetsPerSquareMeter;

        // Multiply out to get the weight
        $totalWeightKg = round(($totalPaperArea * $kgPerSquareMeter), 3);

        return [
            'width_mm' => $impositionProperties['page_width'],
            'height_mm' => $impositionProperties['page_height'],
            'depth_mm' => $depthMM,
            'total_leaves' => $leafCount,
            'total_weight_kg' => $totalWeightKg,
        ];
    }


    public function getDefaultImpositionParameters(): array
    {
        return [
            'sheet_width' => 450,
            'sheet_height' => 320,
            'page_width' => 210,
            'page_height' => 297,
            'gutters_horizontal' => 0,
            'gutters_vertical' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
            'margin_right' => 0,
            'auto_rotation' => false, // false|sheet|page
        ];
    }


    public function getDefaultStockProperties(): array
    {
        return [
            'width_mm' => 210,
            'height_mm' => 297,
            'gsm' => 80,
            'sheets_per_ream' => 500,
            'ream_depth_mm' => 50,
            'ream_weight_kg' => 2.5,
            'reams_per_box' => 5,
        ];
    }

}
