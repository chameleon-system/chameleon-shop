<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MTShopOrderHistory extends MTPkgViewRendererAbstractModuleMapper
{
    /**
     * @deprecated since 6.2.0 - do not use this constant outside of MTShopOrderHistory.
     */
    const ADDRESS_TYPE_BILLING = 1;
    /**
     * @deprecated since 6.2.0 - do not use this constant outside of MTShopOrderHistory.
     */
    const ADDRESS_TYPE_SHIPPING = 2;

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $oUser = TdbDataExtranetUser::GetInstance();
        $oVisitor->SetMappedValue('sDetailLinkCaption', 'Details');
        if (null === $oUser->id) {
            return;
        }
        $oOrderList = &TdbShopOrderList::GetListForDataExtranetUserId($oUser->id);
        // show only orders that were not canceled and have been completed
        $oOrderList->AddFilterString(
            "`shop_order`.`canceled` = '0' AND `shop_order`.`system_order_save_completed` = '1'"
        );

        $oVisitor->SetMappedValue('aOrderList', $this->getOrderList($oOrderList, $bCachingEnabled, $oCacheTriggerManager));
        $oOrder = $this->getActiveOrder($oOrderList);
        if (null !== $oOrder) {
            $oVisitor->SetMappedValue('oObject', $oOrder);
        }
    }

    /**
     * get the value list for the whole order list.
     *
     * @param TdbShopOrderList              $oOrderList
     * @param bool                          $bCachingEnabled
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     *
     * @return array
     */
    protected function getOrderList(TdbShopOrderList $oOrderList, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $aOrderList = array();
        while ($oOrder = &$oOrderList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oOrder->table, $oOrder->id);
            }
            $aOrder = $this->getOrder($oOrder, $bCachingEnabled, $oCacheTriggerManager);
            $aOrder['bActive'] = $this->showDetail($oOrder);
            $aOrderList[] = $aOrder;
        }

        return $aOrderList;
    }

    /**
     * @return string
     */
    protected function getActiveOrderParameter()
    {
        return 'sOrder';
    }

    /**
     * get the active order (if present).
     *
     * @param TdbShopOrderList $oOrderList
     *
     * @return null|TdbShopOrder
     */
    protected function getActiveOrder(TdbShopOrderList $oOrderList)
    {
        $sActiveOrderParameter = $this->GetActiveOrderRequest();
        if (false !== $sActiveOrderParameter) {
            $oOrderList->AddFilterString("`shop_order`.`ordernumber` = '".$sActiveOrderParameter."'");
            $oOrderList->GoToStart();

            if (0 < $oOrderList->Length()) {
                return $oOrderList->Current();
            }
        }

        return null;
    }

    /**
     * Retuirns the active order parameter from url parameter.
     *
     * @return bool
     */
    protected function GetActiveOrderRequest()
    {
        $sActiveOrderParameter = false;
        $oGlobal = TGlobal::instance();
        if ($oGlobal->UserDataExists($this->getActiveOrderParameter())) {
            $sActiveOrderParameter = $oGlobal->GetUserData($this->getActiveOrderParameter());
        }

        return $sActiveOrderParameter;
    }

    /**
     * returns true if order number of given order is equal to the parameter in the url.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return bool
     */
    protected function showDetail(TdbShopOrder $oOrder)
    {
        $oGlobal = TGlobal::instance();

        return true === $oGlobal->UserDataExists($this->getActiveOrderParameter()) && $oOrder->fieldOrdernumber == $oGlobal->GetUserData($this->getActiveOrderParameter());
    }

    /**
     * value map for one order.
     *
     * @param TdbShopOrder                  $oOrder
     * @param bool                          $bCachingEnabled
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     *
     * @return array
     */
    private function getOrder(TdbShopOrder $oOrder, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $aOrder = array();

        $oLocal = TCMSLocal::GetActive();

        $aOrder['sOrderDate'] = $oLocal->FormatDate($oOrder->fieldDatecreated, TCMSLocal::DATEFORMAT_SHOW_DATE);
        $aOrder['sOrdernumber'] = $oOrder->fieldOrdernumber;
        $aOrder['sSumGrandTotal'] = $oOrder->fieldValueTotalFormated;
        $aOrder['sShippingAddress'] = $this->getShippingAddress($oOrder, $bCachingEnabled, $oCacheTriggerManager);
        $aOrder['sDetailLink'] = $this->getDetailLink($oOrder);
        $aOrder['bActive'] = false;

        return $aOrder;
    }

    /**
     * returns one address as concatenated string
     * type can be defined by using the class constants.
     *
     * @param TdbShopOrder                  $order
     * @param int                           $addressType         use constants of the class to define the type to be fetched from
     * @param bool                          $cachingEnabled
     * @param IMapperCacheTriggerRestricted $cacheTriggerManager
     *
     * @return string
     *
     * @deprecated since 6.0.9 use getBillingAddress() or getShippingAddress()
     */
    protected function getAddress(TdbShopOrder $order, $addressType, $cachingEnabled, IMapperCacheTriggerRestricted $cacheTriggerManager)
    {
        $address = '';

        if (self::ADDRESS_TYPE_BILLING === $addressType) {
            $address = $this->getBillingAddress($order, $cachingEnabled, $cacheTriggerManager);
        } elseif (self::ADDRESS_TYPE_SHIPPING === $addressType) {
            $address = $this->getShippingAddress($order, $cachingEnabled, $cacheTriggerManager);
        }

        return $address;
    }

    /**
     * @param TdbShopOrder                  $order
     * @param bool                          $cachingEnabled
     * @param IMapperCacheTriggerRestricted $cacheTriggerManager
     *
     * @return string
     */
    protected function getBillingAddress(TdbShopOrder $order, $cachingEnabled, IMapperCacheTriggerRestricted $cacheTriggerManager)
    {
        $salutation = $order->GetFieldAdrBillingSalutation();
        if ($salutation && $cachingEnabled) {
            $cacheTriggerManager->addTrigger($salutation->table, $salutation->id);
        }
        $address = '';
        $address .= (null !== $salutation) ? $salutation->GetName().' ' : '';
        $address .= $this->getValueOrDefault($order->fieldAdrBillingFirstname, ' ');
        $address .= $this->getValueOrDefault($order->fieldAdrBillingLastname, ', ');
        $address .= $this->getValueOrDefault($order->fieldAdrBillingAdditionalInfo, ', ');
        $address .= $this->getValueOrDefault($order->fieldAdrBillingStreet, ' ');
        $address .= $this->getValueOrDefault($order->fieldAdrBillingStreetnr, ', ');
        $address .= $this->getValueOrDefault($order->fieldAdrBillingPostalcode, ' ');
        $address .= $this->getValueOrDefault($order->fieldAdrBillingCity);

        return $address;
    }

    /**
     * @param TdbShopOrder                  $order
     * @param bool                          $cachingEnabled
     * @param IMapperCacheTriggerRestricted $cacheTriggerManager
     *
     * @return string
     */
    protected function getShippingAddress(TdbShopOrder $order, $cachingEnabled, IMapperCacheTriggerRestricted $cacheTriggerManager)
    {
        if ($order->fieldAdrShippingUseBilling) {
            return $this->getBillingAddress($order, $cachingEnabled, $cacheTriggerManager);
        }
        $salutation = $order->GetFieldAdrShippingSalutation();
        if ($salutation && $cachingEnabled) {
            $cacheTriggerManager->addTrigger($salutation->table, $salutation->id);
        }
        $address = '';
        $address .= (null !== $salutation) ? $salutation->GetName().' ' : '';
        $address .= $this->getValueOrDefault($order->fieldAdrShippingFirstname, ' ');
        $address .= $this->getValueOrDefault($order->fieldAdrShippingLastname, ', ');
        $address .= $this->getValueOrDefault($order->fieldAdrShippingAdditionalInfo, ', ');
        $address .= $this->getValueOrDefault($order->fieldAdrShippingStreet, ' ');
        $address .= $this->getValueOrDefault($order->fieldAdrShippingStreetnr, ', ');
        $address .= $this->getValueOrDefault($order->fieldAdrShippingPostalcode, ' ');
        $address .= $this->getValueOrDefault($order->fieldAdrShippingCity);

        return $address;
    }

    /**
     * @param string $value
     * @param string $appendIfNotEmpty
     *
     * @return string
     */
    protected function getValueOrDefault($value, $appendIfNotEmpty = '')
    {
        return '' === $value ? '' : $value.$appendIfNotEmpty;
    }

    /**
     * get the detail link for the given order
     * takes active page as base url and adds only the parameter for the active order.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return string
     */
    protected function getDetailLink(TdbShopOrder $oOrder)
    {
        $aParameters = array(
            $this->getActiveOrderParameter() => $oOrder->fieldOrdernumber,
        );

        return '?'.TTools::GetArrayAsURL($aParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function _AllowCache()
    {
        return false;
    }
}
