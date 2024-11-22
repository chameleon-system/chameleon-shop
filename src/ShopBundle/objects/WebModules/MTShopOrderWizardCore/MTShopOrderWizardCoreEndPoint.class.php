<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;

/**
 * the order wizard coordinates the different steps a user needs to complete to execute an order.
 * Note: The class could have extended from MTWizardCore, however the shop ordering process
 * was deemed so imported, that a new class was conceived to prevent changes in MTWizardCore from
 * leaking into the process.
 */
class MTShopOrderWizardCoreEndPoint extends TShopUserCustomModelBase
{
    /**
     * @var TdbShopOrderStep
     */
    protected $oActiveOrderStep;

    protected $bAllowHTMLDivWrapping = true;

    const URL_PARAM_STEP_SYSTEM_NAME = 'stpsysname';
    const URL_PARAM_STEP_METHOD = 'orderstepmethod';
    const URL_PARAM_MODULE_SPOT = 'spot';

    const SESSION_PARAM_NAME = 'MTShopOrderWizardCoreSession';

    public function Init()
    {
        parent::Init();

        $inputFilterUtil = $this->getInputFilterUtil();

        // load current step
        $sStepName = $inputFilterUtil->getFilteredInput(self::URL_PARAM_STEP_SYSTEM_NAME);

        if (TdbShopOrderStep::OrderProcessHasBeenMarkedAsCompleted() && 'thankyou' != $sStepName) {
            // the order has been successfully executed... we redirect to the thank you page
            // this only happens, if the user multi-klicks the send order button.
            TTools::WriteLogEntry("User multi-clicked on order confirm button. auto redirecting from step [{$sStepName}] to step [thankyou]", 3, __FILE__, __LINE__);
            TdbShopOrderStep::MarkOrderProcessAsCompleted();
            $oStep = TdbShopOrderStep::GetStep('thankyou');
            $oStep->JumpToStep($oStep);
        }

        $this->oActiveOrderStep = TdbShopOrderStep::GetStep($sStepName);

        // if the order has been created, and we are not on the final step, then we jump to that step
        if (is_null($this->oActiveOrderStep)) {
            // order step not found... go back to the calling step, but write a message
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage(TCMSMessageManager::GLOBAL_CONSUMER_NAME, 'SYSTEM-ERROR-SHOP-ORDER-STEP-NOT-DEFINED', array('target' => $sStepName, 'calling' => TdbShopOrderStep::GetCallingStepName()));
            $sStepName = TdbShopOrderStep::GetCallingStepName();
            $this->oActiveOrderStep = TdbShopOrderStep::GetStep($sStepName);
        }
        if (!is_null($this->oActiveOrderStep)) {
            $this->oActiveOrderStep->bIsTheActiveStep = true;
            $this->oActiveOrderStep->Init();
        }

        //TdbShopPaymentHandler::SetExecutePaymentInterrupt(true);
        if (TdbShopPaymentHandler::ExecutePaymentInterrupted()) {
            TTools::WriteLogEntry('Return from ExecutePaymentInterrupted()', 4, __FILE__, __LINE__);
            TdbShopPaymentHandler::SetExecutePaymentInterrupt(false);
            if (array_key_exists(TShopBasket::SESSION_KEY_PROCESSING_BASKET, $_SESSION)) {
                unset($_SESSION[TShopBasket::SESSION_KEY_PROCESSING_BASKET]);
            }
            // continue payment...
            $oOrder = TShopBasket::GetLastCreatedOrder();
            $oBasket = TShopBasket::GetInstance();
            //$oPaymentHandler
            $oActivePaymentMethod = $oBasket->GetActivePaymentMethod();
            if ($oActivePaymentMethod) {
                $oPaymentHandler = $oActivePaymentMethod->GetFieldShopPaymentHandler();
                if ($oPaymentHandler) {
                    try {
                        if ($oPaymentHandler->postExecutePaymentInterruptedHook($oOrder, TCMSMessageManager::GLOBAL_CONSUMER_NAME) && $oBasket->OnPaymentSuccessHook($oOrder, $oPaymentHandler, TCMSMessageManager::GLOBAL_CONSUMER_NAME)) {
                            $oOrder->CreateOrderInDatabaseCompleteHook(); // since the order process was interrupted by the external payment process, we need to call the method here again
                            TTools::WriteLogEntry('completed OnPaymentSuccessHook with success', 4, __FILE__, __LINE__);
                            // jump to thank you page...
                            TdbShopOrderStep::MarkOrderProcessAsCompleted();
                            $oPaymentHandler->BeforeJumpToThankYouStepAfterInterruptedPaymentHook();
                            $oStep = TdbShopOrderStep::GetStep('thankyou');
                            $oStep->JumpToStep($oStep);
                        } else {
                            TTools::WriteLogEntry('Unable to complete payment (OnPaymentSuccessHook) for order: '.print_r($oOrder->sqlData, true), 2, __FILE__, __LINE__);
                            // we need the option to redirect to a different page based on the payment selected
                            $oPaymentHandler->OnPaymentErrorAfterInterruptedPaymentHook();
                        }
                    } catch (TPkgCmsException_LogAndMessage $e) {
                        $oMsgManager = TCMSMessageManager::GetInstance();
                        $oMsgManager->AddMessage(TCMSMessageManager::GLOBAL_CONSUMER_NAME, $e->getMessageCode(), $e->getAdditionalData());
                        $oOrder->SetStatusCanceled(true);
                        $oPaymentHandler->OnPaymentErrorAfterInterruptedPaymentHook();
                    }
                } else {
                    TTools::WriteLogEntry('Error: Unable to get payment handler from payment method: '.print_r($oActivePaymentMethod, true), 1, __FILE__, __LINE__);
                }
            } else {
                TTools::WriteLogEntry('Error: Unable to get payment method from basket: '.print_r($oBasket, true), 1, __FILE__, __LINE__);
            }

            // unlock the basket again.
            $oBasket = TShopBasket::GetInstance();
            $oBasket->UnlockBasket();
        }
    }

