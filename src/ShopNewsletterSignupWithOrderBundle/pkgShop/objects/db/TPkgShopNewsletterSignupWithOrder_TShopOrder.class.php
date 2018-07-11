<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopNewsletterSignupWithOrder_TShopOrder extends TPkgShopNewsletterSignupWithOrder_TShopOrderAutoParent
{
    // ------------------------------------------------------------------------

    /**
     * method can be used to modify the data saved to order before the save is executed.
     *
     * @param TShopBasket $oBasket
     * @param array       $aOrderData
     */
    protected function LoadFromBasketPostProcessData($oBasket, &$aOrderData)
    {
        // ------------------------------------------------------------------------
        parent::LoadFromBasketPostProcessData($oBasket, $aOrderData);
        if (method_exists($oBasket, 'getUserSelectedNewsletterOptionInOrderStep') && $oBasket->getUserSelectedNewsletterOptionInOrderStep()) {
            $aOrderData['newsletter_signup'] = '1';
        } else {
            $aOrderData['newsletter_signup'] = '0';
        }
    }

    // ------------------------------------------------------------------------
    // ------------------------------------------------------------------------

    /**
     * method is called after all data from the basket has been saved to the order tables.
     */
    public function CreateOrderInDatabaseCompleteHook()
    {
        // ------------------------------------------------------------------------
        if ($this->fieldNewsletterSignup && false == $this->fieldCanceled) {
            $sMail = $this->fieldUserEmail;
            $sUserId = $this->fieldDataExtranetUserId;
            // now signup user

            $oNewsletter = null;
            if (!empty($sUserId)) {
                // try to load by id
                $oNewsletter = TdbPkgNewsletterUser::GetNewInstance();
                if (false == $oNewsletter->LoadFromField('data_extranet_user_id', $sUserId)) {
                    $oNewsletter = null;
                }
            }

            if (is_null($oNewsletter)) {
                $aData = array();
                $sNow = date('Y-m-d H:i:s');
                $aData['email'] = $sMail;
                $aData['data_extranet_salutation_id'] = $this->fieldAdrBillingSalutationId;
                $aData['lastname'] = $this->fieldAdrBillingLastname;
                $aData['firstname'] = $this->fieldAdrBillingFirstname;
                $aData['signup_date'] = $sNow;
                $aData['optin'] = '1';
                $aData['optin_date'] = $sNow;
                /**
                 * There are different ways of opting in into the newsletter table:
                 * - Subscribe on Website
                 * - Subscribe on MyAccount page
                 * - Subscribe while ordering
                 * This information is now put into the optincode field.
                 */
                $aData['optincode'] = 'signup-via-order-confirm-page';
                $aData['data_extranet_user_id'] = $sUserId;

                $oNewsletter = TdbPkgNewsletterUser::GetNewInstance($aData);

                $oNewsletter->AllowEditByAll(true);
                $oNewsletter->Save();
                TdbPkgNewsletterUser::GetInstanceForActiveUser(true);
            } elseif (false == $oNewsletter->fieldOptin) {
                $oNewsletter->AllowEditByAll(true);
                $oNewsletter->SaveFieldsFast(array('optincode' => 'signup-via-order-confirm-page', 'optin' => '1', 'optin_date' => date('Y-m-d H:i:s')));
                TdbPkgNewsletterUser::GetInstanceForActiveUser(true);
            }
        }
        parent::CreateOrderInDatabaseCompleteHook();
    }

    // ------------------------------------------------------------------------
}
