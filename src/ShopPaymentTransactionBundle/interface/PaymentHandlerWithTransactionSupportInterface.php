<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace esono\pkgshoppaymenttransaction;

/**
 * payment handler that implement this interface will route payment transactions through an instance of PaymentTransactionHandlerInterface.
 *
 * Interface PaymentHandlerWithTransactionSupportInterface
 */
interface PaymentHandlerWithTransactionSupportInterface
{
    /**
     * @param string $portalId
     *
     * @return PaymentTransactionHandlerInterface
     */
    public function paymentTransactionHandlerFactory($portalId);

    /**
     * return true if capture on shipment is active.
     *
     * @return bool
     */
    public function isCaptureOnShipment();
}
