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
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class TShopStepConfirmCore extends TdbShopOrderStep
{
    /**
     * method is called from the init method of the calling module. here you can check
     * if the step may be viewed, and redirect to another step if the user does not have permission.
     */
    public function Init()
    {
        // check basket amount.. remove items that may not be ordered
        $basket = $this->getShopService()->getActiveBasket();
        $global = TGlobal::instance();
        $basket->ValidateBasketContents($global->GetExecutingModulePointer()->sModuleSpotName);
        parent::Init();
    }

    /**
     * returns true if the user may view the step.
     *
     * @param bool $bRedirectToPreviousPermittedStep
     *
     * @return bool
     */
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        $bAllowAccess = parent::AllowAccessToStep($bRedirectToPreviousPermittedStep);
        $shopService = $this->getShopService();
        $oBasket = $shopService->getActiveBasket();
        // validate previous steps...
        $oActiveBasketPaymentMethod = $oBasket->GetActivePaymentMethod();
        $oActiveBasketShippingGroup = $oBasket->GetActiveShippingGroup();
        $bShippingCompleted = (array_key_exists('shipping', $oBasket->aCompletedOrderStepList) && true == $oBasket->aCompletedOrderStepList['shipping']);
        $bShippingCompleted = ($bShippingCompleted && !is_null($oActiveBasketPaymentMethod));
        $bShippingCompleted = ($bShippingCompleted && !is_null($oActiveBasketShippingGroup));
        $bAllowAccess = parent::AllowAccessToStep($bRedirectToPreviousPermittedStep);
        $oBasket = $shopService->getActiveBasket();
        $bUserDataCompleted = (array_key_exists('user', $oBasket->aCompletedOrderStepList) && true == $oBasket->aCompletedOrderStepList['user']);

        if ($oBasket->iTotalNumberOfUniqueArticles <= 0) {
            $bAllowAccess = false;
            if ($bRedirectToPreviousPermittedStep) {
                $oBasketStep = &TdbShopOrderStep::GetStep('basket');
                $this->JumpToStep($oBasketStep);
            }
        }

        $oUser = $this->getExtranetUserProvider()->getActiveUser();

        if (false === $this->getShopService()->getActiveShop()->allowPurchaseAsGuest()) {
            if (false === $oUser->IsLoggedIn()) {
                $bAllowAccess = false;
            }
        }

        if ($bAllowAccess && ($oUser->IsLoggedIn() || $bUserDataCompleted)) {
            // check to make sure we have a billing addres for the user
            $oBillingAdr = $oUser->GetBillingAddress();
            if (is_null($oBillingAdr) || !$oBillingAdr->ContainsData()) {
                $bUserDataCompleted = false;
            } else {
                $bUserDataCompleted = true;
            }

            if (!$bUserDataCompleted) {
                $bAllowAccess = false;
                if ($bRedirectToPreviousPermittedStep) {
                    $oUserStep = &TdbShopOrderStep::GetStep('user');
                    $this->JumpToStep($oUserStep);
                }
            }
        }

        if (!$bShippingCompleted) {
            $bAllowAccess = false;
            if ($bRedirectToPreviousPermittedStep) {
                $oMsgManager = $this->getFlashMessages();
                if (false == $oMsgManager->consumerHasMessages(TCMSMessageManager::GLOBAL_CONSUMER_NAME)) {
                    $oMsgManager->addMessage(TCMSMessageManager::GLOBAL_CONSUMER_NAME, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => TGlobal::Translate('chameleon_system_shop.error.generic_payment_shipping_error')));
                }
                $oUserStep = &TdbShopOrderStep::GetStep('shipping');
                $this->JumpToStep($oUserStep);
            }
        }

        return $bAllowAccess;
    }

    /**
     * define any methods of the class that may be called via get or post.
     *
     * @return array
     */
    public function AllowedMethods()
    {
        $externalFunctions = parent::AllowedMethods();
        if (!is_array($externalFunctions)) {
            $externalFunctions = array();
        }
        $externalFunctions[] = 'ConfirmRemotePayment';

        return $externalFunctions;
    }

    public function ConfirmRemotePayment()
    {
        $oGlobal = TGlobal::instance();
        $oActivePage = $this->getActivePageService()->getActivePage();
        $sErrorCode = $oGlobal->GetUserData('ERRORCODE');
        $this->SaveInExportLog();
        if (empty($sErrorCode)) {
            $sUrl = $oActivePage->GetRealURLPlain(array('module_fnc' => array('spota' => 'ExecuteStep'), 'aInput' => array('agb' => 'true')), true);
        } else {
            $sUrl = $oActivePage->GetRealURLPlain(array(), true);
            $sErrorMessage = $oGlobal->GetUserData('ERRORMESSAGE');
            $sErrorReason = $oGlobal->GetUserData('ERRORREASON');
            $oMessageMessenger = $this->getFlashMessages();
            $oMessageMessenger->addMessage(MTShopBasketCore::MSG_CONSUMER_NAME.'-remotepayment', 'ERROR-CONFIRM-PAYMENT', array('errorcode' => $sErrorCode, 'errormessage' => $sErrorMessage, 'errorreason' => $sErrorReason));
        }
        $this->getRedirect()->redirect($sUrl);
    }

    protected function SaveInExportLog()
    {
        /** @var Request $request */
        $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();

        $oExportLog = TdbShopOrderExportLog::GetNewInstance();
        $oExportLog->sqlData['datecreated'] = date('Y-m-d H:i:s');
        $oExportLog->sqlData['data'] = serialize($request);
        $oExportLog->sqlData['ip'] = $request->getClientIp();
        $oExportLog->sqlData['user_session_id'] = session_id();
        $oExportLog->AllowEditByAll(true);
        $oExportLog->Save();
        $oExportLog->AllowEditByAll(false);
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
    protected function &GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = &parent::GetAdditionalViewVariables($sViewName, $sViewType);
        $aViewVariables['oUser'] = $this->getExtranetUserProvider()->getActiveUser();
        $aViewVariables['oExtranetConfig'] = &TdbDataExtranet::GetInstance();
        $aViewVariables['oBasket'] = $this->getShopService()->getActiveBasket();

        $oUserStep = TdbShopOrderStep::GetStep('user');
        $aViewVariables['sLinkUserData'] = $oUserStep->GetStepURL();
        $aViewVariables['sLinkShippingAddress'] = $aViewVariables['sLinkUserData'];
        $oShippingStep = TdbShopOrderStep::GetStep('shippingaddress');
        if ($oShippingStep) {
            $aViewVariables['sLinkShippingAddress'] = $oShippingStep->GetStepURL();
        }

        $oShippingStep = TdbShopOrderStep::GetStep('shipping');
        $aViewVariables['sLinkShipping'] = $oShippingStep->GetStepURL();

        return $aViewVariables;
    }

    /**
     * called by the ExecuteStep Method - place any logic for the standard proccessing of this step here
     * return false if any errors occure (returns the user to the current step for corrections).
     *
     * @return bool
     */
    protected function ProcessStep()
    {
        $continue = parent::ProcessStep();

        $basket = $this->getShopService()->getActiveBasket();
        /**
         * If the user managed to submit the confirmation form twice, we need to avoid handling the second submit.
         * The parent's ProcessStep() method will have delayed the second click until the first request is processed
         * completely by locking the session, so that we can check if the order has already been completed.
         * If yes, we simply forward to the next step (most likely the thank-you page).
         */
        if (0 === $basket->dTotalNumberOfArticles && TdbShopOrderStep::OrderProcessHasBeenMarkedAsCompleted()) {
            $this->JumpToStep($this->GetNextStep());
        }

        // check AGB
        $input = $this->getInputFilterUtil()->getFilteredPostInput('aInput');
        if (false === is_array($input)) {
            $input = array();
        }
        $agb = 'false';
        if (array_key_exists('agb', $input)) {
            $agb = $input['agb'];
        }
        if ('true' !== $agb) {
            $this->getFlashMessages()->addMessage(MTShopBasketCore::MSG_CONSUMER_NAME.'-agb', 'ERROR-CONFIRM-AGB');
            $continue = false;
        }

        $user = $this->getExtranetUserProvider()->getActiveUser();
        $continue = $basket->GetActivePaymentMethod()->processConfirmOrderUserResponse($user, $input) && $continue;

        if ($continue) {
            $this->addDataToBasket($basket);
            // if we fail here, we need to jump back to the current step so any data is rechecked
            if (false === $basket->CreateOrder(MTShopBasketCore::MSG_CONSUMER_NAME)) {
                $this->JumpToStep($this);
            } else {
                $this->PostProcessHookOnOrderCreateSuccess();
            }
        }

        return $continue;
    }

    /**
     * method is called if the order creation process is completed successfully
     * use this hook if you want to perform any actions after order creation
     * based on user into in the last order step (if you want to modify the order based on
     * basket data, use the hook within the order object instead).
     */
    protected function PostProcessHookOnOrderCreateSuccess()
    {
        TdbShopOrderStep::MarkOrderProcessAsCompleted();
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return FlashMessageServiceInterface
     */
    private function getFlashMessages()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.flash_messages');
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
