<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPN_TPkgShopPaymentHandlerGroup extends TPkgShopPaymentIPN_TPkgShopPaymentHandlerGroupAutoParent
{
    /**
     * return true if the ip may send IPN to this payment type.
     *
     * @param string $sIP
     *
     * @return bool
     */
    public function isValidIP($sIP)
    {
        $bAllowed = false;
        $sIPSource = trim($this->fieldIpnAllowedIps);
        if ('' === $sIPSource) {
            return true;
        }

        $oNetworkHelper = new TPkgCoreUtility_Network();
        $aIPList = explode("\n", $sIPSource);
        foreach ($aIPList as $sAllowedIP) {
            $sRangeType = $oNetworkHelper->getRangeType($sAllowedIP);
            switch ($sRangeType) {
                case TPkgCoreUtility_Network::IP_RANGE_TYPE_NONE:
                    $bAllowed = ($sIP === $sAllowedIP);
                    break;
                case TPkgCoreUtility_Network::IP_RANGE_TYPE_RANGE:
                case TPkgCoreUtility_Network::IP_RANGE_TYPE_WILDCARD:
                case TPkgCoreUtility_Network::IP_RANGE_TYPE_CIDR:
                    $bAllowed = $oNetworkHelper->ipIsInRange($sIP, $sAllowedIP);
                    break;
            }

            if (true === $bAllowed) {
                break;
            }
        }

        return $bAllowed;
    }

    /**
     * process the IPN request - the request object contains all details (payment handler, group, order etc)
     * the call should return true if processing should continue, false if it is to stop. On Error it should throw an error
     * extending AbstractPkgShopPaymentIPNHandlerException.
     *
     * @param TPkgShopPaymentIPNRequest $oRequest
     *
     * @trows AbstractPkgShopPaymentIPNHandlerException
     *
     * @return void
     */
    final public function handleIPN(TPkgShopPaymentIPNRequest $oRequest)
    {
        $aHandlerChain = $this->getIPNHandlerChain();
        $this->handleIPNHook($oRequest);
        foreach ($aHandlerChain as $sHandler) {
            try {
                /** @var $oHandler IPkgShopPaymentIPNHandler */
                $oHandler = new $sHandler();
                if (false === $oHandler->handleIPN($oRequest)) {
                    $this->handleTransactionHook($oHandler, $oRequest);
                    break;
                } else {
                    $this->handleTransactionHook($oHandler, $oRequest);
                }
            } catch (TPkgCmsException $e) {
                // do not fatal on exceptions - we want to process all IPN
                TTools::WriteLogEntry(
                    'the handler '.$sHandler.' threw an exception '.get_class(
                        $e
                    ).'. Continuing to the next IPN handler anyway.',
                    1,
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    /**
     * extend this method if you want to do things for incoming ipn.
     *
     * @param TPkgShopPaymentIPNRequest $oRequest
     *
     * @return void
     */
    protected function handleIPNHook(TPkgShopPaymentIPNRequest $oRequest)
    {
    }

    /**
     * trigger transaction for the order based on the IPN.
     *
     * @param IPkgShopPaymentIPNHandler $oHandler
     * @param TPkgShopPaymentIPNRequest $oRequest
     *
     * @return void
     */
    protected function handleTransactionHook(IPkgShopPaymentIPNHandler $oHandler, TPkgShopPaymentIPNRequest $oRequest)
    {
        $oOrder = $oRequest->getOrder();
        if (null === $oOrder) {
            return;
        }

        $oTransactionDetails = $oHandler->getIPNTransactionDetails($oRequest);
        if (null === $oTransactionDetails) {
            return;
        }

        $oTransactionManager = new TPkgShopPaymentTransactionManager($oOrder);

        $sStatus = '';
        $oStatus = $oRequest->getIpnStatus();
        if (null !== $oStatus) {
            $sStatus = $oStatus->fieldCode;
        }

        $oTransaction = null;
        // try to update the corresponding transaction first
        if (null !== $oTransactionDetails->getSequenceNumber()) {
            $oTransaction = $oTransactionManager->confirmTransaction(
                $oTransactionDetails->getSequenceNumber(),
                $oTransactionDetails->getTransactionTimestamp()
            );
        }

        if (null === $oTransaction) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order_payment_ipn')->warning(
                "IPN had transaction data but no matching transaction was found. order-id: {$oOrder->id}, ordernumber: {$oOrder->fieldOrdernumber}",
                array('request' => $oRequest->getRequestPayload())
            );
        }
    }

    /**
     * return an array with names of payment handler (classes that implement IPkgShopPaymentIPNHandler).
     *
     * @return array
     */
    protected function getIPNHandlerChain()
    {
        return array();
    }

    /**
     * returns true if the data is valid - throws an exception extending the PkgShopPaymentIPNException_RequestError exception.
     *
     * @param TPkgShopPaymentIPNRequest $oRequest
     *
     * @return bool
     */
    public function validateIPNRequestData(TPkgShopPaymentIPNRequest $oRequest)
    {
        return true;
    }

    /**
     * overwrite this if you need to grab the order from the request data instead of getting it from the URL
     * is only called if getting the order from the URL fails.
     *
     * @param array|null $aRequestData
     *
     * @return TdbShopOrder|null
     */
    public function getOrderFromRequestData($aRequestData)
    {
        return null;
    }

    /**
     * you can use this method to perform any transformations on the data. notice that these modifications are not saved
     * in the IPN message.
     *
     * @param array $aRequestData
     *
     * @return array
     */
    public function processRawRequestData($aRequestData)
    {
        return $aRequestData;
    }

    /**
     * @param TPkgShopPaymentIPNRequest $oRequest
     *
     * @return TdbPkgShopPaymentIpnStatus|null
     */
    public function getIPNStatus(TPkgShopPaymentIPNRequest $oRequest)
    {
        return null;
    }

    /**
     * @param string|array $sData - either the id of the object to load, or the row with which the instance should be initialized
     * @param string $sLanguage - init with the language passed
     *
     * @return TdbShopPaymentHandlerGroup
     */
    public static function GetNewInstance($sData = null, $sLanguage = null): TdbShopPaymentHandlerGroup
    {
        $oInstance = parent::GetNewInstance($sData, $sLanguage);
        if (false !== $oInstance->sqlData) {
            $sClass = trim($oInstance->fieldClassname);
            if ('' !== $sClass) {
                /** @var TdbShopPaymentHandlerGroup $oNewClass */
                $oNewClass = new $sClass();
                if (null !== $sLanguage) {
                    $oNewClass->SetLanguage($sLanguage);
                }
                $oNewClass->LoadFromRow($oInstance->sqlData);
                $oInstance = $oNewClass;
            }
        }

        return $oInstance;
    }
}
