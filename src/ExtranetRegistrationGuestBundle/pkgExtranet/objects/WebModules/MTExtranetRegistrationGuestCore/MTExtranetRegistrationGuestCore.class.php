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
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;

/**
 * Use this package module to register user after creating order with guest account.
 *
 * @psalm-suppress UndefinedInterfaceMethod
 *
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
                $sSuccessURL = $this->global->GetUserData('sSuccessURL', [], TCMSUserInput::FILTER_URL);
            }
            if (empty($sSuccessURL)) {
                $sSuccessURL = null;
            }
        }
        if (null === $sFailureURL) {
            if ($this->global->UserDataExists('sFailureURL')) {
                $sFailureURL = $this->global->GetUserData('sFailureURL', [], TCMSUserInput::FILTER_URL);
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
                    $oUserOrder = TShopBasket::GetLastCreatedOrder();
                    $sNewUserId = $oUser->Register();
                    $this->UpdateUserAddress(null, null, true);
                    $oUserOrder->AllowEditByAll(true);
                    $oUserOrder->SaveFieldsFast(['data_extranet_user_id' => $sNewUserId]);
                    $oUserOrder->AllowEditByAll(false);
                    if (null !== $sSuccessURL) {
                        $this->getRedirectService()->redirect($sSuccessURL, true);
                    } else {
                        $oExtranetConf = TdbDataExtranet::GetInstance();
                        $this->getRedirectService()->redirect($oExtranetConf->GetLinkRegisterSuccessPage(), true);
                    }
                }
            }
        }
        if (null !== $sFailureURL) {
            $this->getRedirectService()->redirect($sFailureURL, true);
        } else {
            $oUser = TdbDataExtranetUser::GetInstance();
            $this->getRedirectService()->redirect($oUser->GetLinkForRegistrationGuest(), true);
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
                $this->getRedirectService()->redirect($oExtranetConfig->GetLinkAccessDeniedPage(), true);
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
                    $oUserOrder = TShopBasket::GetLastCreatedOrder();
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
     * @param TdbDataExtranetUser $oUser
     *
     * @return bool
     */
    protected function ValidateGivenUserData($oUser)
    {
        $bIsValid = true;
        $oMessages = TCMSMessageManager::GetInstance();
        $aRequiredFields = ['data_extranet_salutation_id', 'firstname', 'lastname', 'street', 'postalcode', 'city', 'data_country_id'];
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
        $oShop = ServiceLocator::get('chameleon_system_shop.shop_service')->getShopForPortalId($oURLData->iPortalId);
        $sNodeId = $oShop->GetSystemPageNodeId('register-after-shopping');
        $oActivePage = $this->getActivePageService()->getActivePage();
        if ($oActivePage && $sNodeId == $oActivePage->GetMainTreeId()) {
            $bActivePageIsRegisterAfterShopping = true;
        }

        return $bActivePageIsRegisterAfterShopping;
    }

    private function getActivePageService(): ActivePageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    private function getExtranetUserProvider(): ExtranetUserProviderInterface
    {
        return ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }

    private function getRedirectService(): ICmsCoreRedirect
    {
        return ServiceLocator::get('chameleon_system_core.redirect');
    }
}
