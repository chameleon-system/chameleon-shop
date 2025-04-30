<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Tests\Payment\PaymentHandler;

class ShopPaymentHandlerMock
{
    /**
     * @var array
     */
    private $paymentUserData;

    /**
     * @return array
     */
    public function getUserPaymentDataWithoutLoading()
    {
        return $this->paymentUserData;
    }

    public function SetPaymentUserData(array $paymentUserData)
    {
        $this->paymentUserData = $paymentUserData;
    }
}
