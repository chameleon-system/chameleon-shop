<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Tests\objects\ArticleList;

use ChameleonSystem\ShopBundle\objects\ArticleList\SortString;
use PHPUnit\Framework\TestCase;

class SortStringTest extends TestCase
{
    /**
     * @var SortString
     */
    private $sortString;
    private $sortArray;

    /**
     * @test
     *
     * @dataProvider dataProviderSortRawData
     */
    public function itShouldReturnAnSortArray($sortString, $expectedArray)
    {
        $this->givenASortTypeWith($sortString);
        $this->whenICallGetAsArray();
        $this->thenIShouldHave($expectedArray);
    }

    public function dataProviderSortRawData()
    {
        return [
            ['', []],
            ['field2 ASC, field3 DESC, field4', ['field2' => 'ASC', 'field3' => 'DESC', 'field4' => 'ASC']],
            ['field2 ASC, field3 DESC, field4', ['field2' => 'ASC', 'field3' => 'DESC', 'field4' => 'ASC']],
            ['field2 bar, field3 DESC, field4', ['field2 bar' => 'ASC', 'field3' => 'DESC', 'field4' => 'ASC']],
            ['field2 some expression ASC, some expression desc, some other expression', ['field2 some expression' => 'ASC', 'some expression' => 'DESC', 'some other expression' => 'ASC']],
        ];
    }

    private function givenASortTypeWith($secondarySort)
    {
        $this->sortString = new SortString($secondarySort);
    }

    private function whenICallGetAsArray()
    {
        $this->sortArray = $this->sortString->getAsArray();
    }

    private function thenIShouldHave($expectedArray)
    {
        $this->assertEquals($expectedArray, $this->sortArray);
    }
}
