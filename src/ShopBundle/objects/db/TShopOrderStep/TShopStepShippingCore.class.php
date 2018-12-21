<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;

class TShopStepShippingCore extends TdbShopOrderStep
{
    /**
     * the selected shipping group.
     *
     * @var TdbShopShippingGroup
     */
    protected $oActiveShippingGroup = null;

    /**
     * selected payment group.
     *
     * @var TdbShopPaymentMethod
     */
    protected $oActivePaymentMethod = null;
    protected $aRequestData = array();

    const MSG_SHIPPING_GROUP = 'shippinggroupmessage';
    const MSG_PAYMENT_METHOD = 'paymentmessage';

    /**
     * method is called from the init method of the calling module. here you can check
     * if the step may be viewed, and redirect to another step if the user does not have permission.
     */
    public function Init()
    {
        parent::Init();
        $inputFilterUtil = $this->getInputFilterUtil();
        $shippingData = $inputFilterUtil->getFilteredPostInput('aShipping');
        if (null !== $shippingData) {
            $this->aRequestData = $shippingData;
            if (!is_array($this->aRequestData)) {
                $this->aRequestData = array();
            }
        }
        $this->ChangeShippingGroup();
        $this->LoadActivePaymentMethod();
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
        $oBasket = $this->getShopService()->getActiveBasket();
        $bUserDataCompleted = (array_key_exists('user', $oBasket->aCompletedOrderStepList) && true == $oBasket->aCompletedOrderStepList['user']);

        if ($oBasket->iTotalNumberOfUniqueArticles <= 0) {
            $bAllowAccess = false;
            if ($bRedirectToPreviousPermittedStep) {
                $oBasketStep = &TdbShopOrderStep::GetStep('basket');
                $this->JumpToStep($oBasketStep);
            }
        }

        $user = $this->getExtranetUserProvider()->getActiveUser();

        if (false === $this->getShopService()->getActiveShop()->allowPurchaseAsGuest()) {
            if (false === $user->IsLoggedIn()) {
                $bAllowAccess = false;
            }
        }

        $oBillingAdr = $user->GetBillingAddress();
        if ($bAllowAccess && ($user->HasData() && (($user->IsLoggedIn() && $oBillingAdr->ValidateData('')) || $bUserDataCompleted))) {
            // check to make sure we have a billing addres for the user

            $oCountry = $oBillingAdr->GetFieldDataCountry();
            if (is_null($oBillingAdr) || !$oBillingAdr->ContainsData() || null === $oCountry || !$oCountry->isActive()) {
                $bUserDataCompleted = false;
            } else {
                $bUserDataCompleted = true;
            }

            if (!$user->ShipToBillingAddress()) {
                $oShippingAddress = $user->GetShippingAddress();
                $oShippingCountry = null;
                if ($oShippingAddress) {
                    $oShippingCountry = $oShippingAddress->GetFieldDataCountry();
                }
                if (null === $oShippingCountry || !$oShippingCountry->isActive()) {
                    $bUserDataCompleted = false;
                }
            }

            if (!$bUserDataCompleted) {
                $bAllowAccess = false;
                if ($bRedirectToPreviousPermittedStep) {
                    $oUserStep = &TdbShopOrderStep::GetStep('user');
                    $this->JumpToStep($oUserStep);
                }
            }
        } else {
            $bAllowAccess = false;
            if ($bRedirectToPreviousPermittedStep) {
                $oUserStep = &TdbShopOrderStep::GetStep('user');
                $this->JumpToStep($oUserStep);
            }
        }

        return $bAllowAccess;
    }

    /**
     * called by the ExecuteStep Method - place any logic for the standard proccessing of this step here
     * return false if any errors occure (returns the user to the current step for corrections).
     *
     * @return bool
     */
    protected function ProcessStep()
    {
        $bContinue = parent::ProcessStep();
        $oMsgManager = TCMSMessageManager::GetInstance();

        // we may continue only if a shipping group and payment method is set
        if ($bContinue && is_null($this->oActiveShippingGroup)) {
            $oMsgManager->AddMessage(self::MSG_SHIPPING_GROUP, 'ERROR-BASKET-NO-SHIPPING-GROUP');
            $bContinue = false;
        }
        if ($bContinue && is_null($this->oActivePaymentMethod)) {
            $oMsgManager->AddMessage(self::MSG_PAYMENT_METHOD, 'ERROR-BASKET-NO-PAYMENT-METHOD');
            $bContinue = false;
        }

        $oPaymentHandler = null;
        if ($bContinue) {
            // check payment method data
            $oPaymentHandler = &$this->oActivePaymentMethod->GetFieldShopPaymentHandler();
            $bContinue = $oPaymentHandler->ValidateUserInput();
        }

        if ($bContinue) {
            // set payment method and shipping group in basket
            $oBasket = TShopBasket::GetInstance();
            $oBasket->SetActiveShippingGroup($this->oActiveShippingGroup);
            $oBasket->SetActivePaymentMethod($this->oActivePaymentMethod);
            $oBasket->RecalculateBasket();
            $bContinue = $this->postSelectPaymentHook();

            // if things fail here, we need to redirect to the active step to clear our parameter list - and refresh the payment list
            if (false === $bContinue) {
                $this->JumpToStep($this);
            }
        }

        return $bContinue;
    }

