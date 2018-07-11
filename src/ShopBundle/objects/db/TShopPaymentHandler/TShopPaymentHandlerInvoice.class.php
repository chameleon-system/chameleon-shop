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
 * the paymenthandlers are used to handle the different payment methods. They ensure that the right
 * information is collected from the user, and that the payment is executed (as may be the case for online payment)
 * Note that the default handler has no functionality. it must be extended in order to do anything usefull.
/**/
class TShopPaymentHandlerInvoice extends TdbShopPaymentHandler
{
    const MSG_MANAGER_NAME = 'TShopPaymentHandlerInvoiceMSG';

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerInvoice';
    }

    /**
     * store user payment data in order.
     *
     * @param int $iOrderId
     */
    public function SaveUserPaymentDataToOrder($iOrderId)
    {
        $this->aPaymentUserData = array();
        parent::SaveUserPaymentDataToOrder($iOrderId);
    }
}
