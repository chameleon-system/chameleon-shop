<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ShopBundle\Exception\ConfigurationException;
use ChameleonSystem\ShopBundle\Interfaces\DataAccess\PaymentMethodDataAccessInterface;
use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * represents a payment method the user can choose from. it uses a payment handler to gather payment infos
 * and execute the payment.
/**/
class TShopPaymentMethod extends TShopPaymentMethodAutoParent implements IPkgShopVatable
{
    const VIEW_PATH = 'pkgShop/views/db/TShopPaymentMethod';

    protected $dPrice = null;
    /**
     * payment handler.
     *
     * @var TdbShopPaymentHandler
     */
    protected $oPaymentHandler = null;
    /**
     * we need this information to unserialize the payment handler.
     *
     * @var array
     */
    protected $aPaymentHandlerClassData = null;

    public function GetName()
    {
        $sName = parent::GetName();
        if (!TGlobal::IsCMSMode()) {
            $dCosts = $this->GetPrice();
            if (0 != $dCosts) {
                $oLocal = &TCMSLocal::GetActive();
                $sName .= ' ('.$oLocal->FormatNumber($dCosts, 2).' EUR)';
            }
        }

        return $sName;
    }

    /**
     * called after the payment method is selected - return false the selection is invalid.
     *
     * @param TdbShop             $oShop
     * @param TShopBasket         $oBasket
     * @param TdbDataExtranetUser $oUser
     * @param $sMessageConsumer
     *
     * @return bool
     */
    public function postSelectPaymentHook(TdbShop $oShop, TShopBasket $oBasket, TdbDataExtranetUser $oUser, $sMessageConsumer)
    {
        return $this->GetFieldShopPaymentHandler()->PostSelectPaymentHook($sMessageConsumer);
    }

    /**
     * return payment handler for method
     * Note: you should NOT use this function - use GetFieldShopPaymentHandler instead! this method is outdated!
     *
     * @deprecated
     *
     * @return TdbShopPaymentHandler
     */
    public function &GetPaymentHandler()
    {
        return $this->GetFieldShopPaymentHandler();
    }

    /**
     * Paymenthandler.
     *
     * @return TdbShopPaymentHandler
     */
    public function &GetFieldShopPaymentHandler()
    {
        if (is_null($this->oPaymentHandler)) {
            if (empty($this->fieldShopPaymentHandlerId)) {
                return $this->oPaymentHandler;
            }
            $activePortal = $this->getPortalDomainService()->getActivePortal();
            if (null === $activePortal) {
                return $this->oPaymentHandler;
            }
            try {
                $this->oPaymentHandler = $this->getShopPaymentHandlerFactory()->createPaymentHandler($this->fieldShopPaymentHandlerId, $activePortal->id);
            } catch (ConfigurationException $e) {
                $this->getLogger()->error(
                    sprintf('Unable to create payment handler: %s', $e->getMessage()),
                    [
                        'paymentHandlerId' => $this->fieldShopPaymentHandlerId,
                        'portalId' => $activePortal->id
                    ]
                );

                return $this->oPaymentHandler;
            }
        }

        return $this->oPaymentHandler;
    }

    /**
     * return true if this shipping type is valid for the current user / basket.
     *
     * @return bool
     */
    public function IsAvailable()
    {
        $bIsValid = true;
        $bIsValid = ($bIsValid && $this->IsActive());
        if (!$bIsValid) {
            return $bIsValid;
        }
        $oPaymentHandler = $this->GetFieldShopPaymentHandler();
        $bIsValid = ($bIsValid && !is_null($oPaymentHandler) && $oPaymentHandler->AllowUse($this));
        $bIsValid = ($bIsValid && $this->IsValidForCurrentUser());
        $bIsValid = ($bIsValid && $this->IsValidForBasket());

        return $bIsValid;
    }

    /**
     * returns true if the payment method has no user or user group restriction.
     *
     * @return bool
     */
    public function IsPublic()
    {
        $bIsPublic = true;

        if (!$this->IsActive()) {
            $bIsPublic = false;
        }

        if ($bIsPublic) {
            $oGroups = $this->GetFieldDataExtranetGroupList();
            if ($oGroups->Length() > 0) {
                $bIsPublic = false;
            }
        }

        if ($bIsPublic) {
            $oUsers = $this->GetFieldDataExtranetUserList();
            if ($oUsers->Length() > 0) {
                $bIsPublic = false;
            }
        }

        return $bIsPublic;
    }