    public function Execute()
    {
        parent::Execute();
        $this->data['oActiveOrderStep'] = $this->oActiveOrderStep;

        $this->data['oSteps'] = TdbShopOrderStepList::GetNavigationStepList($this->oActiveOrderStep);
        $this->data['sBasketRequestURL'] = self::GetCallingURL();

        return $this->data;
    }

    /**
     * return the url to the page that requested the order step.
     *
     * @return string
     */
    public static function GetCallingURL()
    {
        $sURL = '/';
        if (array_key_exists(self::SESSION_PARAM_NAME, $_SESSION)) {
            $sURL = $_SESSION[self::SESSION_PARAM_NAME];
        } else {
            $sURL = self::getPageService()->getLinkToPortalHomePageAbsolute();
        }

        return $sURL;
    }

    /**
     * save the url in the session so we can use it later to return to the calling page.
     *
     * @param string $sURL
     *
     * @return void
     */
    public static function SetCallingURL($sURL)
    {
        if (!empty($sURL)) {
            $_SESSION[self::SESSION_PARAM_NAME] = $sURL;
        }
    }

    /**
     * run a method on the step. default is ExecuteStep, but can be overwritten
     * by passing the parameter $sStepMethod (if null is passed, the method will try to fetch
     * the value from get/post from self::URL_PARAM_STEP_METHOD = xx.
     *
     * @param string|null $sStepMethod - method to execute. defaults to ExecuteStep
     *
     * @return false|null
     */
    public function ExecuteStep($sStepMethod = null)
    {
        if (is_null($this->oActiveOrderStep)) {
            return false;
        } // stop if we have no active step

        $inputFilterUtil = $this->getInputFilterUtil();

        if (is_null($sStepMethod)) {
            if ($inputFilterUtil->getFilteredInput(self::URL_PARAM_STEP_METHOD)) {
                $sStepMethod = $inputFilterUtil->getFilteredInput(self::URL_PARAM_STEP_METHOD);
            }
        }
        if (is_null($sStepMethod) || false === $sStepMethod || empty($sStepMethod)) {
            $sStepMethod = 'ExecuteStep';
        }
        // check if the method is permitted
        $aAllowedMethod = $this->oActiveOrderStep->AllowedMethods();
        if (in_array($sStepMethod, $aAllowedMethod) && method_exists($this->oActiveOrderStep, $sStepMethod)) {
            $this->oActiveOrderStep->$sStepMethod();
        } else {
            // error - method not allowed
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($this->sModuleSpotName, 'SYSTEM-ERROR-SHOP-ORDER-STEP-CALLED-METHOD-NOT-ALLOWED', array('methodName' => $sStepMethod));
        }
    }

    /**
     * define any head includes the step needs.
     *
     * @return string[]
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        if (!is_array($aIncludes)) {
            $aIncludes = array();
        }
        if (!is_null($this->oActiveOrderStep)) {
            $aStepIncludes = $this->oActiveOrderStep->GetHtmlHeadIncludes();
            if (count($aStepIncludes) > 0) {
                $aIncludes = array_merge($aIncludes, $aStepIncludes);
            }
        }

        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgShop/shopBasket'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgShop/OrderSteps'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgShop/shopBasket/mails'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgShop/shippingAndPayment'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgShop/shippingAndPayment/handler'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgExtranet/address'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('common/userInput'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('common/userInput/form'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('common/textBlock'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('common/navigation'));

        return $aIncludes;
    }

    public function GetHtmlFooterIncludes()
    {
        $aIncludes = parent::GetHtmlFooterIncludes();
        if (!is_array($aIncludes)) {
            $aIncludes = array();
        }
        if (!is_null($this->oActiveOrderStep)) {
            $aStepIncludes = $this->oActiveOrderStep->GetHtmlFooterIncludes();
            if (count($aStepIncludes) > 0) {
                $aIncludes = array_merge($aIncludes, $aStepIncludes);
            }
        }

        return $aIncludes;
    }

    /**
     * add your custom methods as array to $this->methodCallAllowed here
     * to allow them to be called from web.
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'ExecuteStep';
        $this->methodCallAllowed[] = 'PostProcessExternalPaymentHandlerHook';
        $this->methodCallAllowed[] = 'JumpSelectPaymentMethod';
        $this->methodCallAllowed[] = 'GetStepAsAjax';
    }

    /**
     * return the step passed as ajax.
     *
     * @param string $sStepName
     *
     * @return string
     */
    protected function GetStepAsAjax($sStepName = null)
    {
        $inputFilterUtil = $this->getInputFilterUtil();
        $sHTML = '';
        if (is_null($sStepName)) {
            $sStepName = $inputFilterUtil->getFilteredInput('sStepName');
        }
        $oStep = TdbShopOrderStep::GetStep($sStepName);
        if ($oStep) {
            $sModuleSpotName = $this->sModuleSpotName;
            $sHTML = $oStep->Render($sModuleSpotName);
        }

        return $sHTML;
    }

