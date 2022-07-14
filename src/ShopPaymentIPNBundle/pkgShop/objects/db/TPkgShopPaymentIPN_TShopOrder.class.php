<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPN_TShopOrder extends TPkgShopPaymentIPN_TShopOrderAutoParent
{
    /**
     * returns true if the status code has been sent as an IPN for the order.
     *
     * @param string $sStatusCode
     *
     * @return bool
     */
    final public function hasIPNStatusCode($sStatusCode)
    {
        $oStatusCode = TdbPkgShopPaymentIpnMessage::getMessageForOrder($this, $sStatusCode);
        $bHasStatusCode = (null !== $oStatusCode);

        return $bHasStatusCode;
    }
}
