<?php

namespace arajcany\PrePressTricks\Utilities;


class Pages
{

    /**
     * pagesToCutAndStack
     *
     * Calculates either:
     *  - the height of a stack of sheets when given the [total_stacks]
     *  - how many stack when given [sheets_per_stack]
     * Used when doing n-up impositions
     *
     * @param null $pp
     * @param array $options
     * @return array
     */
    public function pagesToCutAndStack($pp = null, $options = []): array
    {
        $defaultOptions = [
            'total_stacks' => null,
            'sheets_per_stack' => null,
            'plex' => 1,
        ];

        $options = array_merge($defaultOptions, $options);

        if (empty($options['total_stacks']) && empty($options['sheets_per_stack'])) {
            $options['total_stacks'] = 1;
        }

        //return array
        $pageStacks = [];

        if (is_numeric($options['total_stacks'])) {
            $total_stacks = $options['total_stacks'];
            $sheets_per_stack = ceil($pp / $options['total_stacks']);
            $pages_per_stack = $sheets_per_stack * $options['plex'];

            $counter = range(1, $total_stacks);
            $pagePositionStart = 1;
            foreach ($counter as $count) {
                $pagePositionEnd = $pagePositionStart + $pages_per_stack - 1;
                if ($pagePositionStart > $pp) {
                    //if you force N stacks, you may get an empty stack
                    $pageStacks[] = null;
                } else {
                    if ($pagePositionEnd > $pp) {
                        $pagePositionEnd = $pp;
                    }
                    $pageStacks[] = $pagePositionStart . "-" . $pagePositionEnd;
                }

                $pagePositionStart = $pagePositionEnd + 1;
            }
        }


        if (is_numeric($options['sheets_per_stack'])) {
            $total_stacks = ceil($pp / $options['sheets_per_stack']);
            $sheets_per_stack = $options['sheets_per_stack'];
            $pages_per_stack = $sheets_per_stack * $options['plex'];

            $counter = range(1, $total_stacks);
            $pagePositionStart = 1;
            foreach ($counter as $count) {
                $pagePositionEnd = $pagePositionStart + $pages_per_stack - 1;
                if ($pagePositionStart <= $pp) {

                    if ($pagePositionEnd > $pp) {
                        $pagePositionEnd = $pp;
                    }
                    $pageStacks[] = $pagePositionStart . "-" . $pagePositionEnd;
                }

                $pagePositionStart = $pagePositionEnd + 1;
            }
        }

        return $pageStacks;
    }


    /**
     * rangeExpand
     *
     * Expand a page range string to comma delimited string or array.
     * Will correct the page order.
     * Will correct overlapping page ranges.
     * e.g. '12-13,1-4,6' => '1,2,3,4,6,12,13'
     *
     * @param array|string|null $rangeInput
     * @param array $options
     * @return false|array|string
     */
    public function rangeExpand(array|string $rangeInput = null, array $options = []): false|array|string
    {
        if ($this->is_blank($rangeInput)) {
            return false;
        }

        //replace en and em dashes with a plain dash
        $dashesEnEm = ['–', '—'];
        $rangeInput = str_replace($dashesEnEm, '-', $rangeInput);

        $defaultOptions = [
            'returnFormat' => 'string'
        ];
        $options = array_merge($defaultOptions, $options);

        $rangeFinal = [];

        if (is_string($rangeInput)) {
            $ranges = preg_replace('/[^0-9\-,.]/', '', $rangeInput);
            $ranges = explode(",", $ranges);
        } elseif (is_array($rangeInput)) {
            $ranges = $rangeInput;
        } else {
            return false;
        }

        foreach ($ranges as $range) {
            $range = str_replace(" ", "", $range);
            $range = explode("-", $range);

            if (isset($range[0])) {
                $lower = strval(intval(ceil(1 * $range[0])));
            } else {
                return false;
            }

            if (isset($range[1])) {
                $upper = strval(intval(ceil(1 * $range[1])));
            } else {
                $upper = $lower;
            }

            $range = range($lower, $upper);
            $rangeFinal = array_merge($rangeFinal, $range);
        }

        $rangeFinal = array_unique($rangeFinal);
        sort($rangeFinal);

        if ($options['returnFormat'] == 'array') {
            return $rangeFinal;
        } elseif ($options['returnFormat'] == 'string') {
            return implode(",", $rangeFinal);
        } else {
            return false;
        }
    }

