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
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $oUser = TdbDataExtranetUser::GetInstance();
        $oVisitor->SetMappedValue('sDetailLinkCaption', 'Details');
        if (null === $oUser->id) {
            return;
        }
        $oOrderList = TdbShopOrderList::GetListForDataExtranetUserId($oUser->id);
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
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function getOrderList(TdbShopOrderList $oOrderList, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $aOrderList = [];
        while ($oOrder = $oOrderList->Next()) {
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
     * @return TdbShopOrder|null
     */
    protected function getActiveOrder(TdbShopOrderList $oOrderList)
    {
        $sActiveOrderParameter = $this->GetActiveOrderRequest();
        if (false !== $sActiveOrderParameter) {
            $oOrderList->AddFilterString("`shop_order`.`ordernumber` = '".$sActiveOrderParameter."'");
            $oOrderList->GoToStart();

            if (0 < $oOrderList->Length()) {
                /*
                 * @psalm-suppress FalsableReturnStatement - We checked the length of the list here.
                 */
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
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    private function getOrder(TdbShopOrder $oOrder, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $aOrder = [];

        $oLocal = TCMSLocal::GetActive();

        $aOrder['sOrderDate'] = $oLocal->FormatDate($oOrder->fieldDatecreated, TCMSLocal::DATEFORMAT_SHOW_DATE);
        $aOrder['sOrdernumber'] = $oOrder->fieldOrdernumber;
        $aOrder['sSumGrandTotal'] = $oOrder->fieldValueTotalFormated;
        $aOrder['sShippingAddress'] = $this->getShippingAddress($oOrder, $bCachingEnabled, $oCacheTriggerManager);
        $aOrder['sDetailLink'] = $this->getDetailLink($oOrder);
        $orderCurrency = $oOrder->GetFieldPkgShopCurrency();
        if (null !== $orderCurrency) {
            $aOrder['currencyIso'] = $orderCurrency->fieldIso4217;
            $aOrder['currencySymbol'] = $orderCurrency->fieldSymbol;
        }
        $aOrder['bActive'] = false;

        return $aOrder;
    }

    /**
     * @param bool $cachingEnabled
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
        if (property_exists($order, 'fieldAdrBillingCompany')) {
            $address .= $this->getValueOrDefault($order->fieldAdrBillingCompany, ', ');
        }
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
     * @param bool $cachingEnabled
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
        if (property_exists($order, 'fieldAdrShippingCompany')) {
            $address .= $this->getValueOrDefault($order->fieldAdrShippingCompany, ' ');
        }
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
     * @return string
     */
    protected function getDetailLink(TdbShopOrder $oOrder)
    {
        $aParameters = [
            $this->getActiveOrderParameter() => $oOrder->fieldOrdernumber,
        ];

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
