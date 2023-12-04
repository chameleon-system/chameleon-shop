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
     *
     * @return void
     */
    protected function GetUserDataFromPayPalData(&$aBilling, &$aShipping)
    {
        $countryIsoCode = $this->aCheckoutDetails['SHIPTOCOUNTRYCODE'] ?? 'de';
        $shippingCountry = TdbDataCountry::GetInstanceForIsoCode($countryIsoCode);

        $mail = $this->aCheckoutDetails['EMAIL'] ?? '';
        $company = $this->aCheckoutDetails['BUSINESS'] ?? '';
        $firstname = $this->aCheckoutDetails['FIRSTNAME'] ?? '';
        $lastname = $this->aCheckoutDetails['LASTNAME'] ?? '';
        $street = $this->aCheckoutDetails['SHIPTOSTREET'] ?? '';
        $city = $this->aCheckoutDetails['SHIPTOCITY'] ?? '';
        $postalCode = $this->aCheckoutDetails['SHIPTOZIP'] ?? '';
        $phone = $this->aCheckoutDetails['PHONENUM'] ?? '';
        $addressAdditionalInfo = $this->aCheckoutDetails['SHIPTOSTREET2'] ?? '';

        $aBilling = [
            'name' => $mail,
            'email' => $mail,
            'company' => $company,
            'data_extranet_salutation_id' => '',
            'firstname' => $firstname,
            'lastname' => $lastname,
            'street' => $street,
            'streenr' => '',
            'city' => $city,
            'postalcode' => $postalCode,
            'telefon' => $phone,
            'fax' => '',
            'data_country_id' => $shippingCountry->id ?? '',
            'address_additional_info' => $addressAdditionalInfo,
        ];

        $shippingFirstAnLastName = $this->getPayPalResponseShippingFirstAnLastName(
            $firstname,
            $lastname,
            $this->aCheckoutDetails['SHIPTONAME'] ?? null
        );
        $aShipping = [
            'company' => $company,
            'data_extranet_salutation_id' => '',
            'firstname' => $shippingFirstAnLastName['firstname'],
            'lastname' => $shippingFirstAnLastName['lastname'],
            'street' => $street,
            'streenr' => '',
            'city' => $city,
            'postalcode' => $postalCode,
            'telefon' => $phone,
            'fax' => '',
            'data_country_id' => $shippingCountry->id ?? '',
            'address_additional_info' => $addressAdditionalInfo,
        ];

        $this->postProcessBillingAndShippingAddress($aBilling, $aShipping);
    }

    /**
     * use buyer's first and lastname, if no "shipToName" is provided,
     * or if "shipToName" is just a simple creation out of both.
     */
    private function getPayPalResponseShippingFirstAnLastName(
        string $userFirstname,
        string $userLastname,
        ?string $shipToName
    ): array {
        $result = [
            'firstname' => $userFirstname,
            'lastname' => $userLastname,
        ];

        if (null === $shipToName) {
            return $result;
        }

        $fullNameLowerCase = sprintf('%s %s', mb_strtolower($userFirstname), mb_strtolower($userLastname));
        if ($fullNameLowerCase === mb_strtolower($shipToName)) {
            return $result;
        }

        return [
            'firstname' => '',
            'lastname' => $shipToName,
        ];
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

    /**
     * @param string $postalcode
     * @param string $city
     * @return bool
     */
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
