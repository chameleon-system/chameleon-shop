<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopBundle\Exception\ConfigurationException;
use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class TShopOrder extends TShopOrderAutoParent
{
    const VIEW_PATH = 'pkgShop/views/db/TShopOrder';
    const MAIL_CONFIRM_ORDER = 'shop-confirm-order';
    const MAIL_STATUS_UPDATE = 'shop-order-status-update';

    /**
     * we use the post insert hook to set the ordernumber for the order.
     */
    protected function PostInsertHook()
    {
        parent::PostInsertHook();
        // we need to add an order number to the order... since generation of this number may differ
        // from shop to shop, we have added the method to fetch a new order number to the shop class
        $oShop = TdbShop::GetInstance();
        $iOrderNumber = $oShop->GetNextFreeOrderNumber();
        $aData = $this->sqlData;
        $aData['ordernumber'] = $iOrderNumber;
        $this->LoadFromRow($aData);
        $this->Save();
    }

    /**
     * fill the order object with data from the basket
     * NOTE: this fills the base order only. None of the lists (article, vat, etc) will be added yet.
     *
     * @param TShopBasket $oBasket
     */
    public function LoadFromBasket(TShopBasket &$oBasket)
    {
        $oShop = TdbShop::GetInstance();
        $oPortal = $this->getPortalDomainService()->getActivePortal();
        $oUser = TdbDataExtranetUser::GetInstance();
        $oBillingAdr = $oUser->GetBillingAddress();

        $sAffiliateCode = $oShop->GetAffilateCode();
        if (false === $sAffiliateCode) {
            $sAffiliateCode = '';
        }

        $pkg_shop_affiliate_id = '';
        $oAffiliatePartnerProgram = TdbPkgShopAffiliate::GetActiveInstance();
        if ($oAffiliatePartnerProgram) {
            if (!empty($oAffiliatePartnerProgram->sCode)) {
                $pkg_shop_affiliate_id = $oAffiliatePartnerProgram->id;
            }
        }
        $request = $this->getCurrentRequest();
        $aOrderData = array(
            'shop_id' => $oShop->id,
            'cms_portal_id' => $oPortal->id,
            'ordernumber' => '',
            'order_ident' => $oBasket->sBasketIdentifier,
            'data_extranet_user_id' => $oUser->id,
            'user_email' => $oUser->GetUserEMail(),
            'customer_number' => $oUser->GetCustomerNumber(),
            'vat_id' => $oUser->fieldVatId,
            'adr_billing_company' => $oBillingAdr->fieldCompany,
            'adr_billing_salutation_id' => $oBillingAdr->fieldDataExtranetSalutationId,
            'adr_billing_firstname' => $oBillingAdr->fieldFirstname,
            'adr_billing_lastname' => $oBillingAdr->fieldLastname,
            'adr_billing_street' => $oBillingAdr->fieldStreet,
            'adr_billing_streetnr' => $oBillingAdr->fieldStreetnr,
            'adr_billing_city' => $oBillingAdr->fieldCity,
            'adr_billing_postalcode' => $oBillingAdr->fieldPostalcode,
            'adr_billing_country_id' => $oBillingAdr->fieldDataCountryId,
            'adr_billing_telefon' => $oBillingAdr->fieldTelefon,
            'adr_billing_fax' => $oBillingAdr->fieldFax,
            'adr_shipping_use_billing' => '1',
            'adr_shipping_salutation_id' => '',
            'adr_shipping_company' => '',
            'adr_shipping_firstname' => '',
            'adr_shipping_lastname' => '',
            'adr_shipping_street' => '',
            'adr_shipping_streetnr' => '',
            'adr_shipping_city' => '',
            'adr_shipping_postalcode' => '',
            'adr_shipping_country_id' => '',
            'adr_shipping_telefon' => '',
            'adr_shipping_fax' => '',
            'shop_shipping_group_price' => $oBasket->dCostShipping,
            'shop_payment_method_price' => $oBasket->dCostPaymentMethodSurcharge,
            'value_article' => $oBasket->dCostArticlesTotal,
            'value_total' => $oBasket->dCostTotal,
            'value_wrapping' => $oBasket->dCostWrapping,
            'value_wrapping_card' => $oBasket->dCostWrappingCards,
            'value_vouchers' => $oBasket->dCostVouchers,
            'value_vouchers_not_sponsored' => $oBasket->dCostNoneSponsoredVouchers,
            'value_discounts' => $oBasket->dCostDiscounts,
            'value_vat_total' => $oBasket->dCostVAT,
            'count_articles' => $oBasket->dTotalNumberOfArticles,
            'count_unique_articles' => $oBasket->iTotalNumberOfUniqueArticles,
            'totalweight' => $oBasket->dTotalWeight,
            'totalvolume' => $oBasket->dTotalVolume,
            'affiliate_code' => $sAffiliateCode,
            'pkg_shop_affiliate_id' => $pkg_shop_affiliate_id,
            'cms_language_id' => self::getLanguageService()->getActiveLanguageId(),
            'user_ip' => null === $request ? '' : $request->getClientIp(),
        );
        if (property_exists($oBillingAdr, 'fieldAddressAdditionalInfo')) {
            $aOrderData['adr_billing_additional_info'] = $oBillingAdr->fieldAddressAdditionalInfo;
        }

        $oShippingGroup = $oBasket->GetActiveShippingGroup();
        if ($oShippingGroup) {
            $oShippingVat = $oShippingGroup->GetVat();
            $aOrderData['shop_shipping_group_id'] = $oShippingGroup->id;
            $aOrderData['shop_shipping_group_name'] = $oShippingGroup->fieldName;
            if ($oShippingVat) {
                $aOrderData['shop_shipping_group_vat_percent'] = $oShippingVat->fieldVatPercent;
            }
        }
        $oPayment = $oBasket->GetActivePaymentMethod();
        if ($oPayment) {
            $aOrderData['shop_payment_method_id'] = $oPayment->id;
            $aOrderData['shop_payment_method_name'] = $oPayment->fieldName;
            $oPaymentVat = $oPayment->GetVat();
            if ($oPaymentVat) {
                $aOrderData['shop_payment_method_vat_percent'] = $oPaymentVat->fieldVatPercent;
            }
        }

        if (!$oUser->ShipToBillingAddress()) {
            $oShippingAdr = $oUser->GetShippingAddress();
            $aOrderData['adr_shipping_use_billing'] = '0';
            $aOrderData['adr_shipping_salutation_id'] = $oShippingAdr->fieldDataExtranetSalutationId;
            $aOrderData['adr_shipping_company'] = $oShippingAdr->fieldCompany;
            $aOrderData['adr_shipping_firstname'] = $oShippingAdr->fieldFirstname;
            $aOrderData['adr_shipping_lastname'] = $oShippingAdr->fieldLastname;
            $aOrderData['adr_shipping_street'] = $oShippingAdr->fieldStreet;
            $aOrderData['adr_shipping_streetnr'] = $oShippingAdr->fieldStreetnr;
            $aOrderData['adr_shipping_city'] = $oShippingAdr->fieldCity;
            $aOrderData['adr_shipping_postalcode'] = $oShippingAdr->fieldPostalcode;
            $aOrderData['adr_shipping_country_id'] = $oShippingAdr->fieldDataCountryId;
            $aOrderData['adr_shipping_telefon'] = $oShippingAdr->fieldTelefon;
            $aOrderData['adr_shipping_fax'] = $oShippingAdr->fieldFax;
            if (property_exists($oShippingAdr, 'fieldAddressAdditionalInfo')) {
                $aOrderData['adr_shipping_additional_info'] = $oShippingAdr->fieldAddressAdditionalInfo;
            }
        }
        $this->LoadFromBasketPostProcessData($oBasket, $aOrderData);
        $this->LoadFromRow($aOrderData);
    }

    /**
     * method can be used to modify the data saved to order before the save is executed.
     *
     * @param TShopBasket $oBasket
     * @param array       $aOrderData
     */
    protected function LoadFromBasketPostProcessData($oBasket, &$aOrderData)
    {
    }

    /**
     * method can be used to save any custom data to the order. The method is called
     * after all core data has been saved to the order (but before the order is marked as complete).
     *
     * @param TShopBasket $oBasket
     */
    public function SaveCustomDataFromBasket(TShopBasket $oBasket)
    {
    }

    /**
     * Saves the vouchers passed to the order to the shop_voucher_use table
     * Note: this will only work if the order has an id.
     * Also Note: existing voucher uses will NOT be removed.
     *
     * @param TShopBasketVoucherList $oBasketVoucherList
     */
    public function SaveVouchers(TShopBasketVoucherList $oBasketVoucherList)
    {
        if (!is_null($this->id) && !is_null($oBasketVoucherList) && $oBasketVoucherList->Length() > 0) {
            $oBasketVoucherList->GoToStart();
            while ($oVoucherItemUsed = $oBasketVoucherList->Next()) {
                $oVoucherItemUsed->CommitVoucherUseForCurrentUser($this->id);
            }
        }
    }

    /**
     * save basket articles to order. NOTE: the order object must have an id for
     * this method to work.
     *
     * @param TShopBasketArticleList $oBasketArticleList
     */
    public function SaveArticles(TShopBasketArticleList $oBasketArticleList)
    {
        if (!is_null($this->id)) {
            $oBasketArticleList->GoToStart();
            while ($oBasketItem = $oBasketArticleList->Next()) {
                $this->SaveArticle($oBasketItem);
            }
        }
    }

    /**
     * store one article with order.
     *
     * @param TShopBasketArticle $oBasketItem
     *
     * @return TdbShopOrderItem
     */
    protected function &SaveArticle(TShopBasketArticle $oBasketItem)
    {
        $oVat = $oBasketItem->GetVat();
        $oOrderItem = TdbShopOrderItem::GetNewInstance();
        /** @var $oOrderItem TdbShopOrderItem */
        $oManufacturer = TdbShopManufacturer::GetNewInstance();
        $oManufacturer->Load($oBasketItem->fieldShopManufacturerId);
        $aData = array(
            'shop_order_id' => $this->id,
            'basket_item_key' => $oBasketItem->sBasketItemKey,
            'shop_article_id' => $oBasketItem->id,
            'name' => $oBasketItem->fieldName,
            'name_variant_info' => $oBasketItem->fieldNameVariantInfo,
            'articlenumber' => $oBasketItem->fieldArticlenumber,
            'description_short' => $oBasketItem->fieldDescriptionShort,
            'description' => $oBasketItem->fieldDescription,
            'shop_manufacturer_id' => $oBasketItem->fieldShopManufacturerId,
            'shop_manufacturer_name' => $oManufacturer->GetName(),
            'price' => $oBasketItem->fieldPrice,
            'price_reference' => $oBasketItem->fieldPriceReference,
            'vat_percent' => $oVat->fieldVatPercent,
            'size_weight' => $oBasketItem->fieldSizeWeight,
            'size_width' => $oBasketItem->fieldSizeWidth,
            'size_height' => $oBasketItem->fieldSizeHeight,
            'size_length' => $oBasketItem->fieldSizeLength,
            'stock' => $oBasketItem->getAvailableStock(),
            'virtual_article' => $oBasketItem->sqlData['virtual_article'],
            'exclude_from_vouchers' => $oBasketItem->sqlData['exclude_from_vouchers'],
            'subtitle' => $oBasketItem->fieldSubtitle,
            'is_new' => $oBasketItem->sqlData['is_new'],
            'usp' => $oBasketItem->fieldUsp,
            'is_bundle' => $oBasketItem->sqlData['is_bundle'],
            'order_amount' => $oBasketItem->dAmount,
            'order_price' => $oBasketItem->dPrice,
            'order_price_total' => $oBasketItem->dPriceTotal,
            'order_price_after_discounts' => $oBasketItem->dPriceTotalAfterDiscount,
            'order_total_weight' => $oBasketItem->dTotalWeight,
            'order_total_volume' => $oBasketItem->dTotalVolume,
            'download' => $oBasketItem->fieldDownload,
            'price_discounted' => $oBasketItem->dPriceAfterDiscount,
            'exclude_from_discounts' => $oBasketItem->sqlData['exclude_from_discounts'],
            'quantity_in_units' => $oBasketItem->fieldQuantityInUnits,
            'shop_unit_of_measurement_id' => $oBasketItem->fieldShopUnitOfMeasurementId,
            'custom_data' => $oBasketItem->getCustomData(),
        );
        $this->PrepareArticleDataForSave($oBasketItem, $aData);
        $oOrderItem->LoadFromRow($aData);
        $oOrderItem->AllowEditByAll(true);
        $iInsertedOrderArticleId = $oOrderItem->Save();

        //Linking the downloads from BasketItem to OrderItem
        $oDownloadFilesList = $oBasketItem->GetDownloads('download');
        while ($oDownloadFile = $oDownloadFilesList->Next()) {
            $sQuery = "INSERT INTO `shop_order_item_download_cms_document_mlt`
                           SET `source_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($iInsertedOrderArticleId)."',
                               `target_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oDownloadFile->id)."'";
            MySqlLegacySupport::getInstance()->query($sQuery);
        }

        if ($oBasketItem->fieldIsBundle) {
            // also save bundle articles
            $oBundleArticles = $oBasketItem->GetFieldShopBundleArticleList();
            while ($oBundleArticle = $oBundleArticles->Next()) {
                $this->SaveBundleArticle($oBasketItem, $oOrderItem, $oBundleArticle);
            }
        }

        return $oOrderItem;
    }

    /**
     * use this method to make changes to the order item data before it is saved to the database.
     *
     * @param TShopBasketArticle $oBasketItem
     * @param array              $aOrderItemData
     */
    protected function PrepareArticleDataForSave(&$oBasketItem, &$aOrderItemData)
    {
    }

    /**
     * save an article (pointed to by $oBundleArticle) belonging to a bundle ($oBasketItem) within the order.
     *
     * @param TShopBasketArticle   $oBasketItem    - the original basket item
     * @param TdbShopOrderItem     $oOrderItem     - the generated order item (ie the bundle owner)
     * @param TdbShopBundleArticle $oBundleArticle - the bundle connection between the owning item and the owned item
     */
    protected function SaveBundleArticle(&$oBasketItem, &$oOrderItem, &$oBundleArticle)
    {
        $oVirtualBasketArticleForBundle = new TShopBasketArticle();
        /** @var $oVirtualBasketArticleForBundle TShopBasketArticle */
        if ($oVirtualBasketArticleForBundle->Load($oBundleArticle->fieldBundleArticleId)) {
            $oVirtualBasketArticleForBundle->dPrice = 0; // set price to zero - alle products belonging to the bundle are free... the price comes from the bundle item.
            $oVirtualBasketArticleForBundle->ChangeAmount($oBundleArticle->fieldAmount * $oBasketItem->dAmount);

            $oBundleOrderItem = $this->SaveArticle($oVirtualBasketArticleForBundle);
            // make connection
            $oBundleConnection = TdbShopOrderBundleArticle::GetNewInstance();
            /** @var $oBundleConnection TdbShopOrderBundleArticle */
            $aConnectionData = array('shop_order_item_id' => $oOrderItem->id, 'bundle_article_id' => $oBundleOrderItem->id, 'amount' => $oBundleArticle->fieldAmount, 'position' => $oBundleArticle->fieldPosition);
            $oBundleConnection->LoadFromRow($aConnectionData);
            $oBundleConnection->AllowEditByAll(true);
            $oBundleConnection->Save();
        }
    }

    /**
     * save vat list to basket. NOTE: the order object must have an id for
     * this method to work.
     *
     * @param TdbShopVatList $oVatList
     */
    public function SaveVATList(TdbShopVatList $oVatList)
    {
        // TODO
        if (!is_null($this->id)) {
            while ($oVat = $oVatList->Next()) {
                $oOrderVat = TdbShopOrderVat::GetNewInstance();
                /** @var $oOrderVat TdbShopOrderVat */
                $aVatData = array('shop_order_id' => $this->id, 'name' => $oVat->GetName(), 'vat_percent' => $oVat->fieldVatPercent, 'value' => $oVat->GetVatValue());
                $oOrderVat->LoadFromRow($aVatData);
                $oOrderVat->AllowEditByAll(true);
                $oOrderVat->Save();
            }
        }
    }

    /**
     * save any user data for the shipping group in the basket
     * NOTE: the order object must have an id for this method to work.
     *
     * @param TdbShopShippingGroup $oShippingGroup
     */
    public function SaveShippingUserData(TdbShopShippingGroup &$oShippingGroup)
    {
        // TODO
        if (!is_null($this->id)) {
        }
    }

    /**
     * save any user data for the payment method in the basket
     * NOTE: the order object must have an id for this method to work.
     *
     * @param TdbShopPaymentMethod $oPaymentMethod
     */
    public function SavePaymentUserData(TdbShopPaymentMethod &$oPaymentMethod)
    {
        if (!is_null($this->id)) {
            $oHandler = $oPaymentMethod->GetFieldShopPaymentHandler();
            if (!is_null($oHandler)) {
                $oHandler->SaveUserPaymentDataToOrder($this->id);
            }
        }
    }

    /**
     * return the payment handler for the order - the handler is initialized
     * with the payment data from the order object.
     *
     * @return TdbShopPaymentHandler|null
     */
    public function &GetPaymentHandler()
    {
        $oPaymentHandler = $this->GetFromInternalCache('oOrderPaymentHandler');
        if (is_null($oPaymentHandler)) {
            $oPaymentMethod = $this->GetFieldShopPaymentMethod();
            if ($oPaymentMethod) {
                $aParameter = array();
                $oPaymentParameterList = $this->GetFieldShopOrderPaymentMethodParameterList();
                while ($oParam = $oPaymentParameterList->Next()) {
                    $aParameter[$oParam->fieldName] = $oParam->fieldValue;
                }
                try {
                    $oPaymentHandler = $this->getShopPaymentHandlerFactory()->createPaymentHandler($oPaymentMethod->fieldShopPaymentHandlerId, $this->fieldCmsPortalId, $aParameter);
                    $this->SetInternalCache('oOrderPaymentHandler', $oPaymentHandler);
                } catch (ConfigurationException $e) {
                    $this->getLogger()->error(
                        sprintf('Unable to create payment handler: %s', $e->getMessage()),
                        [
                            'paymentHandlerId' => $oPaymentMethod->fieldShopPaymentHandlerId,
                            'portalId' => $this->fieldCmsPortalId,
                        ]
                    );
                }
            }
        }

        return $oPaymentHandler;
    }

    /**
     * @return ShopPaymentHandlerFactoryInterface
     */
    private function getShopPaymentHandlerFactory()
    {
        return ServiceLocator::get('chameleon_system_shop.payment.handler_factory');
    }

    private function getLogger(): LoggerInterface
    {
        return ServiceLocator::get('monolog.logger.order');
    }

    /**
     * method is called after all data from the basket has been saved to the order tables.
     */
    public function CreateOrderInDatabaseCompleteHook()
    {
        $aData = $this->sqlData;
        $aData['system_order_save_completed'] = '1';
        $this->LoadFromRow($aData);
        $bAllowEdit = $this->bAllowEditByAll;
        $this->AllowEditByAll(true);
        $this->Save();
        $this->AllowEditByAll($bAllowEdit);

        // update auto groups
        $oTmpuser = null;
        if (!empty($this->fieldDataExtranetUserId)) {
            $oTmpuser = $this->GetFieldDataExtranetUser();
            if (!is_null($oTmpuser)) {
                TdbDataExtranetGroup::UpdateAutoAssignToUser($oTmpuser);
            }
        }
        if ($this->fieldValueTotal <= 0) {
            $this->SetStatusPaid(true);
        }
    }

    /**
     * Hook used to export order for WaWi (called by TShopBasket after creating the order object)
     * return true if the export worked. false if it did not.
     *
     * @param TdbShopPaymentHandler $oPaymentHandler - the payment handler for the order. this is passed via parameter since
     *                                               some payment handler do not save their data in the order (such as credit card)
     *
     * @return bool
     */
    public function ExportOrderForWaWiHook(&$oPaymentHandler)
    {
        return true;
    }

    /**
     * @param bool $bExported
     */
    public function MarkOrderAsExportedToWaWi($bExported = true)
    {
        if ($bExported) {
            $sDate = date('Y-m-d H:i:s');
        } else {
            $sDate = '0000-00-00 00:00:00';
        }
        $aData = $this->sqlData;
        $aData['system_order_exported_date'] = $sDate;
        $this->LoadFromRow($aData);
        $bEditState = $this->bAllowEditByAll;
        $this->AllowEditByAll(true);
        $this->Save();
        $this->AllowEditByAll($bEditState);
    }

    /**
     * send an order notification for this order.
     */
    public function SendOrderNotification($sSendToMail = null, $sSendToName = null)
    {
        $orderNotificationBeforeSendStatus = $this->fieldSystemOrderNotificationSend;
        $this->updateSendOrderNotificationState(true);

        if (is_null($sSendToMail)) {
            $sSendToMail = $this->fieldUserEmail;
        }
        if (is_null($sSendToName)) {
            $sSendToName = $this->fieldAdrBillingFirstname.' '.$this->fieldAdrBillingLastname;
        }

        $oMail = $this->GetOrderNotificationEmailProfile(self::MAIL_CONFIRM_ORDER);

        TCMSImage::ForceNonSSLURLs(true); // force image urls to non ssl for use in order email
        if (is_null($oMail)) {
            if(false === $orderNotificationBeforeSendStatus) {
                $this->updateSendOrderNotificationState(false);
            }
            $bOrderSend = TGlobal::Translate('chameleon_system_shop.order_notification.error_mail_template_not_found', array('%emailTemplate%' => self::MAIL_CONFIRM_ORDER));
        } else {
            $aMailData = $this->sqlData;
            $aMailData = $this->AddOrderNotificationEmailData($aMailData);
            $aMailData = $this->AddOrderNotificationEmailComplexData($aMailData);
            $oMail->AddDataArray($aMailData);
            $aOrderDetails = $this->GetSQLWithTablePrefix();
            $oMail->AddDataArray($aOrderDetails);
            $oMail->ChangeToAddress($sSendToMail, $sSendToName);
            $bOrderSend = $oMail->SendUsingObjectView('emails', 'Customer');
            $this->SaveOrderNotificationDataToOrder($bOrderSend, $oMail, $sSendToMail);
        }
        TCMSImage::ForceNonSSLURLs(false); // reset - force non ssl urls

        return $bOrderSend;
    }

    /**
     * Save additional information to the order for successfully sent notification
     * email and  incorrect sent notification email.
     *
     * @param bool               $bOrderSend
     * @param TdbDataMailProfile $oMail
     * @param string             $sSendToMail
     */
    protected function SaveOrderNotificationDataToOrder($bOrderSend, $oMail, $sSendToMail)
    {
        $aData = $this->sqlData;
        if ($bOrderSend && !TGlobal::IsCMSMode()) {
            $aData = $this->AddOrderNotificationDataForOrderSuccess($aData);
        } elseif (!$bOrderSend && !TGlobal::IsCMSMode()) {
            $aData = $this->AddOrderNotificationDataForOrderFailure($aData, $oMail, $sSendToMail);
        }
        $this->LoadFromRow($aData);
        $this->AllowEditByAll(true);
        $this->Save();
        $this->AllowEditByAll(false);
    }

    protected function updateSendOrderNotificationState(bool $state): void
    {
        $aData = $this->sqlData;
        $aData['system_order_notification_send'] = true === $state ? '1' : '0';

        $this->LoadFromRow($aData);
        $this->AllowEditByAll(true);
        $this->Save();
        $this->AllowEditByAll(false);
    }

    /**
     * Add additional data for the order if notification email was send correctly
     * to given array.
     *
     * @return array
     */
    protected function AddOrderNotificationDataForOrderSuccess($aData)
    {
        if (!is_array($aData)) {
            $aData = array();
        }
        $aData['system_order_notification_send'] = '1';

        return $aData;
    }

    /**
     * Add additional data for the order if notification email was send incorrectly
     * to given array. Add info to the email profile that the notification email was
     * not send correctly. And add the incorrectly sent email profile to the given array.
     *
     * @param array              $aData
     * @param TdbDataMailProfile $oMail
     * @param string             $sSendToMail - target email
     *
     * @return array
     */
    protected function AddOrderNotificationDataForOrderFailure($aData, $oMail, $sSendToMail)
    {
        $aAdditionalMailData = array('iAttemptsToSend' => 1, 'sSendToMail' => $sSendToMail);
        $oMail->AddDataArray($aAdditionalMailData);
        if (!is_array($aData)) {
            $aData = array();
        }
        $aData['object_mail'] = base64_encode(serialize($oMail));
        $aData['system_order_notification_send'] = '0';

        return $aData;
    }

    /**
     * Get email profile for given profile name.
     *
     * @param string $sProfileName
     *
     * @return TdbDataMailProfile
     */
    protected function GetOrderNotificationEmailProfile($sProfileName)
    {
        $oMail = TdbDataMailProfile::GetProfile($sProfileName);

        return $oMail;
    }

    /**
     * Add values for name parameter to email data array.
     *
     * @param array $aMailData
     *
     * @return array
     */
    protected function AddOrderNotificationEmailData($aMailData)
    {
        $oLocal = TCMSLocal::GetActive();
        $oTmp = $this->GetFieldAdrBillingCountry();
        $aMailData = $this->AddOrderNotificationEmailDataArray($aMailData, $oTmp, 'adr_billing_country_name');
        $oTmp = $this->GetFieldAdrBillingSalutation();
        $aMailData = $this->AddOrderNotificationEmailDataArray($aMailData, $oTmp, 'adr_billing_salutation_name');
        $oTmp = $this->GetFieldAdrShippingCountry();
        $aMailData = $this->AddOrderNotificationEmailDataArray($aMailData, $oTmp, 'adr_shipping_country_name');
        $oTmp = $this->GetFieldAdrShippingSalutation();
        $aMailData = $this->AddOrderNotificationEmailDataArray($aMailData, $oTmp, 'adr_shipping_salutation_name');
        $oTmp = $this->GetFieldDataExtranetUser();
        $aMailData = $this->AddOrderNotificationEmailDataArray($aMailData, $oTmp, 'data_extranet_user_name');
        $oTmp = $this->GetFieldShop();
        $aMailData = $this->AddOrderNotificationEmailDataArray($aMailData, $oTmp, 'shop_name');
        $oTmp = $this->GetFieldShopPaymentMethod();
        if (null !== $oTmp) {
            $aMailData['shop_payment_method_name'] = $oTmp->fieldName;
        }
        $oTmp = $this->GetFieldShopShippingGroup();
        if (null !== $oTmp) {
            $aMailData['shop_shipping_group_name'] = $oTmp->fieldName;
        }

        $aMailData['datecreated'] = $oLocal->FormatDate($this->fieldDatecreated, TCMSLocal::DATEFORMAT_SHOW_DATE);

        return $aMailData;
    }

    /**
     * add name value from given object to given array with given field name as key if object is not null.
     *
     * @param array      $aMailData
     * @param TCMSRecord $oNameObject
     * @param string     $sSaveFieldName
     *
     * @return array
     */
    protected function AddOrderNotificationEmailDataArray($aMailData, $oNameObject, $sSaveFieldName)
    {
        $aMailData[$sSaveFieldName] = '';
        if (!is_null($oNameObject)) {
            $aMailData[$sSaveFieldName] = $oNameObject->GetName();
        }

        return $aMailData;
    }

    /**
     * add complex data (rendered view) needed in order email to given array.
     *
     * @param array $aMailData
     *
     * @return array
     */
    protected function AddOrderNotificationEmailComplexData($aMailData)
    {
        $aMailData['sArtikel'] = $this->Render('mailArticleList', 'Customer');
        $aMailData['sArticle'] = $aMailData['sArtikel'];
        $aMailData['sArtikel-text'] = $this->Render('mailArticleList.txt', 'Customer');
        $aMailData['sArticle-text'] = $aMailData['sArtikel-text'];
        $aMailData['sArtikel_text'] = $aMailData['sArtikel-text'];
        $aMailData['sArticle_text'] = $aMailData['sArtikel-text'];

        $aMailData['sRechnungsadresse'] = $this->Render('mailBillingAddress', 'Customer');
        $aMailData['sBillingaddress'] = $aMailData['sRechnungsadresse'];
        $aMailData['sRechnungsadresse-text'] = $this->Render('mailBillingAddress.txt', 'Customer');
        $aMailData['sBillingaddress-text'] = $aMailData['sRechnungsadresse-text'];
        $aMailData['sRechnungsadresse_text'] = $aMailData['sRechnungsadresse-text'];
        $aMailData['sBillingaddress_text'] = $aMailData['sRechnungsadresse-text'];

        $aMailData['sLieferadresse'] = $this->Render('mailShippingAddress', 'Customer');
        $aMailData['sShippingaddress'] = $aMailData['sLieferadresse'];
        $aMailData['sLieferadresse-text'] = $this->Render('mailShippingAddress.txt', 'Customer');
        $aMailData['sShippingaddress-text'] = $aMailData['sLieferadresse-text'];
        $aMailData['sLieferadresse_text'] = $aMailData['sLieferadresse-text'];
        $aMailData['sShippingaddress_text'] = $aMailData['sLieferadresse-text'];

        $aMailData['sZahlungsInfos'] = $this->Render('mailPaymentInfo', 'Customer');
        $aMailData['sPaymentInfos'] = $aMailData['sZahlungsInfos'];
        $aMailData['sZahlungsInfos-text'] = $this->Render('mailPaymentInfo.txt', 'Customer');
        $aMailData['sPaymentInfos-text'] = $aMailData['sZahlungsInfos-text'];
        $aMailData['sZahlungsInfos_text'] = $aMailData['sZahlungsInfos-text'];
        $aMailData['sPaymentInfos_text'] = $aMailData['sZahlungsInfos-text'];

        return $aMailData;
    }

    /**
     * executes payment for order and given payment handler.
     *
     * @param TdbShopPaymentHandler $oPaymentHandler
     * @param string                $sMessageConsumer - message consummer to send errors to
     *
     * @return bool
     */
    public function ExecutePayment(&$oPaymentHandler, $sMessageConsumer = '')
    {
        $bPaymentOk = false;
        $bAllowEdit = $this->bAllowEditByAll;
        $this->AllowEditByAll(true);
        $aData = $this->sqlData;
        $aData['system_order_payment_method_executed'] = '0';
        $aData['system_order_payment_method_executed_date'] = date('Y-m-d H:i:s');
        $this->LoadFromRow($aData);
        $this->Save();

        $bPaymentOk = $this->ExecutePaymentHook($oPaymentHandler, $sMessageConsumer);

        $this->AllowEditByAll($bAllowEdit);

        return $bPaymentOk;
    }

    /**
     * hook used to execute payment. overwrite this instead of the ExecutePayment if
     * you want to add special handling before the payment handlers execute method is called
     * NOTE: returns true on success, an error code on failure. SO MAKE SURE TO CHECK FOR VALID RESPONSE USING TYPE (ie. use === instead of ==).
     *
     * @param TdbShopPaymentHandler $oPaymentHandler
     * @param string                $sMessageConsumer - message consumer to send error messages to
     *
     * @return bool
     */
    protected function ExecutePaymentHook(&$oPaymentHandler, $sMessageConsumer = '')
    {
        return $oPaymentHandler->ExecutePayment($this, $sMessageConsumer);
    }

    /**
     * get info if ordered from registered user or guest user.
     * Was used for cms list.
     */
    public static function getUserTypeOrdered($sDBValue, $aOrderRow)
    {
        $sUserType = TGlobal::Translate('chameleon_system_shop.order_list.user_type_guest');
        if ('' != $sDBValue) {
            $sUserType = TGlobal::Translate('chameleon_system_shop.order_list.user_type_customer');
        }

        return $sUserType;
    }

    /**
     * Get country name for value.
     * Was used for cms list.
     */
    public static function getCountryName($sDBValue, $aOrderRow)
    {
        $oCountry = TdbDataCountry::GetNewInstance();
        $sCountry = '-';
        if ($oCountry->Load($sDBValue)) {
            $sCountry = $oCountry->fieldName;
        }

        return $sCountry;
    }

    /**
     * Get shipping country from value.
     * If value was empty get billing country
     * Was used for cms list.
     */
    public static function getShippingCountryName($sDBValue, $aOrderRow)
    {
        if ('' == $sDBValue) {
            $sCountry = TdbShopOrder::getCountryName($aOrderRow['adr_billing_country_id'], $aOrderRow);
        } else {
            $sCountry = TdbShopOrder::getCountryName($sDBValue, $aOrderRow);
        }

        return $sCountry;
    }

    /**
     * SECTION - RENDER RELATED METHODS.
     */

    /**
     * used to display an order.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Core', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        $oView->AddVar('oOrder', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        return array();
    }

    /**
     * return the net product value of the order (so product value without taxes shipping or payment costs).
     *
     * @return float
     */
    public function GetNetProductValue()
    {
        $dProductNetValue = 0;
        $oArticles = $this->GetFieldShopOrderItemList();
        $oArticles->GoToStart();
        $aTaxValueGroups = array();
        while ($oArticle = $oArticles->Next()) {
            if (!array_key_exists($oArticle->fieldVatPercent, $aTaxValueGroups)) {
                $aTaxValueGroups[$oArticle->fieldVatPercent] = 0;
            }
            $aTaxValueGroups[$oArticle->fieldVatPercent] += $oArticle->fieldOrderPriceAfterDiscounts;
        }

        foreach ($aTaxValueGroups as $dTaxPercent => $dGrosValue) {
            $oVat = TdbShopVat::GetNewInstance();
            if ($oVat->LoadFromField('vat_percent', $dTaxPercent)) {
                $oVat->addValue($dGrosValue);
                $dProductNetValue += $oVat->getNetValue();
            } else {
                $dProductNetValue += round($dGrosValue - ($dGrosValue / (1 + $dTaxPercent / 100)), 2);
            }
        }

        return $dProductNetValue;
    }

    /*
    * change paid state of order
    */
    public function SetStatusPaid($bIsPaid = true)
    {
        if ($this->fieldOrderIsPaid != $bIsPaid) {
            $sDate = '';
            if ($bIsPaid) {
                $sDate = date('Y-m-d H:i:s');
            }
            $aData = $this->sqlData;
            $aData['order_is_paid_date'] = $sDate;
            if ($bIsPaid) {
                $aData['order_is_paid'] = '1';
            } else {
                $aData['order_is_paid'] = '0';
            }
            $this->LoadFromRow($aData);
            $bEditState = $this->bAllowEditByAll;
            $this->AllowEditByAll(true);
            $this->Save();
            $this->AllowEditByAll($bEditState);
        }
    }

    /*
    * change canceled state
    */
    public function SetStatusCanceled($bIsCanceled = true)
    {
        if ($this->fieldCanceled != $bIsCanceled) {
            $sDate = '';
            if ($bIsCanceled) {
                $sDate = date('Y-m-d H:i:s');
            }
            $aData = $this->sqlData;
            $aData['canceled_date'] = $sDate;
            if ($bIsCanceled) {
                $aData['canceled'] = '1';
            } else {
                $aData['canceled'] = '0';
            }
            $this->LoadFromRow($aData);
            $bEditState = $this->bAllowEditByAll;
            $this->AllowEditByAll(true);
            $this->Save();
            $this->UpdateUsedVouchers($bIsCanceled);
            // if we canceled the order, we need to update the stock for all articles
            if ($bIsCanceled) {
                $this->UpdateStock(false);
            } else {
                $this->UpdateStock(true);
            }
            $this->AllowEditByAll($bEditState);
        }
    }

    /**
     * if $bIsCanceled is true we will add all vouchers that have been used with negative values
     * if $bIsCanceled is false so the order gets reactivated all negative used vouchers will be removed.
     *
     * @param bool $bIsCanceled
     */
    public function UpdateUsedVouchers($bIsCanceled)
    {
        $oShopVoucherUseList = $this->GetFieldShopVoucherUseList();
        $oShopVoucherUseList->GoToStart();
        if ($bIsCanceled) {
            while ($oShopVoucherUse = $oShopVoucherUseList->Next()) {
                $oShopVoucher = $oShopVoucherUse->GetFieldShopVoucher();
                if ($oShopVoucher->fieldIsUsedUp) {
                    $oShopVoucher->sqlData['is_used_up'] = '0';
                    $oShopVoucher->sqlData['date_used_up'] = '';
                    $oShopVoucher->fieldIsUsedUp = false;
                    $oShopVoucher->fieldDateUsedUp = '';
                    $oShopVoucher->Save();
                }
                $oNewShopVoucherUse = clone $oShopVoucherUse;
                $aData = $oNewShopVoucherUse->sqlData;
                unset($aData['id']);
                unset($aData['cmsident']);
                $aData['value_used'] = $aData['value_used'] * -1;
                $aData['date_used'] = date('Y-m-d H:i:s');
                $oNewShopVoucherUse->LoadFromRow($aData);
                $oNewShopVoucherUse->AllowEditByAll(true);
                $oNewShopVoucherUse->Save();
                $oBasket = TShopBasket::GetInstance();
                if (!is_null($oBasket)) {
                    $oBasket->SetBasketRecalculationFlag(true);
                }
            }
        } else {
            $oShopVoucherUseList->AddFilterString('`shop_voucher_use`.`value_used` < 0.00');
            while ($oShopVoucherUse = $oShopVoucherUseList->Next()) {
                $oShopVoucher = $oShopVoucherUse->GetFieldShopVoucher();
                if (true == $oShopVoucher->fieldIsUsedUp) {
                    $this->postActivateUsedUpVoucher($oShopVoucher);
                    continue;
                }
                $voucherSeries = $oShopVoucher->GetFieldShopVoucherSeries();
                if (null === $voucherSeries) {
                    continue;
                }
                $previouslyUsed = $oShopVoucher->GetValuePreviouslyUsed();
                $previouslyUsedWithoutActiveVoucher = $previouslyUsed - $oShopVoucherUse->fieldValueUsed;
                if ($previouslyUsedWithoutActiveVoucher > $voucherSeries->fieldValue) {
                    $oShopVoucherUse->fieldValueUsed = $oShopVoucherUse->fieldValueUsed + $previouslyUsed;
                    $oShopVoucherUse->sqlData['value_used'] = $oShopVoucherUse->fieldValueUsed;
                    $oShopVoucherUse->AllowEditByAll();
                    $oShopVoucherUse->Save();
                    $this->postActivateUsedUpVoucher($oShopVoucher);
                } else {
                    $oShopVoucherUse->AllowEditByAll();
                    $oShopVoucherUse->Delete();
                }
                if (true == $oShopVoucher->checkMarkVoucherAsCompletelyUsed()) {
                    $oShopVoucher->MarkVoucherAsCompletelyUsed();
                }
            }
        }
    }

    /**
     * @param TdbShopVoucher $voucher
     */
    private function postActivateUsedUpVoucher(TdbShopVoucher $voucher)
    {
        $this->logOnActivatingUsedUpVoucher($voucher);
        $this->sendMailOnActivatingUsedUpVoucher($voucher);
    }

    /**
     * @param TdbShopVoucher $voucher
     */
    private function logOnActivatingUsedUpVoucher(TdbShopVoucher $voucher)
    {
        $message = sprintf(
            'Can not activate voucher on changing order cancel status because voucher was used up already. Please check your order (%s) for correctness.',
            $this->id
        );
        $context = array(
            'orderId' => $this->id,
            'voucherId' => $voucher->id,
            'voucherCode' => $voucher->fieldCode,
        );
        $this->getLogger()->error($message, $context);
    }

    /**
     * @param TdbShopVoucher $voucher
     */
    private function sendMailOnActivatingUsedUpVoucher(TdbShopVoucher $voucher)
    {
        $mailProfile = TdbDataMailProfile::GetProfile('error-voucher-activation');
        $mailProfile->AddDataArray($this->sqlData);
        $mailProfile->AddData('voucherCode', $voucher->fieldCode);
        $mailProfile->AddData('voucherSeriesId', $voucher->fieldShopVoucherSeriesId);
        $mailProfile->AddData('voucherId', $voucher->id);
        $mailProfile->SendUsingObjectView('emails', 'Customer');
    }

    protected function PreDeleteHook()
    {
        parent::PreDeleteHook();
        $this->UpdateStock(false);
    }

    /**
     * remove or add the stock of the order back into the product pool.
     *
     * @param bool $bRemoveFromStock
     */
    public function UpdateStock($bRemoveFromStock)
    {
        $oOrderItemList = $this->GetFieldShopOrderItemList();
        $oOrderItemList->GoToStart();
        while ($oOrderItem = $oOrderItemList->Next()) {
            $oItem = $oOrderItem->GetFieldShopArticle();
            if ($oItem) {
                $dAmount = $oOrderItem->fieldOrderAmount;
                if ($bRemoveFromStock) {
                    $dAmount = -1 * $dAmount;
                }
                $oItem->UpdateStock($dAmount, true, true);
            }
        }
        $oOrderItemList->GoToStart();
    }

    /* SECTION: CACHE RELEVANT METHODS FOR THE RENDER METHOD

    /**
     * returns an array with all table names that are relevant for the render function.
     *
     * @param string $sViewName - the view name being requested (if know by the caller)
     * @param string $sViewType - the view type (core, custom-core, customer) being requested (if know by the caller)
     *
     * @return array
     */
    public static function GetCacheRelevantTables($sViewName = null, $sViewType = null)
    {
        $aTables = array();
        $aTables[] = 'shop_order';
        $aTables[] = 'shop_order_item';
        $aTables[] = 'shop_order_payment_method_parameter';
        $aTables[] = 'shop_order_shipping_group_parameter';
        $aTables[] = 'shop_order_vat';

        return $aTables;
    }

    /**
     * this method is called in TShopBasket->CreateOrder() just before execution
     * of the payment method and after saving the order to the database.
     *
     * you may use this hook to call external APIs to save the order externaly
     * if this method returns false the payment will not be executed and the
     * order canceled
     *
     * @param string $sMessageConsumer TCMSMessageManager consumer name
     *
     * @return bool
     */
    public function PrePaymentExecuteHook($sMessageConsumer)
    {
        /**
         * call external api and if call fails add an error message
         * $oMsgManager = TCMSMessageManager::GetInstance();
         * $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-SAVE-VIA-API-RETURNED-ERROR');.
         */

        return true;
    }

    /**
     * this method is called in TShopBasket->CreateOrder() on saving
     * the order to the database.
     *
     * @param TShopBasketDiscountList $oShopBasketDiscountList TShopBasketDiscountList
     *
     * @return bool
     */
    public function SaveDiscounts($oShopBasketDiscountList = null)
    {
        if (!is_null($this->id) && !is_null($oShopBasketDiscountList) && $oShopBasketDiscountList->Length() > 0) {
            $oShopBasketDiscountList->GoToStart();
            while ($oShopBasketDiscount = $oShopBasketDiscountList->Next()) {
                //save...
                $oShopOrderDiscount = TdbShopOrderDiscount::GetNewInstance();
                /** @var $oShopOrderDiscount TdbShopOrderDiscount */
                $aConnectionData = array(
                    'shop_order_id' => $this->id,
                    'shop_discount_id' => $oShopBasketDiscount->id,
                    'name' => $oShopBasketDiscount->fieldName,
                    'value' => $oShopBasketDiscount->fieldValueFormated,
                    'valuetype' => $oShopBasketDiscount->fieldValueType,
                    'total' => $oShopBasketDiscount->GetValue(),
                );
                $aConnectionData = $this->saveDiscountsEnrichDiscountSaveData($this, $oShopBasketDiscount, $aConnectionData);

                $oShopOrderDiscount->LoadFromRow($aConnectionData);
                $oShopOrderDiscount->AllowEditByAll(true);
                $oShopOrderDiscount->Save();
            }
        }

        return true;
    }

    protected function saveDiscountsEnrichDiscountSaveData(TdbShopOrder $order, TdbShopDiscount $oShopBasketDiscount, $aConnectionData)
    {
        return $aConnectionData;
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return ServiceLocator::get('request_stack')->getCurrentRequest();
    }

    private function getPortalDomainService(): PortalDomainServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