    /**
     * rangeCompact
     *
     * Compact a string of page numbers to a comma delimited string or array.
     * Corrects the page order.
     * Corrects overlapping page ranges.
     * e.g. '3,4,6,12,13,10-20,1,2,8' => '1-4,6,8,10-20'
     *
     * If $duplicateStringSingles==true then single pages
     * will display as a range.
     * e.g. '3,4,6,12,13,10-20,1,2,8' => '1-4,6-6,8-8,10-20'
     *
     * @param array|string|null $rangeInput
     * @param array $options
     * @return false|array|string
     */
    public function rangeCompact(array|string $rangeInput = null, array $options = []): false|array|string
    {
        if ($this->is_blank($rangeInput)) {
            return false;
        }

        $defaultOptions = [
            'returnFormat' => 'string',
            'duplicateStringSingles' => false
        ];
        $options = array_merge($defaultOptions, $options);

        //expand the range first as this will clean
        $numbers = $this->rangeExpand($rangeInput, ['returnFormat' => 'array']);

        $rangeFinal = [];
        $lower = null;
        $upper = null;

        foreach ($numbers as $key => $number) {

            //define current number
            $currNumber = $number;

            //define next number
            if (isset($numbers[$key + 1])) {
                $nextNumber = $numbers[$key + 1];
            } else {
                $nextNumber = null;
            }

            //search for the lower bound
            if ($lower == null) {
                $lower = $currNumber;
            }

            //search for the upper bound
            if ($upper == null) {
                if ($currNumber + 1 != $nextNumber) {
                    $upper = $currNumber;
                }
            }

            //add to the final array
            if ($lower != null && $upper != null) {

                if ($lower == $upper) {
                    $stringSingle = $lower;
                    $stringDouble = $lower . '-' . $upper;
                } else {
                    $stringSingle = $lower . '-' . $upper;
                    $stringDouble = $lower . '-' . $upper;
                }

                $rangeFinal[] = array(
                    $lower,
                    $upper,
                    'lower' => $lower,
                    'upper' => $upper,
                    'single' => $stringSingle,
                    'double' => $stringDouble
                );
                $lower = null;
                $upper = null;
            }
        }

        if ($options['returnFormat'] == 'array') {
            return $rangeFinal;
        } elseif ($options['returnFormat'] == 'string') {
            $rangeFinalStringArray = [];
            foreach ($rangeFinal as $range) {
                if ($options['duplicateStringSingles'] == false) {
                    $rangeFinalStringArray[] = $range['single'];
                } else {
                    $rangeFinalStringArray[] = $range['double'];
                }
            }
            return implode(",", $rangeFinalStringArray);
        } else {
            return false;
        }
    }

    /**
     * rangeFlip
     *
     * Takes a page range and inverts it. In other words, it finds the missing numbers.
     * You can define the lower and upper bounds of the final range
     *
     * Without lower and upper bounds
     * rangeFlip('3-4,10-20') => '5,6,7,8,9'
     * Notice how this automatically uses 3 and 20 as the lower and upper bounds
     *
     * With lower and upper bounds
     * rangeFlip('3-4,10-20', 1, 24) => '1,2,5,6,7,8,9,21,22,23,24'
     *
     * Will always return a string. Use rangeCompact() or rangeExpand() and define 'array' if needed
     *
     * @param null $rangeInput
     * @param null $lowerBound
     * @param null $upperBound
     * @param array $options
     * @return bool|array|string
     */
    public function rangeFlip($rangeInput = null, $lowerBound = null, $upperBound = null, array $options = []): bool|array|string
    {
        if ($this->is_blank($rangeInput)) {
            return false;
        }

        $defaultOptions = [
            'returnFormat' => 'string',
        ];
        $options = array_merge($defaultOptions, $options);

        //expand the range first as this will clean
        $numbers = $this->rangeExpand($rangeInput, ['returnFormat' => 'array']);

        //clone for array_shift() and array_pop()
        $numbersForBounds = $numbers;

        $lowerBound = preg_replace('/[^0-9\-,.]/', '', $lowerBound);
        if (empty($lowerBound)) {
            $lowerBound = array_shift($numbersForBounds);
        }

        $upperBound = preg_replace('/[^0-9\-,.]/', '', $upperBound);
        if (empty($upperBound)) {
            $upperBound = array_pop($numbersForBounds);
        }

        $bounds = range($lowerBound, $upperBound);

        $difference = array_diff($bounds, $numbers);

        if ($options['returnFormat'] == 'array') {
            return $difference;
        } elseif ($options['returnFormat'] == 'string') {
            return implode(",", $difference);
        } else {
            return false;
        }
    }

