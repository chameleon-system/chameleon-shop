<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces;

use TdbShopPaymentHandler;

interface ShopPaymentHandlerDataAccessInterface
{
    /**
     * Returns a payment handler instance that is NOT completely initialized. The instance is only loaded from the
     * database without configuration or user data being applied.
     *
     * @param string      $paymentHandlerId
     * @param string|null $languageId
     *
     * @return TdbShopPaymentHandler|null the payment handler or null if no handler with the given ID could be loaded
     */
    public function getBarePaymentHandler($paymentHandlerId, $languageId = null);
}