    /**
     * returns true if the payment method is marked as active for the current time.
     *
     * @return bool
     */
    public function IsActive()
    {
        $bIsActive = false;
        if ($this->fieldActive) {
            $bIsActive = true;
        }

        return $bIsActive;
    }

    /**
     * return true if the payment method is allowed for the current user.
     *
     * @return bool
     */
    public function IsValidForCurrentUser()
    {
        $bIsValidForUser = false;
        $bIsValidShippingCountry = false;
        $bIsValidBillingCountry = false;

        $oUser = TdbDataExtranetUser::GetInstance();
        $bIsValidGroup = $this->checkUserGroup($oUser);
        if ($bIsValidGroup) {
            $bIsValidForUser = $this->checkUser($oUser);
        }
        if ($bIsValidForUser && $bIsValidGroup) {
            $bIsValidShippingCountry = $this->checkUserShippingCountry($oUser);
        }
        if ($bIsValidForUser && $bIsValidGroup && $bIsValidShippingCountry) {
            $bIsValidBillingCountry = $this->checkUserBillingCountry($oUser);
        }

        return $bIsValidForUser && $bIsValidGroup && $bIsValidShippingCountry && $bIsValidBillingCountry;
    }

    /**
     * check user group.
     *
     * @param TdbDataExtranetUser $oUser
     *
     * @return bool
     */
    protected function checkUserGroup(TdbDataExtranetUser $oUser)
    {
        $bIsValidGroup = false;
        $aUserGroups = $this->getPaymentMethodDataAccess()->getPermittedUserGroupIds($this->id);
        if (!is_array($aUserGroups) || count($aUserGroups) < 1) {
            $bIsValidGroup = true;
        } elseif ($oUser->IsLoggedIn()) {
            $bIsValidGroup = $oUser->InUserGroups($aUserGroups);
        }

        return $bIsValidGroup;
    }

    /**
     * now check user id.
     *
     * @param TdbDataExtranetUser $oUser
     *
     * @return bool
     */
    protected function checkUser(TdbDataExtranetUser $oUser)
    {
        $bIsValidForUser = false;
        $aUserList = $this->getPaymentMethodDataAccess()->getPermittedUserIds($this->id);
        if (!is_array($aUserList) || count($aUserList) < 1) {
            $bIsValidForUser = true;
        } elseif ($oUser->IsLoggedIn()) {
            $bIsValidForUser = in_array($oUser->id, $aUserList);
        }

        return $bIsValidForUser;
    }

    /**
     * check shipping country.
     *
     * @param TdbDataExtranetUser $oUser
     *
     * @return bool
     */
    protected function checkUserShippingCountry(TdbDataExtranetUser $oUser)
    {
        $bIsValidShippingCountry = false;
        $aShippingCountryRestriction = $this->getPaymentMethodDataAccess()->getShippingCountryIds($this->id);
        if (count($aShippingCountryRestriction) > 0) {
            $oShippingAddress = $oUser->GetShippingAddress();
            if (in_array($oShippingAddress->fieldDataCountryId, $aShippingCountryRestriction)) {
                $bIsValidShippingCountry = true;
            }
        } else {
            $bIsValidShippingCountry = true;
        }

        return $bIsValidShippingCountry;
    }