    /**
     * @return bool
     */
    protected function postSelectPaymentHook()
    {
        return $this->oActivePaymentMethod->postSelectPaymentHook(
            TdbShop::GetInstance(),
            TShopBasket::GetInstance(),
            TdbDataExtranetUser::GetInstance(),
            self::MSG_PAYMENT_METHOD
        );
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
        $aViewVariables['oUser'] = TdbDataExtranetUser::GetInstance();

        $aViewVariables['oExtranetConfig'] = &TdbDataExtranet::GetInstance();

        $oShippingGroupList = &$this->GetAvailableShippingGroups();
        $aViewVariables['oShippingGroupList'] = &$oShippingGroupList;
        $aViewVariables['oActiveShippingGroup'] = &$this->oActiveShippingGroup;
        $aViewVariables['oActivePaymentMethod'] = &$this->oActivePaymentMethod;

        $aViewVariables['oPaymentMethods'] = TShopBasket::GetInstance()->GetValidPaymentMethodsSelectableByTheUser();

        return $aViewVariables;
    }

    /**
     * returns a list of shipping groups that are available to the current user
     * with the selected basket.
     *
     * @return TdbShopShippingGroupList
     */
    protected function &GetAvailableShippingGroups()
    {
        return TdbShopShippingGroupList::GetAvailableShippingGroups();
    }

    /**
     * changes the shipping group to the one passed via post or get in
     * shippinggroupid=id.
     */
    public function ChangeShippingGroup()
    {
        $oBasket = TShopBasket::GetInstance();
        $this->oActiveShippingGroup = $oBasket->GetActiveShippingGroup();
        // check if the group is still valid. if not, reload using default
        if (!$this->oActiveShippingGroup || false == $this->oActiveShippingGroup->IsAvailable()) {
            $oBasket->SetActiveShippingGroup(null);
            $oBasket->SetBasketRecalculationFlag(true);
            $this->oActiveShippingGroup = $oBasket->GetActiveShippingGroup();
        }
        $iShippingGroupId = null;
        if (array_key_exists('shop_shipping_group_id', $this->aRequestData)) { //shop_payment_methode_id?
            $iShippingGroupId = $this->aRequestData['shop_shipping_group_id'];
            /** @var $oShippingGroup TdbShopShippingGroup */
            $this->oActiveShippingGroup = TdbShopShippingGroup::GetNewInstance();
            if (!$this->oActiveShippingGroup->Load($iShippingGroupId)) {
                // if the requested group was not found, fetch default from basket again
                if (is_null($this->oActiveShippingGroup)) {
                    $this->oActiveShippingGroup = $oBasket->GetActiveShippingGroup();
                }
            } else {
                $oBasket->SetActiveShippingGroup($this->oActiveShippingGroup);
                $oBasket->SetBasketRecalculationFlag(true);
            }
        }
    }

    /**
     * loads the active payment method.
     */
    protected function LoadActivePaymentMethod()
    {
        $oBasket = TShopBasket::GetInstance();
        $this->oActivePaymentMethod = &$oBasket->GetActivePaymentMethod();
        if (!is_null($this->oActiveShippingGroup)) {
            $iPaymentId = null;
            $bSelectionValid = true;
            if (array_key_exists('shop_payment_method_id', $this->aRequestData)) {
                $iPaymentId = $this->aRequestData['shop_payment_method_id'];
                // make sure the entry may be selected by the user
                $bSelectionValid = false;
                $oUserSelectablePaymentMethods = $this->oActiveShippingGroup->GetValidPaymentMethodsSelectableByTheUser();
                if (true == $oUserSelectablePaymentMethods->FindItemWithProperty('id', $iPaymentId)) {
                    if (is_null($this->oActivePaymentMethod) || !is_array($this->oActivePaymentMethod->sqlData) || $this->oActivePaymentMethod->id != $iPaymentId) {
                        $this->oActivePaymentMethod = TdbShopPaymentMethod::GetNewInstance();
                        if (!$this->oActivePaymentMethod->Load($iPaymentId)) {
                            $this->oActivePaymentMethod = null;
                        } else {
                            $bSelectionValid = true;
                        }
                    } else {
                        $bSelectionValid = true;
                    }
                }
            }
            if (true == $bSelectionValid) {
                // found one? make sure it is in the active shipping group
                $oList = $oBasket->GetAvailablePaymentMethods();
                if (!is_null($this->oActivePaymentMethod)) {
                    if (!$oList->IsInList($this->oActivePaymentMethod->id)) {
                        $this->oActivePaymentMethod = null;
                    }
                }

                // still nothing? just take the first one from the list
                if (is_null($this->oActivePaymentMethod)) {
                    $oList->GoToStart();
                    if ($oList->Length() > 0) {
                        $this->oActivePaymentMethod = &$oList->Current();
                    }
                }
            }
        } else {
            $this->oActivePaymentMethod = null;
        }
    }

    /**
     * define any methods of the class that may be called via get or post.
     *
     * @return array
     */
    public function AllowedMethods()
    {
        $aExternalFunctions = parent::AllowedMethods();
        if (!is_array($aExternalFunctions)) {
            $aExternalFunctions = array();
        }

        $aExternalFunctions[] = 'ChangeShippingGroup';

        return $aExternalFunctions;
    }

    /**
     * define any head includes the step needs
     * loads includes from payment handlers.
     *
     * @return array
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();

        if (isset($this->oActiveShippingGroup)) {
            $oPaymentMethodList = TShopBasket::GetInstance()->GetAvailablePaymentMethods();
            if ($oPaymentMethodList) {
                while ($oPaymentMethod = $oPaymentMethodList->Next()) {
                    $oPaymentHandler = $oPaymentMethod->GetFieldShopPaymentHandler();

                    if (method_exists($oPaymentHandler, 'GetHtmlHeadIncludes')) {
                        $aAdditionalIncludes = $oPaymentHandler->GetHtmlHeadIncludes();
                        if (count($aAdditionalIncludes) > 0) {
                            $aIncludes = array_merge($aIncludes, $aAdditionalIncludes);
                        }
                    }
                }
            }
        }

        return $aIncludes;
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
