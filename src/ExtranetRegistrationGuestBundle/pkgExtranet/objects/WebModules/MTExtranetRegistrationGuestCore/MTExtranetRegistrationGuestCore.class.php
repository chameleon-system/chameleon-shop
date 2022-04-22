<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;

/**
 * Use this package module to register user after creating order with guest account.
 *
 * @psalm-suppress UndefinedInterfaceMethod
 * @FIXME `HeaderURLRedirect` only exist on one implementation of `ChameleonControllerInterface`
 */
class MTExtranetRegistrationGuestCore extends MTExtranetRegistrationGuestCoreAutoParent
{
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'RegisterGuestUser';
    }

    public function Init()
    {
        parent::Init();
        $this->HandleRegisterAfterShopping();
    }

    /**
     * Register guest user. Requires last basket step to be thankyou.
     *
     * @param null $sSuccessURL
     * @param null $sFailureURL
     *
     * @return void
     */
    protected function RegisterGuestUser($sSuccessURL = null, $sFailureURL = null)
    {
        if (null === $sSuccessURL) {
            if ($this->global->UserDataExists('sSuccessURL')) {
                $sSuccessURL = $this->global->GetUserData('sSuccessURL', array(), TCMSUserInput::FILTER_URL);
            }
            if (empty($sSuccessURL)) {
                $sSuccessURL = null;
            }
        }
        if (null === $sFailureURL) {
            if ($this->global->UserDataExists('sFailureURL')) {
                $sFailureURL = $this->global->GetUserData('sFailureURL', array(), TCMSUserInput::FILTER_URL);
            }
            if (empty($sFailureURL)) {
                $sFailureURL = null;
            }
        }

        /** @var TShopStepOrderCompleted|null $oStep */
        $oStep = TdbShopOrderStep::GetStep('thankyou');
        if (null !== $oStep) {
            $oTmpUser = $oStep->GetLastUserBoughtFromSession();
            $aData = $this->GetFilteredUserData('aUser');
            if (is_array($aData) && 2 == count($aData) && array_key_exists('password', $aData) && array_key_exists('password2', $aData)) {
                foreach ($aData as $key => $val) {
                    $oTmpUser->sqlData[$key] = trim($val);
                }
                $aData = $oTmpUser->sqlData;

                $extranetUserProvider = $this->getExtranetUserProvider();
                $extranetUserProvider->reset();
                $oUser = $extranetUserProvider->getActiveUser();
                $oUser->LoadFromRow($aData);
                $bDataValid = $this->ValidateUserLoginData();
                $bDataValid = $this->ValidateUserData() && $bDataValid;
                if ($bDataValid) {
                    $oUserOrder = &TShopBasket::GetLastCreatedOrder();
                    $sNewUserId = $oUser->Register();
                    $this->UpdateUserAddress(null, null, true);
                    $oUserOrder->AllowEditByAll(true);
                    $oUserOrder->SaveFieldsFast(array('data_extranet_user_id' => $sNewUserId));
                    $oUserOrder->AllowEditByAll(false);
                    if (!is_null($sSuccessURL)) {
                        $this->controller->HeaderURLRedirect($sSuccessURL, true);
                    } else {
                        $oExtranetConf = &TdbDataExtranet::GetInstance();
                        $this->controller->HeaderURLRedirect($oExtranetConf->GetLinkRegisterSuccessPage(), true);
                    }
                    $oStep->RemoveLastUserBoughtFromSession();
                }
            }
        }
        if (null !== $sFailureURL) {
            $this->controller->HeaderURLRedirect($sFailureURL, true);
        } else {
            $oUser = TdbDataExtranetUser::GetInstance();
            $this->controller->HeaderURLRedirect($oUser->GetLinkForRegistrationGuest(), true);
        }
    }

    /**
     * if user is not allowed to be register redirect to access denied page.
     *
     * @return void
     */
    protected function HandleRegisterAfterShopping()
    {
        if ($this->ActivePageIsRegisterAfterShopping()) {
            if (!$this->IsAllowedToShowRegisterAfterShoppingPage()) {
                $oExtranetConfig = TdbDataExtranet::GetInstance();
                $this->controller->HeaderURLRedirect($oExtranetConfig->GetLinkAccessDeniedPage(), true);
            }
        }
    }

    /**
     * Checks if user is allowed to register after shopping.
     *
     * @return bool
     */
    protected function IsAllowedToShowRegisterAfterShoppingPage()
    {
        /** @var TShopStepOrderCompleted $oStep */
        $oStep = TdbShopOrderStep::GetStep('thankyou');
        $bUserIsValid = false;
        if (!is_null($oStep)) {
            $oUser = $oStep->GetLastUserBoughtFromSession();
            if (!is_null($oUser)) {
                $bUserIsValid = $this->ValidateGivenUserData($oUser);
                if ($bUserIsValid) {
                    $oUserOrder = &TShopBasket::GetLastCreatedOrder();
                    if ($oUser->LoginExists()) {
                        $bUserIsValid = false;
                    }
                    if (is_null($oUserOrder)) {
                        $bUserIsValid = false;
                    }
                }
            }
        }

        return $bUserIsValid;
    }

    /**
     * validate the extranet user data (in the current extranet object).
     *
     * @return bool
     *
     * @param TdbDataExtranetUser $oUser
     */
    protected function ValidateGivenUserData($oUser)
    {
        $bIsValid = true;
        $oMessages = TCMSMessageManager::GetInstance();
        $aRequiredFields = array('data_extranet_salutation_id', 'firstname', 'lastname', 'street', 'postalcode', 'city', 'data_country_id');
        foreach ($aRequiredFields as $sField) {
            $sValue = trim($oUser->sqlData[$sField]);

            if (!array_key_exists($sField, $oUser->sqlData) || empty($sValue)) {
                $bIsValid = false;
                $oMessages->AddMessage(TdbDataExtranetUser::MSG_FORM_FIELD.'-'.$sField, 'ERROR-USER-REQUIRED-FIELD-MISSING');
            }
        }

        return $bIsValid;
    }

    /**
     * Checks if active page is register guest page.
     *
     * @return bool
     */
    protected function ActivePageIsRegisterAfterShopping()
    {
        $bActivePageIsRegisterAfterShopping = false;
        $oURLData = TCMSSmartURLData::GetActive();
        $oShop = TdbShop::GetInstance($oURLData->iPortalId);
        $sNodeId = $oShop->GetSystemPageNodeId('register-after-shopping');
        $oActivePage = $this->getActivePageService()->getActivePage();
        if ($oActivePage && $sNodeId == $oActivePage->GetMainTreeId()) {
            $bActivePageIsRegisterAfterShopping = true;
        }

        return $bActivePageIsRegisterAfterShopping;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
