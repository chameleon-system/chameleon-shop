<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPNRequest
{
    const URL_IPN_IDENTIFIER = '_api_pkgshopipn_';

    /**
     * @var string
     */
    private $requestURL = null;
    /**
     * @var array
     */
    private $requestPayload = null;
    /**
     * @var TdbShopPaymentHandlerGroup|null
     */
    private $paymentHandlerGroup = null;
    /**
     * @var TdbShopOrder|null
     */
    private $order = null;

    /**
     * @var TdbShopPaymentHandler|IPkgShopPaymentIPNHandler
     */
    private $paymentHandler = null;

    /**
     * @var TdbPkgShopPaymentIpnStatus
     */
    private $oStatus = null;

    /**
     * @param string $sURL
     * @param array $aRequestPayload
     */
    public function __construct($sURL, $aRequestPayload)
    {
        $this->requestURL = $sURL;
        $this->requestPayload = $aRequestPayload;
    }

    /**
     * @param array<string, mixed> $aRequestPayload
     * @param null|string $sSourceCharset
     *
     * @return array<string, mixed>
     */
    private function convertCharset($aRequestPayload, $sSourceCharset)
    {
        foreach ($aRequestPayload as $sKey => $data) {
            if (true === is_array($data)) {
                $aRequestPayload[$sKey] = $this->convertCharset($data, $sSourceCharset);
            } else {
                $aRequestPayload[$sKey] = iconv($sSourceCharset, 'UTF-8', $data);
            }
        }

        return $aRequestPayload;
    }

    /**
     * @return bool
     */
    public function isIPNRequest()
    {
        return false !== stripos($this->getRequestURL(), self::URL_IPN_IDENTIFIER);
    }

    /**
     * @return TdbPkgShopPaymentIpnStatus|null
     */
    public function getIpnStatus()
    {
        if (null === $this->oStatus) {
            $this->oStatus = $this->getPaymentHandlerGroup()->getIPNStatus($this);
        }

        return $this->oStatus;
    }

    /**
     * @param TdbPkgShopPaymentIpnMessage $oMessage
     *
     * @throws TPkgShopPaymentIPNException_InvalidPaymentHandlerGroupInRequest
     * @throws TPkgShopPaymentIPNException_OrderNotFound
     * @throws TPkgShopPaymentIPNException_RequestError
     *
     * @return void
     */
    public function parseRequest(TdbPkgShopPaymentIpnMessage $oMessage)
    {
        if (false === $this->isIPNRequest()) {
            throw new TPkgShopPaymentIPNException_RequestError($this, "handleIPN called with an invalid URL (URL: {$this->getRequestURL(
            )})", 0);
        }

        $aParts = explode(self::URL_IPN_IDENTIFIER, $this->requestURL);
        if (2 !== count($aParts)) {
            throw new TPkgShopPaymentIPNException_RequestError($this, "handleIPN called with an invalid URL (URL: {$this->getRequestURL(
            )})", 0);
        }

        $sData = substr(
            $this->requestURL,
            stripos($this->requestURL, self::URL_IPN_IDENTIFIER) + strlen(self::URL_IPN_IDENTIFIER)
        );

        if ('' === empty($sData)) {
            throw new TPkgShopPaymentIPNException_RequestError($this, "handleIPN called with an invalid URL (URL: {$this->getRequestURL(
            )})", 0);
        }

        $sPaymentGroupIdentifier = null;
        $sOrderCmsIdent = null;
        if (false !== strpos($sData, '__')) {
            $sPaymentGroupIdentifier = substr($sData, 0, strpos($sData, '__'));
            $sOrderCmsIdent = substr($sData, strpos($sData, '__') + 2);
        } else {
            $sPaymentGroupIdentifier = $sData;
        }

        if (null !== $sPaymentGroupIdentifier) {
            $this->paymentHandlerGroup = null;
            $oPaymentHandlerGroup = TdbShopPaymentHandlerGroup::GetNewInstance();
            if (false === $oPaymentHandlerGroup->LoadFromField('ipn_group_identifier', $sPaymentGroupIdentifier)) {
                if (false === $oPaymentHandlerGroup->LoadFromField('system_name', $sPaymentGroupIdentifier)) {
                    $oPaymentHandlerGroup = null;
                }
            }

            // cast to correct object type
            if (null !== $oPaymentHandlerGroup) {
                $this->paymentHandlerGroup = TdbShopPaymentHandlerGroup::GetNewInstance($oPaymentHandlerGroup->sqlData);
            }
        }
        if (null === $this->paymentHandlerGroup) {
            throw new TPkgShopPaymentIPNException_InvalidPaymentHandlerGroupInRequest($this, "no payment handler group found for URL (URL: {$this->getRequestURL(
            )})", 0);
        }

        // we have a payment group - we may need to transform the raw data using it (because the encoding may be non utf8 for exampel)
        $oMessage->SaveFieldsFast(array('shop_payment_handler_group_id' => $this->paymentHandlerGroup->id));

        // once we have the group we may need to convert the request data
        if ('UTF-8' !== strtoupper($this->getPaymentHandlerGroup()->fieldIpnPayloadCharacterCharset)) {
            $this->requestPayload = $this->convertCharset(
                $this->requestPayload,
                $this->getPaymentHandlerGroup()->fieldIpnPayloadCharacterCharset
            );
        }

        $this->requestPayload = $this->paymentHandlerGroup->processRawRequestData($this->requestPayload);

        $oMessage->SaveFieldsFast(
            array('payload' => serialize($this->requestPayload))
        ); // note: we need to serialize the data since savefieldsfast does NO data transformation before saving

        if (null !== $sOrderCmsIdent) {
            $oOrder = TdbShopOrder::GetNewInstance();
            if (true === $oOrder->LoadFromField('cmsident', $sOrderCmsIdent)) {
                $this->order = $oOrder;
            } else {
                throw new TPkgShopPaymentIPNException_OrderNotFound($sOrderCmsIdent, $this, "order [{$sOrderCmsIdent}] passed via URL not found (URL: {$this->getRequestURL(
                )})", 0);
            }
        } else {
            $this->order = $this->getPaymentHandlerGroup()->getOrderFromRequestData($this->getRequestPayload());
            if (null === $this->order) {
                throw new TPkgShopPaymentIPNException_OrderNotFound($sOrderCmsIdent, $this, "Order not found using payment group [{$this->getPaymentHandlerGroup(
                )->id} class: ".get_class($this->getPaymentHandlerGroup()).'] for request ['.print_r(
                    $this->getRequestPayload(),
                    true
                )."] (URL: {$this->getRequestURL()})", 0);
            }
        }

        $oMessage->SaveFieldsFast(array('shop_order_id' => $this->getOrder()->id));

        $oPaymentHandler = $this->getOrder()->GetPaymentHandler();

        if (null === $oPaymentHandler || false === ($oPaymentHandler instanceof IPkgShopPaymentIPNPaymentHandler)) {
            throw new TPkgShopPaymentIPNException_SystemError_PaymentHanlderDoesNotSupportIPN($this, "Payment handler in order ({$this->getOrder(
            )->id}) must be an instance of IPkgShopPaymentIPNPaymentHandler", 0);
        }

        $this->paymentHandler = $oPaymentHandler;

        // add trigger
        $this->addTrigger($oMessage);
    }

    /**
     * @param TdbPkgShopPaymentIpnMessage $oMessage
     *
     * @return void
     */
    private function addTrigger(TdbPkgShopPaymentIpnMessage $oMessage)
    {
        $oGroup = $this->getPaymentHandlerGroup();
        $oStatus = $this->getIpnStatus();
        if (null === $oStatus) {
            return;
        }

        $connection = $this->getDatabaseConnection();
        $quotedStatusId = $connection->quote($oStatus->id);
        $quotedGroupId = $connection->quote($oGroup->id);

        $query = "SELECT `pkg_shop_payment_ipn_trigger`.*
                FROM `pkg_shop_payment_ipn_trigger`
          INNER JOIN `pkg_shop_payment_ipn_trigger_pkg_shop_payment_ipn_status_mlt`
                  ON `pkg_shop_payment_ipn_trigger`.`id` = `pkg_shop_payment_ipn_trigger_pkg_shop_payment_ipn_status_mlt`.`source_id`
               WHERE `pkg_shop_payment_ipn_trigger_pkg_shop_payment_ipn_status_mlt`.`target_id` = {$quotedStatusId}
                 AND `pkg_shop_payment_ipn_trigger`.`shop_payment_handler_group_id` = {$quotedGroupId}
    ";
        $oTriggerList = TdbPkgShopPaymentIpnTriggerList::GetList($query);
        if (0 === $oTriggerList->Length()) {
            return;
        }

        while ($oTrigger = $oTriggerList->Next()) {
            $oTriggerAction = TdbPkgShopPaymentIpnMessageTrigger::GetNewInstance();
            $aData = [
                'pkg_shop_payment_ipn_trigger_id' => $oTrigger->id,
                'pkg_shop_payment_ipn_message_id' => $oMessage->id,
                'datecreated' => date('Y-m-d H:i:s'),
                'next_attempt' => date('Y-m-d H:i:s'),
            ];
            $oTriggerAction->SaveFieldsFast($aData);
        }
    }

    /**
     * @param null|string $sRequestIP
     *
     * @return bool
     */
    public function allowRequestFromIP($sRequestIP)
    {
        return $this->getPaymentHandlerGroup()->isValidIP($sRequestIP);
    }

    /**
     * @return string|null
     */
    public function getRequestURL()
    {
        return $this->requestURL;
    }

    /**
     * @return array|null
     */
    public function getRequestPayload()
    {
        return $this->requestPayload;
    }

    /**
     * @return TdbShopPaymentHandlerGroup|null
     */
    public function getPaymentHandlerGroup()
    {
        return $this->paymentHandlerGroup;
    }

    /**
     * @return TdbShopOrder|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return TdbShopPaymentHandler|IPkgShopPaymentIPNHandler|null
     */
    public function getPaymentHandler()
    {
        return $this->paymentHandler;
    }
}
