<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface IPkgShopPaymentIPNHandler
{
    /**
     * process the IPN request - the request object contains all details (payment handler, group, order etc)
     * the call should return true if processing should continue, false if it is to stop. On Error it should throw an error
     * extending AbstractPkgShopPaymentIPNHandlerException.
     *
     * @param TPkgShopPaymentIPNRequest $oRequest
     * @trows AbstractPkgShopPaymentIPNHandlerException
     *
     * @return bool
     */
    public function handleIPN(TPkgShopPaymentIPNRequest $oRequest);

    /**
     * return an instance of TPkgShopPaymentIPN_TransactionDetails if your IPN should trigger a transaction for the order
     * (ie payment or refunds etc). if you return null, then no transaction will be triggered.
     *
     * @param TPkgShopPaymentIPNRequest $oRequest
     *
     * @return TPkgShopPaymentIPN_TransactionDetails|null
     */
    public function getIPNTransactionDetails(TPkgShopPaymentIPNRequest $oRequest);
}
