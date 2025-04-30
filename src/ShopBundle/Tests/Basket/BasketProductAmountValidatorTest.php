<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Tests\Basket;

use ChameleonSystem\ShopBundle\Basket\BasketProductAmountValidator;
use PHPUnit\Framework\TestCase;

class BasketProductAmountValidatorTest extends TestCase
{
    /**
     * @var BasketProductAmountValidator
     */
    private $basketProductAmountValidator;
    /**
     * @var bool
     */
    private $actualResult;

    /**
     * @dataProvider getIsAmountValidData
     *
     * @param string|int|float $requestedAmount
     * @param bool $expectedResult
     */
    public function testIsAmountValid($requestedAmount, $expectedResult)
    {
        $this->givenABasketProductAmountValidator();
        $this->whenICallIsAmountValid($requestedAmount);
        $this->thenTheExpectedValidityShouldBeReturned($expectedResult);
    }

    private function givenABasketProductAmountValidator()
    {
        $this->basketProductAmountValidator = new BasketProductAmountValidator();
    }

    /**
     * @param string|int|float $requestedAmount
     */
    private function whenICallIsAmountValid($requestedAmount)
    {
        $this->actualResult = $this->basketProductAmountValidator->isAmountValid($this->getMockBuilder('\TdbShopArticle')->disableAutoload()->getMock(), $requestedAmount);
    }

    /**
     * @param bool $expectedResult
     */
    private function thenTheExpectedValidityShouldBeReturned($expectedResult)
    {
        static::assertEquals($expectedResult, $this->actualResult);
    }

    /**
     * @return array
     */
    public function getIsAmountValidData()
    {
        return [
            [
                0,
                true,
            ],
            [
                1,
                true,
            ],
            [
                2,
                true,
            ],
            [
                -7,
                true,
            ],
            [
                '0',
                true,
            ],
            [
                '2',
                true,
            ],
            [
                '-23',
                true,
            ],
            [
                3.14,
                false,
            ],
            [
                2.00000000001,
                false,
            ],
            [
                '3.14',
                false,
            ],
            [
                'a1',
                false,
            ],
            [
                '1a',
                false,
            ],
        ];
    }
}
