<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

class TPkgShopPaymentIpnMessage extends TPkgShopPaymentIpnMessageAutoParent
{
    /**
     * @return true|string - response string if exists, `true` otherwise
     */
    public function replayIPN()
    {
        $oRequest = new TPkgShopPaymentIPNRequest($this->fieldRequestUrl, $this->fieldPayload);
        $currentRequest = $this->getCurrentRequest();

        $aData = [
            'datecreated' => date('Y-m-d H:i:s'),
            'payload' => $this->fieldPayload,
            'success' => '0',
            'completed' => '0',
            'ip' => null === $currentRequest ? '' : $currentRequest->getClientIp(),
            'request_url' => $this->fieldRequestUrl,
            'cms_portal_id' => $this->fieldCmsPortalId,
            'shop_order_id',
        ];
        $oMessage = TdbPkgShopPaymentIpnMessage::GetNewInstance($aData);
        $oMessage->Save();

        try {
            $oRequest->parseRequest($oMessage);
            $oStatus = $oRequest->getIpnStatus();
            if (null === $oStatus) {
                throw new TPkgShopPaymentIPNException_InvalidStatus($oRequest, 'IPN Request has no status or the status passed is not understood by the payment handler');
            }

            $oMessage->SaveFieldsFast(['pkg_shop_payment_ipn_status_id' => $oStatus->id]);

            $oRequest->getPaymentHandlerGroup()->validateIPNRequestData($oRequest);

            $oRequest->getPaymentHandlerGroup()->handleIPN($oRequest);

            $oMessage->SaveFieldsFast(['success' => '1', 'completed' => '1']);
        } catch (TPkgShopPaymentIPNException_RequestError $e) {
            // something went wrong - add to message

            $aData = [
                'success' => '0',
                'completed' => '1',
                'errors' => (string) $e,
                'error_type' => $e->getErrorType(),
            ];
            $oMessage->SaveFieldsFast($aData);
            header($e->getResponseHeader());
        }

        /*
         * @psalm-suppress UndefinedVariable
         * @FIXME `$responseString` does not exist?
         */
        return (empty($responseString)) ? true : $responseString;
    }

    /**
     * returns the status object with a specific code for an order.
     *
     * @param string $sStatusCode
     *
     * @return TdbPkgShopPaymentIpnMessage|null
     */
    public static function getMessageForOrder(TdbShopOrder $oOrder, $sStatusCode)
    {
        $oStatus = null;
        $oPaymentHandler = $oOrder->GetPaymentHandler();
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
        $quotedOrderId = $connection->quote($oOrder->id);
        $quotedHandlerGroupId = $connection->quote($oPaymentHandler->fieldShopPaymentHandlerGroupId);
        $quotedStatusCode = $connection->quote($sStatusCode);

        $query = "SELECT `pkg_shop_payment_ipn_message`.*
                FROM `pkg_shop_payment_ipn_message`
          INNER JOIN `pkg_shop_payment_ipn_status`
                  ON `pkg_shop_payment_ipn_message`.`pkg_shop_payment_ipn_status_id` = `pkg_shop_payment_ipn_status`.`id`
               WHERE (
                        `pkg_shop_payment_ipn_message`.`shop_order_id` = {$quotedOrderId}
                        AND `pkg_shop_payment_ipn_message`.`shop_payment_handler_group_id` = {$quotedHandlerGroupId}
                        AND `pkg_shop_payment_ipn_message`.`success` = '1'
                        AND `pkg_shop_payment_ipn_message`.`completed` = '1'
                     )
                 AND `pkg_shop_payment_ipn_status`.`code` = {$quotedStatusCode}
           ";

        if ($aMessage = $connection->fetchAssociative($query)) {
            $oStatus = TdbPkgShopPaymentIpnMessage::GetNewInstance($aMessage);
        }

        return $oStatus;
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }
}
