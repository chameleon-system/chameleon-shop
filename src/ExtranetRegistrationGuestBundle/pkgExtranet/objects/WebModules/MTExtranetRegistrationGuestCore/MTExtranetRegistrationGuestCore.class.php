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
use ChameleonSystem\CoreBundle\Service\RequestInfoServiceInterface;
use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Use this package module to register user after creating order with guest account.
 *
/**/
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
     * If the user is not allowed to be registered, show access denied page instead.
     */
    protected function HandleRegisterAfterShopping()
    {
        if (true === $this->getRequestInfoService()->isCmsTemplateEngineEditMode()) {
            return;
        }

        if (false === $this->ActivePageIsRegisterAfterShopping()) {
            return;
        }

        if (false === $this->IsAllowedToShowRegisterAfterShoppingPage()) {
            throw new AccessDeniedHttpException('user is not a guest user and has no access to the post sale registration page');
        }
    }

    /**
     * Checks if user is allowed to register after shopping.
     *
     * @return bool
     */
    protected function IsAllowedToShowRegisterAfterShoppingPage()
    {
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
        $registerAfterShoppingPage = $this->getSystemPageService()->getSystemPage('register-after-shopping');

        if (null === $registerAfterShoppingPage) {
            return false;
        }

        $registerAfterShoppingPageTreeNodeId = $registerAfterShoppingPage->fieldCmsTreeId;
        $activePage = $this->getActivePageService()->getActivePage();

        if (null === $activePage) {
            return false;
        }

        return $registerAfterShoppingPageTreeNodeId === $activePage->GetMainTreeId();
    }

    private function getActivePageService(): ActivePageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    private function getExtranetUserProvider(): ExtranetUserProviderInterface
    {
        return ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }

    private function getRequestInfoService(): RequestInfoServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.request_info_service');
    }

    private function getSystemPageService(): SystemPageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.system_page_service');
    }
}
