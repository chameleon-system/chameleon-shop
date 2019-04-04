<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use Psr\Log\LoggerInterface;

class TShopPaymentHandlerPayPalExpress extends TShopPaymentHandlerPayPal
{
    /**
     * the method is called when an external payment handler returns successfully.
     *
     * for paypal express we need to set the user based on the paypal data (or, if a user is set, update
     * it using the data passed)
     *
     * public function PostProcessExternalPaymentHandlerHook() {
     *       hier:
     *           Daten von PP übernehmen.
     *           $oUser = ExtUser::GetInstance();
     *           Wenn CH-User angemeldet ($oUser->is_logged_in):
     *               Userdaten von PP Save() wenn Lieferadresse != isSameAs();
     *           Wenn nicht:
     *               User anlegen mit PP Lieferadresse LoadFromRow() + Save()
     */
    public function PostProcessExternalPaymentHandlerHook()
    {
        $bResponse = parent::PostProcessExternalPaymentHandlerHook();

        if ($bResponse) {
            $oUser = TdbDataExtranetUser::GetInstance();

            $oUser = TdbDataExtranetUser::GetInstance();
            if (is_null($oUser->id)) {
                $aBilling = array();
                $aShipping = array();
                $this->GetUserDataFromPayPalData($aBilling, $aShipping);
                if (empty($aShipping['firstname']) && empty($aShipping['lastname'])) {
                    $aShipping['firstname'] = $aBilling['firstname'];
                    $aShipping['lastname'] = $aBilling['lastname'];
                }
                $aData = $aBilling;
                // password is a required field... but since we do not ask for a password yet, we have to simulate its presence
                if (!array_key_exists('password', $aData) || empty($aData['password'])) {
                    $aData['password'] = '-';
                }
                $oUser->LoadFromRowProtected($aData);

                // we need to update the billing address as well...
                $tmpAdr = $oUser->GetBillingAddress(true);
                $aShippingData = $aShipping;
                $oUser->UpdateShippingAddress($aShippingData);
                $oShippingAddress = $oUser->GetShippingAddress();
            } elseif ($oUser->IsLoggedIn()) {
                // user is logged in - we just set the shipping address
                $oBillingAdr = $oUser->GetBillingAddress();
                $aBilling = array();
                $aShipping = array();
                $this->GetUserDataFromPayPalData($aBilling, $aShipping);
                //          if (!array_key_exists('data_extranet_salutation_id',$aShipping) || empty($aShipping['data_extranet_salutation_id'])) $aShipping['data_extranet_salutation_id'] = $oBillingAdr->fieldDataExtranetSalutationId;
                if (empty($aShipping['firstname']) && empty($aShipping['lastname'])) {
                    $aShipping['firstname'] = $oBillingAdr->fieldFirstname;
                    $aShipping['lastname'] = $oBillingAdr->fieldLastname;
                }
                $oUser->UpdateShippingAddress($aShipping);
                $oShippingAddress = $oUser->GetShippingAddress();
            } else {
                // user has an ID but is not logged in. That should not happen, so log it and exit
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(TCMSMessageManager::GLOBAL_CONSUMER_NAME, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => TGlobal::Translate('chameleon_system_shop.payment_paypal_express.error_guest_user_with_id')));
                trigger_error('A user with ID was marked as not logged in in TShopBsPaymentHandlerPayPal::PostProcessExternalPaymentHandlerHook', E_USER_WARNING);
                $bResponse = false;
            }
            //$this->aCheckoutDetails
        }

        $logger = $this->getPaypalLogger();

        if ($bResponse) {
            $oBasket = TShopBasket::GetInstance();
            // paypal expres success... redirect to confirm page. we need to force correct redirection here.
            $logger->info('PostProcessExternalPaymentHandlerHook: return from express ok - redirect to checkout');
            $oNextStep = &TdbShopOrderStep::GetStep('confirm');
            $oStepList = TdbShopOrderStepList::GetNavigationStepList($oNextStep);
            $oStepList->GoToStart();
            $bStop = false;
            while (!$bStop && ($oStep = $oStepList->Next())) {
                if ($oStep->id != $oNextStep->id) {
                    $oBasket->aCompletedOrderStepList[$oStep->fieldSystemname] = true;
                } else {
                    $bStop = true;
                }
            }
            $oStepList->GoToStart();
            $oNextStep->JumpToStep($oNextStep);
        } else {
            $logger->error('PostProcessExternalPaymentHandlerHook: return from express NOT ok');
        }

