<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface IPkgShopPaymentHandlerCustomConfirmOrderBlockInterface
 * if a payment handler implements this interface, then renderConfirmOrderBlock will be called to show a custom confirm text
 * in the order confirm step and the response will be processed via processConfirmOrderUserResponse.
 */
interface IPkgShopPaymentHandlerCustomConfirmOrderBlockInterface
{
    /**
     * @param TdbShopPaymentMethod $paymentMethod
     * @param TdbDataExtranetUser  $user
     *
     * @return string
     */
    public function renderConfirmOrderBlock(TdbShopPaymentMethod $paymentMethod, TdbDataExtranetUser $user);

    /**
     * @param TdbShopPaymentMethod $paymentMethod
     * @param TdbDataExtranetUser  $user
     * @param array                $userData
     *
     * @return bool
     */
    public function processConfirmOrderUserResponse(TdbShopPaymentMethod $paymentMethod, TdbDataExtranetUser $user, $userData);
}
