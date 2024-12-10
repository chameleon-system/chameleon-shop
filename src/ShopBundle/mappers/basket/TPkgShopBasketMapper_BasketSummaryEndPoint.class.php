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
 * collects all sum-values for the basket (tax, vouchers, discounts, shipping, etc)
 * note: always use the class WITHOUT the EndPoint (ie. use TPkgShopBasketMapper_BasketSummary).
/**/
class TPkgShopBasketMapper_BasketSummaryEndPoint extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oBasket', 'TShopBasket', TShopBasket::GetInstance());
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oBasket TShopBasket */
        $oBasket = $oVisitor->GetSourceObject('oBasket');

        $aData = array(
            'dSumProducts' => $oBasket->dCostArticlesTotal,
            'dSumDiscounts' => -1 * $oBasket->dCostDiscounts,
            'aDiscountList' => array(),
            'dSumDiscountVouchers' => -1 * $oBasket->dCostNoneSponsoredVouchers,
            'aDiscountVoucherList' => array(),
            'dSumProductsAfterDiscountsAndDiscountVouchers' => $oBasket->dCostArticlesTotalAfterDiscounts, // after discounts and sponsored vouchers

            'dSumShipping' => $oBasket->dCostShipping,
            'dSumPaymentSurcharge' => $oBasket->dCostPaymentMethodSurcharge,

            'dSumVat' => $oBasket->dCostVAT,
            'aVatList' => array(),
            'dSumVatWithoutShipping' => $oBasket->dCostVATWithoutShipping,

            'dSumSponsoredVouchers' => -1 * $oBasket->dCostVouchers,
            'aSponsoredVoucherList' => array(),
            'dSumGrandTotal' => $oBasket->dCostTotal,
            'dBasketTotalWithoutSponsoredVouchers' => $oBasket->dCostTotal + $oBasket->dCostVouchers,

            'iNumberOfUniqueProducts' => $oBasket->iTotalNumberOfUniqueArticles,
            'iNumberOfProducts' => $oBasket->dTotalNumberOfArticles,
            'shippingCountryKnown' => false,
            'oDefaultCountry' => $this->getShop()->GetFieldDataCountry(),
        );

        $user = $this->getActiveUser();
        $shippingAddress = $user->GetShippingAddress();
        if (null !== $shippingAddress->GetFieldDataCountry()) {
            $aData['shippingCountryKnown'] = true;
        }

        // add discounts
        $oDiscountList = $oBasket->GetDiscountList();
        if (null !== $oDiscountList) {
            $oDiscountList->GoToStart();
            while ($oDiscount = $oDiscountList->Next()) {
                $aDiscount = array(
                    'sName' => $oDiscount->fieldName,
                    'dValue' => -1 * $oDiscount->GetValue(),
                );
                $aData['aDiscountList'][] = $aDiscount;
            }
            $oDiscountList->GoToStart();
        }

        // get vouchers
        $oVoucherList = $oBasket->GetVoucherList();
        if (!is_null($oVoucherList)) {
            $oVoucherList->GoToStart();
            while ($oVoucher = $oVoucherList->Next()) {
                if (false === $oVoucher->IsSponsored()) {
                    $aData['aDiscountVoucherList'][] = $this->getDataFromVoucher($oVoucher);
                } else {
                    $aData['aSponsoredVoucherList'][] = $this->getDataFromVoucher($oVoucher);
                }
            }
            $oVoucherList->GoToStart();
        }

        // get vat
        $oVatList = $oBasket->GetActiveVATList();
        if (null !== $oVatList) {
            $oVatList->GoToStart();
            while ($oVat = $oVatList->Next()) {
                $dVatValue = $oVat->GetVatValue();
                if (0 == $dVatValue) {
                    continue;
                }
                $aVat = array(
                    'sName' => $oVat->fieldName,
                    'iPercent' => $oVat->fieldVatPercent,
                    'dValue' => $dVatValue,
                );
                $aData['aVatList'][] = $aVat;
            }
            $oVatList->GoToStart();
        }

        $this->addFormattedValues($aData);

        foreach (array_keys($aData) as $sKey) {
            $oVisitor->SetMappedValue($sKey, $aData[$sKey]);
        }
    }

    /**
     * @return TdbDataExtranetUser|null
     */
    protected function getActiveUser()
    {
        /** @var \ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface $userProvider */
        $userProvider = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');

        return $userProvider->getActiveUser();
    }

    /**
     * @return TdbShop
     */
    protected function getShop()
    {
        /** @var \ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface $shopProvider */
        $shopProvider = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');

        return $shopProvider->getActiveShop();
    }

    /**
     * @param array $aData
     *
     * @return void
     */
    protected function addFormattedValues(&$aData)
    {
        foreach (array_keys($aData) as $sField) {
            if (is_array($aData[$sField])) {
                $this->addFormattedValues($aData[$sField]);
            } elseif ('d' == substr($sField, 0, 1)) {
                $aData['s'.substr($sField, 1)] = TCMSLocal::GetActive()->FormatNumber($aData[$sField], 2);
            } elseif ('i' == substr($sField, 0, 1)) {
                // keep als many decimals as the number has
                $iNumberOfDigits = 0;
                $sTmvVal = (string) $aData[$sField];
                if (false !== stripos($sTmvVal, '.')) {
                    $sTmvVal = substr($sTmvVal, stripos($sTmvVal, '.') + 1);
                    while (0 == substr($sTmvVal, -1) && strlen($sTmvVal) > 0) {
                        $sTmvVal = substr($sTmvVal, 0, -1);
                    }

                    $iNumberOfDigits = strlen($sTmvVal);
                }
                $aData['s'.substr($sField, 1)] = TCMSLocal::GetActive()->FormatNumber($aData[$sField], $iNumberOfDigits);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDataFromVoucher(TdbShopVoucher $oVoucher)
    {
        $oVoucherSeries = $oVoucher->GetFieldShopVoucherSeries();
        $voucherValue = -1 * $oVoucher->GetValue();
        $aVoucher = array(
            'sCode' => $oVoucher->fieldCode,
            'sName' => $oVoucherSeries->fieldName,
            'dValue' => $voucherValue,
            'dValueInOrderCurrency' => $voucherValue,
            'sRemoveFromBasketLink' => $oVoucher->GetRemoveFromBasketLink(),
        );

        return $aVoucher;
    }
}