    /**
     * getMinToMax
     *
     * Simple way to get the min and max page number from a string.
     *
     * @param null $rangeInput
     * @param array $options
     * @return array|false|string
     */
    public function getMinToMax($rangeInput = null, array $options = []): bool|array|string
    {
        if ($this->is_blank($rangeInput)) {
            return false;
        }

        $defaultOptions = [
            'returnFormat' => 'string',
        ];
        $options = array_merge($defaultOptions, $options);

        //expand the range first as this will clean
        $numbers = $this->rangeExpand($rangeInput, ['returnFormat' => 'array']);

        $min = min($numbers);
        $max = max($numbers);

        if ($options['returnFormat'] == 'array') {
            return [$min, $max];
        } elseif ($options['returnFormat'] == 'string') {
            return $min . "-" . $max;
        } else {
            return false;
        }
    }

    /**
     * Replacement function to allow for numeric values in empty()
     *
     * @param $value
     * @return bool
     */
    public function is_blank($value): bool
    {
        return empty($value) && !is_numeric($value);
    }


    /**
     * Sort an array of values into logical groups (with natural sorting applied) based on sequential numbering.
     * This function only looks for Number Sequences at the start or end of a filename.
     *
     * Consider the following array:
     * [
     *  'file_9_bar_02.png',
     *  'file_0_a_002.png',
     *  'file_9_bar_12.png',
     *  'file_0_a_001.png',
     *  'file_9_bar_04.png',
     *  'unrelated_file_001.png',
     *  'file_0_a_003.png',
     *  'file_0_a_004.png',
     *  'file_9_bar_05.png',
     * ]
     *
     * Function will return the following:
     * [
     *    [
     *      'file_0_a_001.png',
     *      'file_0_a_002.png',
     *      'file_0_a_003.png',
     *      'file_0_a_004.png',
     *    ],
     *    [
     *      'file_9_bar_02.png',
     *      'file_9_bar_04.png',
     *      'file_9_bar_05.png',
     *      'file_9_bar_12.png',
     *    ],
     *    [
     *      'unrelated_file_001.png',
     *    ]
     * ]
     *
     * As you can see, this would be really handy if you had a folder full of images from
     * multiple ripped PDF files and you needed to group them by the original document.
     *
     * @param $pages
     * @param bool $considerOnlyStartAndEnd
     * @return array
     */
    public function groupByPageSequences($pages, bool $considerOnlyStartAndEnd = true): array
    {
        //sort the pages
        natsort($pages);
        $pages = array_values($pages);

        $pagesNumbersRemoved = [];
        foreach ($pages as $page) {
            if ($considerOnlyStartAndEnd) {
                $pageTmp = pathinfo($page, PATHINFO_FILENAME);
                $pageExtTmp = str_replace($pageTmp, '', $page);
                $pagesNumbersRemoved[] = trim($pageTmp, "0123456789") . $pageExtTmp;
            } else {
                $pagesNumbersRemoved[] = str_replace(range(0, 9), '', $page);
            }
        }

        $uniquePageSequences = array_unique($pagesNumbersRemoved);

        $grouped = [];
        foreach ($uniquePageSequences as $k => $uniquePageName) {
            foreach ($pagesNumbersRemoved as $p => $pageNumbersRemoved) {
                if ($uniquePageName === $pageNumbersRemoved) {
                    $grouped[$k][] = $pages[$p];
                }
            }
        }

        return array_values($grouped);
    }
}