    /**
     * @return PaymentMethodDataAccessInterface
     */
    protected function getPaymentMethodDataAccess()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.payment_method_data_access');
    }

    /**
     * check billing country.
     *
     * @param TdbDataExtranetUser $oUser
     *
     * @return bool
     */
    protected function checkUserBillingCountry(TdbDataExtranetUser $oUser)
    {
        $bIsValidBillingCountry = false;
        $aShippingCountryRestriction = $this->getPaymentMethodDataAccess()->getBillingCountryIds($this->id);
        if (count($aShippingCountryRestriction) > 0) {
            $oBillingAddress = $oUser->GetBillingAddress();
            if (in_array($oBillingAddress->fieldDataCountryId, $aShippingCountryRestriction)) {
                $bIsValidBillingCountry = true;
            }
        } else {
            $bIsValidBillingCountry = true;
        }

        return $bIsValidBillingCountry;
    }

    /**
     * checks if the current payment method is available for the current basket
     * affected articles in the basket will be marked with the shipping type.
     *
     * @return bool
     */
    public function IsValidForBasket()
    {
        $bValidForBasket = false;

        $oBasket = TShopBasket::GetInstance();

        if (false === $oBasket->isTotalCostKnown()) {
            // check the total basket value
            if ($this->fieldRestrictToBasketValueFrom > $oBasket->dCostTotal || ($this->fieldRestrictToBasketValueTo > 0 && $this->fieldRestrictToBasketValueTo < $oBasket->dCostTotal)) {
                return false;
            }
        }

        if ($this->fieldRestrictToValueFrom <= $oBasket->dCostArticlesTotalAfterDiscounts && (0 == $this->fieldRestrictToValueTo || $this->fieldRestrictToValueTo >= $oBasket->dCostArticlesTotalAfterDiscounts)) {
            $bValidForBasket = true;
        }

        $paymentMethodDataAccess = $this->getPaymentMethodDataAccess();
        if ($bValidForBasket) {
            // now check basket contents.
            // do all articles, article_groups, or categories match?
            $aArticleIds = $paymentMethodDataAccess->getPermittedArticleIds($this->id);
            $aCategoryIds = $paymentMethodDataAccess->getPermittedCategoryIds($this->id);
            $aArticleGroupIds = $paymentMethodDataAccess->getPermittedArticleGroupIds($this->id);

            $bRestrictToArticles = (count($aArticleIds) > 0);
            $bRestrictToCategories = (count($aCategoryIds) > 0);
            $bRestrictToArticleGroups = (count($aArticleGroupIds) > 0);
            if ($bRestrictToArticles || $bRestrictToCategories || $bRestrictToArticleGroups) {
                $oBasketArticles = $oBasket->GetBasketContents();
                $oBasketArticles->GoToStart();

                if ($this->fieldPositivListLooseMatch) {
                    // if we are using a loose positive match, we allow use of the payment method as long as we have at least one article match for every restriction

                    // set the conditions to false, that we need to check
                    $bMatchArticleIds = (false == $bRestrictToArticles);
                    $bMatchCategoryIds = (false == $bRestrictToCategories);
                    $bMatchArticleGroupIds = (false == $bRestrictToArticleGroups);
                    // now check each basket entry
                    while ((!$bMatchArticleIds || !$bMatchCategoryIds || !$bMatchArticleGroupIds) && ($oBasketArticle = $oBasketArticles->Next())) {
                        // if we have no match for the article restriction, check if this one is a match. if it is, mark as "got one" :)
                        if (!$bMatchArticleIds && in_array($oBasketArticle->id, $aArticleIds)) {
                            $bMatchArticleIds = true;
                        }

                        // if we have no match for the category restriction, check if this one is a match. if it is, mark as "got one" :)
                        if (!$bMatchCategoryIds && $oBasketArticle->IsInCategory($aCategoryIds)) {
                            $bMatchCategoryIds = true;
                        }

                        // if we have no match for the article group restriction, check if this one is a match. if it is, mark as "got one" :)
                        if (!$bMatchArticleGroupIds && $oBasketArticle->IsInArticleGroups($aArticleGroupIds)) {
                            $bMatchArticleGroupIds = true;
                        }
                    }

                    // payment method is valid, if every condition that we needed to check did return a match
                    $bValidForBasket = ($bMatchArticleIds && $bMatchCategoryIds && $bMatchArticleGroupIds);
                } else {
                    $bMatchArticleIds = true;
                    $bMatchCategoryIds = true;
                    $bMatchArticleGroupIds = true;
                    while ($bValidForBasket && ($oBasketArticle = $oBasketArticles->Next())) {
                        if ($bRestrictToArticles && !in_array($oBasketArticle->id, $aArticleIds)) {
                            $bValidForBasket = false;
                        }

                        if ($bValidForBasket && $bRestrictToCategories && !$oBasketArticle->IsInCategory($aCategoryIds)) {
                            $bValidForBasket = false;
                        }

                        if ($bValidForBasket && $bRestrictToArticleGroups && !$oBasketArticle->IsInArticleGroups($aArticleGroupIds)) {
                            $bValidForBasket = false;
                        }
                    }
                }
            }
        }

        // if the payment method is still valid, check the negative list to see if we need to disable it
        if ($bValidForBasket) {
            $aArticleIds = $paymentMethodDataAccess->getInvalidArticleIds($this->id);
            $aCategoryIds = $paymentMethodDataAccess->getInvalidCategoryIds($this->id);
            $aArticleGroupIds = $paymentMethodDataAccess->getInvalidArticleGroupIds($this->id);

            $bRestrictToArticles = (count($aArticleIds) > 0);
            $bRestrictToCategories = (count($aCategoryIds) > 0);
            $bRestrictToArticleGroups = (count($aArticleGroupIds) > 0);

            if ($bRestrictToArticles || $bRestrictToCategories || $bRestrictToArticleGroups) {
                $oBasketArticles = $oBasket->GetBasketContents();
                $oBasketArticles->GoToStart();
                while ($bValidForBasket && ($oBasketArticle = $oBasketArticles->Next())) {
                    if ($bRestrictToArticles && in_array($oBasketArticle->id, $aArticleIds)) {
                        $bValidForBasket = false;
                    }

                    if ($bValidForBasket && $bRestrictToCategories && $oBasketArticle->IsInCategory($aCategoryIds)) {
                        $bValidForBasket = false;
                    }

                    if ($bValidForBasket && $bRestrictToArticleGroups && $oBasketArticle->IsInArticleGroups($aArticleGroupIds)) {
                        $bValidForBasket = false;
                    }
                }
            }
        }

        return $bValidForBasket;
    }

    /**
     * return payment method cost.
     *
     * @return float
     */
    public function GetPrice()
    {
        if (is_null($this->dPrice)) {
            $this->dPrice = 0;
            $oBasket = TShopBasket::GetInstance();
            if ('absolut' == $this->fieldValueType) {
                $this->dPrice = $this->fieldValue;
            } else {
                $this->dPrice = ($oBasket->dCostArticlesTotalAfterDiscounts * ($this->fieldValue / 100));
            }
            $this->dPrice = round($this->dPrice, 2);
        }

        return $this->dPrice;
    }

    /**
     * return the vat group for this payment method.
     *
     * attention:
     * vat-logic for payment-methods is currently not implemented in basket!
     *
     * @return TdbShopVat
     */
    public function GetVat()
    {
        $oVat = $this->GetFromInternalCache('ovat');
        if (is_null($oVat)) {
            $oVat = $this->GetFieldShopVat();
            if (is_null($oVat)) {
                $oShopConf = TdbShop::GetInstance();
                $oVat = $oShopConf->GetVat();
            }
            $this->SetInternalCache('ovat', $oVat);
        }

        return $oVat;
    }

    /**
     * used to display the shipping group.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'user-input', $sViewType = 'Core', $aCallTimeVars = array())
    {
        $oView = new TViewParser();

        // add view variables
        $oShop = TdbShop::GetInstance();
        $oView->AddVar('oShop', $oShop);
        $oView->AddVar('oPaymentMethod', $this);

        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
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
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        return array();
    }

    /**
     * the method should be called when returning from an external payment handler such as paypal
     * Note: you should not need to change this method - instead, work with the same method in the payment handler.
     */
    public function PostProcessExternalPaymentHandlerHook()
    {
        return $this->GetFieldShopPaymentHandler()->PostProcessExternalPaymentHandlerHook();
    }

    /**
     * shows an html block (usually next to the agb checkbox) with any special infos for the payment handler
     * form elements requiring user input should all be in aInput (for example <input name="aInput[somedata]"...>
     * note: user data and validation results should be made available via processConfirmOrderUserResponse.
     *
     * @param TdbDataExtranetUser $user
     *
     * @return string
     */
    public function renderConfirmOrderBlock(TdbDataExtranetUser $user)
    {
        if (false === $this->GetFieldShopPaymentHandler() instanceof IPkgShopPaymentHandlerCustomConfirmOrderBlockInterface) {
            return '';
        }

        return $this->GetFieldShopPaymentHandler()->renderConfirmOrderBlock($this, $user);
    }

    /**
     * is called when the user submits the order - allows you do validate and store any data the payment handler requested via renderConfirmOrderBlock.
     *
     * @param array $userData
     *
     * @return bool
     */
    public function processConfirmOrderUserResponse(TdbDataExtranetUser $user, $userData)
    {
        if (false === $this->GetFieldShopPaymentHandler() instanceof IPkgShopPaymentHandlerCustomConfirmOrderBlockInterface) {
            return true;
        }

        return $this->GetFieldShopPaymentHandler()->processConfirmOrderUserResponse($this, $user, $userData);
    }

    /**
     * @return ShopPaymentHandlerFactoryInterface
     */
    private function getShopPaymentHandlerFactory()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.payment.handler_factory');
    }

    private function getLogger(): LoggerInterface
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order');
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
