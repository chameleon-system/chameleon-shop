<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\PaymentHandler;

use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerDataAccessInterface;

class ShopPaymentHandlerDatabaseAccess implements ShopPaymentHandlerDataAccessInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBarePaymentHandler($paymentHandlerId, $languageId = null)
    {
        $paymentHandler = \TdbShopPaymentHandler::GetInstance($paymentHandlerId);
        if (null === $paymentHandler || false === $paymentHandler->sqlData) {
            return null;
        }
        if (null !== $languageId) {
            $paymentHandler->SetLanguage($languageId);
            $paymentHandler->LoadFromRow($paymentHandler->sqlData);
        }

        return $paymentHandler;
    }
}
