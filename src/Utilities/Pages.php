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
    public function pagesToCutAndStack($pp = null, $options = [])
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
     * @param string $rangeInput
     * @param array $options
     * @return mixed
     */
    public function rangeExpand($rangeInput = null, $options = [])
    {
        if ($this->is_blank($rangeInput)) {
            return false;
        }

        $defaultOptions = [
            'returnFormat' => 'string'
        ];
        $options = array_merge($defaultOptions, $options);

        $rangeFinal = [];

        $ranges = preg_replace('/[^0-9\-,.]/', '', $rangeInput);
        $ranges = explode(",", $ranges);
        foreach ($ranges as $range) {
            $range = explode("-", $range);

            if (isset($range[0])) {
                $lower = ceil(1 * $range[0]);
            } else {
                return false;
            }

            if (isset($range[1])) {
                $upper = ceil(1 * $range[1]);
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
            $rangeFinal = implode(",", $rangeFinal);
            return $rangeFinal;
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
     * @param type string $rangeInput
     * @param type array $options
     * @return mixed
     */
    public function rangeCompact($rangeInput = null, $options = [])
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
            $rangeFinalString = implode(",", $rangeFinalStringArray);
            return $rangeFinalString;
        } else {
            return false;
        }
    }

    /**
     * rangeFlip
     *
     * Takes a page range and inverts it. In other words, if finds the missing numbers.
     * You can define the lower and upper bounds of the final range
     *
     * Without lower and upper bounds
     * rangeFlip('3-4,10-20') => '5,6,7,8,9'
     * Notice how this automatically uses 3 and 20 as the lower and upper bounds
     *
     * With lower and upper bounds
     * rangeFlip('3-4,10-20', 1, 24) => '1,2,5,6,7,8,9,21,22,23,24'
     *
     * Will always return a string so rangeCompact() or rangeExpand() and define 'array' if needed
     *
     * @param null $rangeInput
     * @param null $lowerBound
     * @param null $upperBound
     * @return array|bool|string
     */
    public function rangeFlip(
        $rangeInput = null,
        $lowerBound = null,
        $upperBound = null
    ) {
        if ($this->is_blank($rangeInput)) {
            return false;
        }

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
        $difference = implode(',', $difference);

        return $difference;
    }

    /**
     * Replacement function to allow for numeric values in empty()
     *
     * @param $value
     * @return bool
     */
    public function is_blank($value)
    {
        return empty($value) && !is_numeric($value);
    }
}