        return $bResponse;
    }

    /**
     * updates teh aBilling and aShipping arrays with the user billing and shipping
     * info returned from paypal.
     *
     * @param array $aBilling
     * @param array $aShipping
     */
    protected function GetUserDataFromPayPalData(&$aBilling, &$aShipping)
    {
        $sCountryIsoCode = 'de';
        if (array_key_exists('SHIPTOCOUNTRYCODE', $this->aCheckoutDetails)) {
            $sCountryIsoCode = $this->aCheckoutDetails['SHIPTOCOUNTRYCODE'];
        }

        $oShippingCountry = TdbDataCountry::GetInstanceForIsoCode($sCountryIsoCode);

        $sMail = (array_key_exists('EMAIL', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['EMAIL'] : '';
        $sCompany = (array_key_exists('BUSINESS', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['BUSINESS'] : '';
        //$sDataExtranetSalutationId = (array_key_exists('',$this->aCheckoutDetails)) ? $this->aCheckoutDetails[''] : '';
        $sFirstname = (array_key_exists('FIRSTNAME', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['FIRSTNAME'] : '';
        $sLastname = (array_key_exists('LASTNAME', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['LASTNAME'] : '';

        $sStreet = (array_key_exists('SHIPTOSTREET', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['SHIPTOSTREET'] : '';
        $sCity = (array_key_exists('SHIPTOCITY', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['SHIPTOCITY'] : '';
        $sPostalcode = (array_key_exists('SHIPTOZIP', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['SHIPTOZIP'] : '';
        $sTelefon = (array_key_exists('PHONENUM', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['PHONENUM'] : '';
        $addressAdditionalInfo = (array_key_exists('SHIPTOSTREET2', $this->aCheckoutDetails)) ? $this->aCheckoutDetails['SHIPTOSTREET2'] : '';

        $aBilling = array('name' => $sMail, 'email' => $sMail, 'company' => $sCompany, 'data_extranet_salutation_id' => '', 'firstname' => $sFirstname, 'lastname' => $sLastname, 'street' => $sStreet, 'streenr' => '', 'city' => $sCity, 'postalcode' => $sPostalcode, 'telefon' => $sTelefon, 'fax' => '', 'data_country_id' => $oShippingCountry->id, 'address_additional_info' => $addressAdditionalInfo);

        $sShippingLastName = (array_key_exists('SHIPTONAME', $this->aCheckoutDetails) && '' != $this->aCheckoutDetails['SHIPTONAME']) ? $this->aCheckoutDetails['SHIPTONAME'] : $sFirstname.' '.$sLastname;

        $aShipping = array('company' => $sCompany, 'data_extranet_salutation_id' => '', 'firstname' => '', 'lastname' => $sShippingLastName, 'street' => $sStreet, 'streenr' => '', 'city' => $sCity, 'postalcode' => $sPostalcode, 'telefon' => $sTelefon, 'fax' => '', 'data_country_id' => $oShippingCountry->id, 'address_additional_info' => $addressAdditionalInfo);

        $this->postProcessBillingAndShippingAddress($aBilling, $aShipping);
    }

    /**
     * @param array $billingAddress
     * @param array $shippingAddress
     *
     * @return bool
     */
    protected function postProcessBillingAndShippingAddress(array &$billingAddress, array &$shippingAddress)
    {
        $modified = false;
        if ($this->postalCodeAndCitySwitched($billingAddress['postalcode'], $billingAddress['city'])) {
            $city = $billingAddress['postalcode'];
            $billingAddress['postalcode'] = $billingAddress['city'];
            $billingAddress['city'] = $city;
            $modified = true;
        }
        if ($this->postalCodeAndCitySwitched($shippingAddress['postalcode'], $shippingAddress['city'])) {
            $city = $shippingAddress['postalcode'];
            $shippingAddress['postalcode'] = $shippingAddress['city'];
            $shippingAddress['city'] = $city;
            $modified = true;
        }

        return $modified;
    }

    protected function postalCodeAndCitySwitched($postalcode, $city)
    {
        $postalcode = trim($postalcode);
        $city = trim($city);
        $pattern = '/^\d+$/u';

        $plzIsNumeric = (1 === preg_match($pattern, $postalcode));
        $cityIsNumeric = (1 === preg_match($pattern, $city));

        if (true === $cityIsNumeric && false === $plzIsNumeric) {
            return true;
        }

        return false;
    }

    private function getPaypalLogger(): LoggerInterface
    {
        return ServiceLocator::get('monolog.logger.order');
    }
}
