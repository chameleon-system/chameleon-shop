<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_OrderArticleListSummary extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopOrder', null, true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oOrder TdbShopOrder */
        $oOrder = $oVisitor->GetSourceObject('oObject');
        if (null !== $oOrder) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oOrder->table, $oOrder->id);
            }
            $oVisitor->SetMappedValue('sSumGrandTotal', $oOrder->fieldValueTotalFormated);

            $oVisitor->SetMappedValue('sSumShipping', $oOrder->fieldShopShippingGroupPriceFormated);

            $oVisitor->SetMappedValue('sSumProducts', $oOrder->fieldValueArticleFormated);
            $oVisitor->SetMappedValue('sSumDiscounts', $oOrder->fieldValueDiscountsFormated);

            $oVisitor->SetMappedValue('aDiscountList', $this->getDiscounts($oOrder, $oCacheTriggerManager, $bCachingEnabled));
            $oVisitor->SetMappedValue('dSumDiscountVouchers', $oOrder->fieldValueVouchersNotSponsoredFormated);
            $oVisitor->SetMappedValue('aDiscountVoucherList', $this->getVoucherList($oOrder, false, $oCacheTriggerManager, $bCachingEnabled));

            $sValue = $oOrder->fieldValueArticle - $oOrder->fieldValueDiscounts - $oOrder->fieldValueVouchersNotSponsored;
            $oVisitor->SetMappedValue('sSumProductsAfterDiscountsAndDiscountVouchers', TCMSLocal::GetActive()->FormatNumber($sValue, 2));

            $oVisitor->SetMappedValue('sSumPaymentSurcharge', $oOrder->fieldShopPaymentMethodPriceFormated);
            $oVisitor->SetMappedValue('aVatList', $this->getVatList($oOrder, $oCacheTriggerManager, $bCachingEnabled));

            $oVisitor->SetMappedValue('sSumVatWithoutShipping', '');

            $sValue = $oOrder->fieldValueArticle - $oOrder->fieldValueDiscounts;
            $oVisitor->SetMappedValue('sSumTotalBasket', TCMSLocal::GetActive()->FormatNumber($sValue, 2));

            $oVisitor->SetMappedValue('sSumSponsoredVouchers', $oOrder->fieldValueVouchersFormated);
            $oVisitor->SetMappedValue('aSponsoredVoucherList', $this->getVoucherList($oOrder, true, $oCacheTriggerManager, $bCachingEnabled));
            $oVisitor->SetMappedValue('sBasketTotalWithoutSponsoredVouchers', TCMSLocal::GetActive()->FormatNumber($oOrder->fieldValueTotal + $oOrder->fieldValueVouchers, 2));
            $oVisitor->SetMappedValue('iNumberOfUniqueProducts', $oOrder->fieldCountUniqueArticles);
            $oVisitor->SetMappedValue('iNumberOfProducts', $oOrder->fieldCountArticles);
        }
    }

    /**
     * @param TdbShopOrder                   $oOrder
     * @param \IMapperCacheTriggerRestricted $oCacheTriggerManager
     * @param $bCachingEnabled
     *
     * @return array
     */
    protected function getDiscounts(TdbShopOrder $oOrder, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aDiscountList = array();

        $oDiscountList = &$oOrder->GetFieldShopOrderDiscountList();
        $oDiscountList->GoToStart();
        if (0 < $oDiscountList->Length()) {
            while ($oDiscount = &$oDiscountList->Next()) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oDiscount->table, $oDiscount->id);
                }
                $aDiscount = array('sName' => $oDiscount->fieldName, 'dValue' => $oDiscount->fieldTotal, 'sValue' => TCMSLocal::GetActive()->FormatNumber($oDiscount->fieldTotal, 2));
                $aDiscountList[] = $aDiscount;
            }
            $oDiscountList->GoToStart();
        }

        return $aDiscountList;
    }

    /**
     * @param TdbShopOrder                  $oOrder
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     * @param $bCachingEnabled
     *
     * @return array
     */
    protected function getVatList(TdbShopOrder $oOrder, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aVat = array();

        $oVatList = &$oOrder->GetFieldShopOrderVatList();
        $oVatList->GoToStart();
        if (0 < $oVatList->Length()) {
            while ($oVat = &$oVatList->Next()) {
                $dVatValue = $oVat->fieldValue;
                if (0 == $dVatValue) {
                    continue;
                }
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oVat->table, $oVat->id);
                }
                $aVatTmp = array('sName' => $oVat->fieldName, 'iPercent' => $oVat->fieldVatPercent, 'sPercent' => $oVat->fieldVatPercentFormated, 'dValue' => $dVatValue, 'sValue' => TCMSLocal::GetActive()->FormatNumber($dVatValue, 2));
                $aVat[] = $aVatTmp;
            }
            $oVatList->GoToStart();
        }

        return $aVat;
    }

    /**
     * @param TdbShopOrder                  $oOrder
     * @param bool                          $bSponsored
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     * @param $bCachingEnabled
     *
     * @return array
     */
    protected function getVoucherList(TdbShopOrder $oOrder, $bSponsored = false, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aVoucherList = array();

        // get vouchers
        $oVoucherUseList = &$oOrder->GetFieldShopVoucherUseList();
        $oVoucherUseList->GoToStart();
        if (0 < $oVoucherUseList->Length()) {
            while ($oVoucherUse = &$oVoucherUseList->Next()) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oVoucherUse->table, $oVoucherUse->id);
                }
                $oVoucher = $oVoucherUse->GetFieldShopVoucher();
                if (null !== $oVoucher) {
                    if ($bCachingEnabled) {
                        $oCacheTriggerManager->addTrigger($oVoucher->table, $oVoucher->id);
                    }
                    if (false === $oVoucher->IsSponsored() && false === $bSponsored) {
                        $aVoucher = $this->getDataFromVoucher($oVoucherUse, $oVoucher, $oCacheTriggerManager, $bCachingEnabled);
                        $aVoucherList[] = $aVoucher;
                    } elseif (true === $oVoucher->IsSponsored() && true === $bSponsored) {
                        $aVoucher = $this->getDataFromVoucher($oVoucherUse, $oVoucher, $oCacheTriggerManager, $bCachingEnabled);
                        $aVoucherList[] = $aVoucher;
                    }
                } else {
                    $aVoucher = $this->getDataFromVoucher($oVoucherUse, null, $oCacheTriggerManager);
                    $aVoucherList[] = $aVoucher;
                }
            }
            $oVoucherUseList->GoToStart();
        }

        return $aVoucherList;
    }

    /**
     * @param TdbShopVoucherUse             $oVoucherUse
     * @param TdbShopVoucher                $oVoucher
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     * @param $bCachingEnabled
     *
     * @return array
     */
    protected function getDataFromVoucher(TdbShopVoucherUse $oVoucherUse, TdbShopVoucher $oVoucher = null, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aVoucher = array('sCode' => '', 'sName' => '', 'dValue' => $oVoucherUse->fieldValueUsed, 'sValue' => TCMSLocal::GetActive()->FormatNumber($oVoucherUse->fieldValueUsed, 2), 'sRemoveFromBasketLink' => '#');

        if (null !== $oVoucher) {
            $aVoucher['sCode'] = $oVoucher->fieldCode;
            $oVoucherSeries = &$oVoucher->GetFieldShopVoucherSeries();
            if (null !== $oVoucherSeries) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oVoucherSeries->table, $oVoucherSeries->id);
                }
                $aVoucher['sName'] = $oVoucherSeries->fieldName;
            }
        }

        return $aVoucher;
    }
}
