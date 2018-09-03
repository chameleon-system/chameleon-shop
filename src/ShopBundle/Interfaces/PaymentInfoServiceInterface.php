<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Interfaces;

/**
 * PaymentInfoServiceInterface defines a service that provides information on payments.
 */
interface PaymentInfoServiceInterface
{
    /**
     * Indicates if the given payment method is currently active for the given portal (if not null).
     *
     * @param string             $paymentMethodInternalName The internal_name of the payment method
     * @param \TdbCmsPortal|null $portal                    if the portal is not null, the portal restrictions for the payment method will be applied.
     *                                                      Always provide a portal in the frontend
     *
     * @return bool
     */
    public function isPaymentMethodActive($paymentMethodInternalName, \TdbCmsPortal $portal = null);
}
