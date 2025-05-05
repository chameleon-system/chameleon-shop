<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPayment_TShopPaymentHandlerGroup extends TdbShopPaymentHandlerGroup
{
    /**
     * @return TdbPkgShopPaymentIpnStatus|null
     */
    public function getIPNStatus(TPkgShopPaymentIPNRequest $oRequest)
    {
        $aPayload = $oRequest->getRequestPayload();
        $sStatusCode = null;
        if (false === isset($aPayload['ret_status'])) {
            return null;
        }

        $sCode = trim($aPayload['ret_status']);
        $oStatus = TdbPkgShopPaymentIpnStatus::GetNewInstance();
        if (false === $oStatus->LoadFromFields(['code' => $sCode, 'shop_payment_handler_group_id' => $this->id])) {
            return null;
        }

        return $oStatus;
    }

    /**
     * return an array with names of payment handler (classes that implement IPkgShopPaymentIPNHandler).
     *
     * @return array
     */
    protected function getIPNHandlerChain()
    {
        return [
            'TPkgShopPaymentIPayment_TPkgShopPaymentIPNHandler_BaseResponse', // send response to payone
        ];
    }
}
