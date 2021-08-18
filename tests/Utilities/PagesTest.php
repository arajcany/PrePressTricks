<?php

namespace Utilities;

use arajcany\PrePressTricks\Utilities\Pages;
use PHPUnit\Framework\TestCase;

class PagesTest extends TestCase
{

    public function testGroupByPageSequences()
    {
        $pages = new Pages();

        //----------------------------------------

        $fileNames = [
            'file_9_bar_02.png',
            'file_0_a_002.png',
            'file_9_bar_12.png',
            'file_0_a_001.png',
            'file_9_bar_04.png',
            'unrelated_file_001.png',
            'file_0_a_003.png',
            'file_0_a_004.png',
            'file_9_bar_05.png',
        ];

        $expected = [
            [
                'file_0_a_001.png',
                'file_0_a_002.png',
                'file_0_a_003.png',
                'file_0_a_004.png'
            ],
            [
                'file_9_bar_02.png',
                'file_9_bar_04.png',
                'file_9_bar_05.png',
                'file_9_bar_12.png'
            ],
            [
                'unrelated_file_001.png'
            ],
        ];
        $actual = $pages->groupByPageSequences($fileNames);
        $this->assertEquals($expected, $actual);

        //----------------------------------------

        $fileNames = [
            'file_9_bar_02.png',
            'file_0_bar_002.png',
            'file_9_bar_12.png',
            'file_0_bar_001.png',
            'file_9_bar_04.png',
            'unrelated_file_001.png',
            'file_0_bar_003.png',
            'file_0_bar_004.png',
            'file_9_bar_05.png',
        ];

        $expected = [
            [
                'file_0_bar_001.png',
                'file_0_bar_002.png',
                'file_0_bar_003.png',
                'file_0_bar_004.png',
                'file_9_bar_02.png',
                'file_9_bar_04.png',
                'file_9_bar_05.png',
                'file_9_bar_12.png'
            ],
            [
                'unrelated_file_001.png'
            ],
        ];
        $actual = $pages->groupByPageSequences($fileNames, false);
        $this->assertEquals($expected, $actual);

        //----------------------------------------

        $fileNames = [
            'file_1_bar_01.png',
            'file_2_bar_01.png',
        ];

        $expected = [
            [
                'file_1_bar_01.png',
            ],
            [
                'file_2_bar_01.png',
            ],
        ];
        $actual = $pages->groupByPageSequences($fileNames);
        $this->assertEquals($expected, $actual);

        //----------------------------------------

        $fileNames = [
            '01_file_1_bar.png',
            '02_file_1_bar.png',
            '01_file_2_bar.png',
            '02_file_2_bar.png',
        ];

        $expected = [
            [
                '01_file_1_bar.png',
                '02_file_1_bar.png',
            ],
            [
                '01_file_2_bar.png',
                '02_file_2_bar.png',
            ],
        ];
        $actual = $pages->groupByPageSequences($fileNames);
        $this->assertEquals($expected, $actual);

        //----------------------------------------

        $fileNames = [
            '01_file_1_bar_001.png',
            '02_file_1_bar_003.pdf',
            '01_file_2_bar_001.png',
            '02_file_2_bar_003.pdf',
        ];

        $expected = [
            [
                '01_file_1_bar_001.png',
            ],
            [
                '01_file_2_bar_001.png',
            ],
            [
                '02_file_1_bar_003.pdf',
            ],
            [
                '02_file_2_bar_003.pdf',
            ],
        ];
        $actual = $pages->groupByPageSequences($fileNames);
        $this->assertEquals($expected, $actual);

        //----------------------------------------
    }

}