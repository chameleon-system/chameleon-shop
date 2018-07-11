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
     * @param bool$expectedResult
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
        return array(
            array(
                0,
                true,
            ),
            array(
                1,
                true,
            ),
            array(
                2,
                true,
            ),
            array(
                -7,
                true,
            ),
            array(
                '0',
                true,
            ),
            array(
                '2',
                true,
            ),
            array(
                '-23',
                true,
            ),
            array(
                3.14,
                false,
            ),
            array(
                2.00000000001,
                false,
            ),
            array(
                '3.14',
                false,
            ),
            array(
                'a1',
                false,
            ),
            array(
                '1a',
                false,
            ),
        );
    }
}