    /**
     * the method should be called when returning from an external payment handler such as paypal
     * note: make sure you pass the session to the URL that is called by the external handler when the process is completed!
     *
     * @return void
     */
    protected function PostProcessExternalPaymentHandlerHook()
    {
        $oBasket = TShopBasket::GetInstance();
        if ($oBasket->GetActivePaymentMethod()) {
            if ($oBasket->GetActivePaymentMethod()->PostProcessExternalPaymentHandlerHook()) {
                $oBasket->aCompletedOrderStepList[$this->oActiveOrderStep->fieldSystemname] = true;
                // all good.... jump to next step
                $oNextStep = $this->oActiveOrderStep->GetNextStep();
                $oNextStep->JumpToStep($oNextStep);
            } else {
                TTools::WriteLogEntry("PostProcessExternalPaymentHandlerHook: called 'PostProcessExternalPaymentHandlerHook' on the payment method - and got false", 1, __FILE__, __LINE__);
            }
        } else {
            TTools::WriteLogEntry('PostProcessExternalPaymentHandlerHook: no active payment method found', 1, __FILE__, __LINE__);
        }
    }

    /**
     * executes the post select payment hook for the given payment method. you can pass either the id
     * or the internal name of the payment method.
     *
     * @param string $sPaymentMethodId
     * @param string $sPaymentMethodNameInternal
     *
     * @return bool
     */
    protected function JumpSelectPaymentMethod($sPaymentMethodId = null, $sPaymentMethodNameInternal = null)
    {
        $oPaymentMethod = null;
        $inputFilterUtil = $this->getInputFilterUtil();
        if (is_null($sPaymentMethodId)) {
            $sPaymentMethodId = $inputFilterUtil->getFilteredInput('sPaymentMethodId');
        }
        if (empty($sPaymentMethodId)) {
            $oPaymentMethod = TdbShopPaymentMethod::GetNewInstance();
            if (is_null($sPaymentMethodNameInternal)) {
                $sPaymentMethodNameInternal = $inputFilterUtil->getFilteredInput('sPaymentMethodNameInternal');
            }
            $oPaymentMethod->LoadFromField('name_internal', $sPaymentMethodNameInternal);
        }

        if (is_null($oPaymentMethod)) {
            $oPaymentMethod = TdbShopPaymentMethod::GetNewInstance();
            $oPaymentMethod->Load($sPaymentMethodId);
        }

        if ($oPaymentMethod->IsAvailable()) {
            $oBasket = TShopBasket::GetInstance();
            $oBasket->SetActivePaymentMethod($oPaymentMethod);

            // check if payment method was set
            $oCheckPayment = $oBasket->GetActivePaymentMethod();
            if (!$oCheckPayment) {
                TTools::WriteLogEntry('JumpSelectPaymentMethod: unable to select payment method', 1, __FILE__, __LINE__);
            }

            // also set shipping type if not already set
            $oActiveShippingGroup = $oBasket->GetActiveShippingGroup();
            if (is_null($oActiveShippingGroup) || !$oPaymentMethod->isConnected('shop_shipping_group', $oActiveShippingGroup->id)) {
                $oMatchingShippingGroup = TdbShopShippingGroupList::GetShippingGroupsThatAllowPaymentWith($oPaymentMethod->fieldNameInternal);
                $oBasket->SetActiveShippingGroup($oMatchingShippingGroup);
                $oBasket->RecalculateBasket();
            }

            $oPaymentMethod->GetFieldShopPaymentHandler()->PostSelectPaymentHook(TCMSMessageManager::GLOBAL_CONSUMER_NAME);

            return true;
        } else {
            trigger_error('trying to access an unavailable payment method through JumpSelect', E_USER_WARNING);

            return false;
        }
    }

    public function _AllowCache()
    {
        return false;
    }

    /**
     * @return PageServiceInterface
     */
    private static function getPageService()
    {
        return ServiceLocator::get('chameleon_system_core.page_service');
    }

    /**
     * @return InputFilterUtilInterface
     */
    private function getInputFilterUtil()
    {
        return ServiceLocator::get('chameleon_system_core.util.input_filter');
    }
}
