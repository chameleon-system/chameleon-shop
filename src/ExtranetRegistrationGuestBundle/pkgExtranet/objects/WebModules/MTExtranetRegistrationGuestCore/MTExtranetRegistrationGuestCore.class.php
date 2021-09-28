<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Interfaces\FlashMessageServiceInterface;
use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\CoreBundle\Service\RequestInfoServiceInterface;
use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Use this package module to register user after creating order with guest account.
 *
/**/
class MTExtranetRegistrationGuestCore extends MTExtranetRegistrationGuestCoreAutoParent
{
    const MSG_CONSUMER_LOGIN_PAGE = 'loginBox';

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
                $sSuccessURL = $this->global->GetUserData('sSuccessURL', [], TCMSUserInput::FILTER_URL);
            }

            if (empty($sSuccessURL)) {
                $extranetConfiguration = \TdbDataExtranet::GetInstance();
                $sSuccessURL = $extranetConfiguration->GetLinkRegisterSuccessPage();
            }
        }

        if (null === $sFailureURL) {
            if ($this->global->UserDataExists('sFailureURL')) {
                $sFailureURL = $this->global->GetUserData('sFailureURL', [], TCMSUserInput::FILTER_URL);
            }
            if (empty($sFailureURL)) {
                $user = $this->getExtranetUserProvider()->getActiveUser();
                if (null !== $user) {
                    $sFailureURL = $user->GetLinkForRegistrationGuest();
                }
            }
        }
        $oStep = TdbShopOrderStep::GetStep('thankyou');
        if (null === $oStep) {
            $this->getRedirectService()->redirect($sFailureURL, Response::HTTP_FOUND, true);
        }

        $lastUserOrderedInSession = $oStep->GetLastUserBoughtFromSession();

        if (null === $lastUserOrderedInSession) {
            $this->getRedirectService()->redirect($sFailureURL, Response::HTTP_FOUND, true);
        }

        $aData = $this->GetFilteredUserData('aUser');
        if (false === isset($aData['password'], $aData['password2'])) {
            $this->getRedirectService()->redirect($sFailureURL, Response::HTTP_FOUND, true);
        }

        foreach ($aData as $key => $val) {
            $lastUserOrderedInSession->sqlData[$key] = trim($val);
        }
        $aData = $lastUserOrderedInSession->sqlData;

        $extranetUserProvider = $this->getExtranetUserProvider();
        $extranetUserProvider->reset();
        $oUser = $extranetUserProvider->getActiveUser();
        $oUser->LoadFromRow($aData);
        if (true === ($this->ValidateUserData() && $this->ValidateUserLoginData())) {
            $oUserOrder = &TShopBasket::GetLastCreatedOrder();
            $sNewUserId = $oUser->Register();
            $this->UpdateUserAddress(null, null, true);
            $oUserOrder->AllowEditByAll(true);
            $oUserOrder->SaveFieldsFast(['data_extranet_user_id' => $sNewUserId]);
            $oUserOrder->AllowEditByAll(false);

            $oStep->RemoveLastUserBoughtFromSession();
            $this->getRedirectService()->redirect($sSuccessURL, Response::HTTP_FOUND, true);
        }

        $this->getRedirectService()->redirect($sFailureURL, Response::HTTP_FOUND, true);
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
        $activeStep = TdbShopOrderStep::GetStep('thankyou');

        if (null === $activeStep) {
            return false;
        }

        $lastUserOrderedInSession = $activeStep->GetLastUserBoughtFromSession();
        if (null === $lastUserOrderedInSession) {
            return false;
        }

        if (true === $lastUserOrderedInSession->LoginExists()) {
            $this->triggerUserAlreadyRegisteredMessage();

            return false;
        }

        $userOrder = &TShopBasket::GetLastCreatedOrder();
        if (null === $userOrder) {
            return false;
        }

        return $this->ValidateGivenUserData($lastUserOrderedInSession);
    }

    protected function triggerUserAlreadyRegisteredMessage(): void
    {
        $parameters = ['forgotPwdLinkStart' => '<a href="'.\TdbDataExtranet::GetInstance()->GetLinkForgotPasswordPage().'">', 'forgotPwdLinkEnd' => '</a>'];
        $this->getFlashMessageService()->addMessage(self::MSG_CONSUMER_LOGIN_PAGE, 'ERROR-USER-EXISTS', $parameters);
    }

    /**
     * validate the extranet user data (in the current extranet object).
     *
     * @return bool
     */
    protected function ValidateGivenUserData($oUser)
    {
        $userDataValid = true;

        $flashMessageService = $this->getFlashMessageService();

        $aRequiredFields = ['data_extranet_salutation_id', 'firstname', 'lastname', 'street', 'postalcode', 'city', 'data_country_id'];
        foreach ($aRequiredFields as $sField) {
            $sValue = trim($oUser->sqlData[$sField]);

            if (!array_key_exists($sField, $oUser->sqlData) || empty($sValue)) {
                $userDataValid = false;
                $flashMessageService->addMessage(TdbDataExtranetUser::MSG_FORM_FIELD.'-'.$sField, 'ERROR-USER-REQUIRED-FIELD-MISSING');
            }
        }

        return $userDataValid;
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

    private function getRedirectService(): ICmsCoreRedirect
    {
        return ServiceLocator::get('chameleon_system_core.redirect');
    }

    private function getRequestInfoService(): RequestInfoServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.request_info_service');
    }

    private function getFlashMessageService(): FlashMessageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.flash_messages');
    }

    private function getSystemPageService(): SystemPageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.system_page_service');
    }
}
