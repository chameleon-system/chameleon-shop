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
use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopShippingGroupDataAccessInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * shipping costs are grouped into shipping groups. these groups hold the allowed payment methods, and they
 * can be restricted to certain users, user groups, or delivery countries.
/**/
class TShopShippingGroup extends TShopShippingGroupAutoParent implements IPkgShopVatable
{
    const VIEW_PATH = 'pkgShop/views/db/TShopShippingGroup';

    /**
     * @var TdbShopShippingTypeList
     */
    protected $oActingShippingTypeList = null;

    /**
     * a list of all payment methods valid for the group (and the current user/basket).
     *
     * @var TdbShopPaymentMethodList
     */
    protected $oValidPaymentMethods = null;

    /**
     * @var float|null
     */
    protected $dPrice = null;

    public function __sleep()
    {
        return array('table', 'id', 'iLanguageId');
    }

    public function __wakeup()
    {
        $this->Load($this->id);
        if (method_exists('TShopShippingGroupAutoParent', '__wakeup')) {
            parent::__wakeup();
        }
    }

    public function GetName()
    {
        $sName = parent::GetName();
        if (!TGlobal::IsCMSMode()) {
            $dCosts = $this->GetShippingCostsForBasket();
            if (0 != $dCosts) {
                $oLocal = TCMSLocal::GetActive();
                $sCurrencySymbol = $this->GetCurrencySymbol();
                $sName .= ' ('.$oLocal->FormatNumber($dCosts, 2).' '.$sCurrencySymbol.')';
            }
        }

        return $sName;
    }

    /**
     * returns currency symbol (by default €).
     *
     * @return string
     */
    protected function GetCurrencySymbol()
    {
        return '€';
    }

    /**
     * returns true if the shipping cost group may be used for the current user / basket.
     *
     * @return bool
     */
    public function isAvailableIgnoreGroupRestriction()
    {
        $bIsValid = true;

        $bIsValid = ($bIsValid && $this->IsActive());
        $bIsValid = ($bIsValid && $this->isValidForCurrentPortal());
        $bIsValid = ($bIsValid && $this->IsValidForCurrentUser());
        $bIsValid = ($bIsValid && $this->HasAvailableShippingTypes());
        $bIsValid = ($bIsValid && $this->HasAvailablePaymentMethods());

        if ($bIsValid && false === $this->hasShippingTypeThatStopsTypeChain()) {
            $oBasket = TShopBasket::GetInstance();
            //check to make sure that every item in the basket has one shipping type
            $oBasketItemList = $oBasket->GetBasketContents();
            $oBasketItemList->GoToStart();
            while ($bIsValid && ($oBasketItem = $oBasketItemList->Next())) {
                if (null === $oBasketItem->GetActingShippingType()) {
                    $bIsValid = false;
                }
            }
            $oBasketItemList->GoToStart();
        }

        return $bIsValid;
    }

    /**
     * @return bool
     */
    protected function hasShippingTypeThatStopsTypeChain()
    {
        $item = $this->GetActingShippingTypes()->FindItemWithProperty('fieldEndShippingTypeChain', true);
        if (false !== $item && null !== $item) {
            return true;
        }

        return false;
    }

    /**
     * returns true if the shipping cost group may be used for the current user / basket
     * also checks if the group is invalid due to a restriction to other shipping groups.
     *
     * @return bool
     */
    public function IsAvailable()
    {
        $bIsValid = $this->isAvailableIgnoreGroupRestriction();

        if ($bIsValid) {
            // check group restrictions
            $aShippingGroupIdList = array();
            $oShippingGroup = TShopBasket::GetInstance()->GetAvailableShippingGroups();
            if (null !== $oShippingGroup) {
                $aShippingGroupIdList = $oShippingGroup->GetIdList();
            }
            $bAllowedForCurrentAvailableShippingGroupList = $this->allowedForShippingGroupList($aShippingGroupIdList);

            $bIsValid = ($bIsValid && $bAllowedForCurrentAvailableShippingGroupList);
        }

        return $bIsValid;
    }

    /**
     * check if the group is restricted by other groups (by the mlt field) and returns false if some of the restricted groups
     * are found in the given list $aShippingGroupIdList.
     *
     * @param array $aShippingGroupIdList
     *
     * @return bool
     */
    public function allowedForShippingGroupList($aShippingGroupIdList)
    {
        $bValid = true;

        // get list of all groups that will exclude this shipping group from the valid list
        // this group is not valid if one of the restricted groups is found in the given shipping group list ($aShippingGroupIdList)
        $aRestrictionGroupIdList = $this->GetFieldShopShippingGroupIdList();
        foreach ($aRestrictionGroupIdList as $sRestrictionGroupId) {
            if (in_array($sRestrictionGroupId, $aShippingGroupIdList)) {
                $bValid = false;
            }
        }

        return $bValid;
    }

    /**
     * returns true if the group has no user or user group restriction.
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

        if ($bIsPublic) {
            // check if we have public shipping types
            $oPublicTypes = $this->GetPublicShippingTypes();
            if ($oPublicTypes->Length() < 1) {
                $bIsPublic = false;
            }
        }

        if ($bIsPublic) {
            // check if we have public payment types
            $oPublicPaymentTypes = $this->GetPublicPaymentMethods();
            if ($oPublicPaymentTypes->Length() < 1) {
                $bIsPublic = false;
            }
        }

        return $bIsPublic;
    }

    /**
     * returns true if the shipping group is marked as active for the current time.
     *
     * @return bool
     */
    public function IsActive()
    {
        $bIsActive = false;
        $sToday = date('Y-m-d H:i:s');
        if ($this->fieldActive && $this->fieldActiveFrom <= $sToday && ('0000-00-00 00:00:00' == $this->fieldActiveTo || $this->fieldActiveTo >= $sToday)) {
            $bIsActive = true;
        }

        return $bIsActive;
    }

    /**
     * @return ShopShippingGroupDataAccessInterface
     */
    protected function getShippingGroupDataAccess()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_shipping_group_data_access');
    }

    /**
     * return true if the shipping group is allowed for the current user.
     *
     * @return bool
     */
    public function IsValidForCurrentUser()
    {
        $bIsValidForUser = false;
        $bIsValidGroup = false;

        $shippingGroupDataAccess = $this->getShippingGroupDataAccess();
        $aUserGroups = $shippingGroupDataAccess->getPermittedUserGroupIds($this->id);
        if (!is_array($aUserGroups) || count($aUserGroups) < 1) {
            $bIsValidGroup = true;
        } else {
            $oUser = TdbDataExtranetUser::GetInstance();
            $bIsValidGroup = $oUser->InUserGroups($aUserGroups);
        }

        // now check user id
        if ($bIsValidGroup) {
            $aUserList = $shippingGroupDataAccess->getPermittedUserIds($this->id);
            if (!is_array($aUserList) || count($aUserList) < 1) {
                $bIsValidForUser = true;
            } else {
                $oUser = TdbDataExtranetUser::GetInstance();
                $bIsValidForUser = in_array($oUser->id, $aUserList);
            }
        }

        return $bIsValidForUser && $bIsValidGroup;
    }

    /**
     * return true if the shipping group is allowed for the current portal.
     * If no active portal was found and group was restricted to portal return false.
     *
     * @return bool
     */
    public function isValidForCurrentPortal()
    {
        $aPortalIdList = $this->getShippingGroupDataAccess()->getPermittedPortalIds($this->id);
        if (!is_array($aPortalIdList) || count($aPortalIdList) < 1) {
            $bIsValidForPortal = true;
        } else {
            $oActivePortal = $this->getPortalDomainService()->getActivePortal();
            if (null === $oActivePortal) {
                $bIsValidForPortal = false;
            } else {
                $bIsValidForPortal = in_array($oActivePortal->id, $aPortalIdList);
            }
        }

        return $bIsValidForPortal;
    }

    /**
     * returns true if the group has shipping types available for the current user/basket
     * in order to do this, we need to fetch all acting shipping types. if we have at least
     * one, we can continue.
     *
     * @return bool
     */
    public function HasAvailableShippingTypes()
    {
        return $this->GetActingShippingTypes()->Length() > 0;
    }

    /**
     * returns true if the group has payment methods available for the current user/basket.
     *
     * @return bool
     */
    public function HasAvailablePaymentMethods()
    {
        $bValidMethods = false;
        $oMethods = $this->GetValidPaymentMethods();
        $bValidMethods = ($oMethods->Length() > 0);

        return $bValidMethods;
    }

    /**
     * returns true, if the requested payment method id supported by this group for
     * this user, then the function returns true.
     *
     * @param string $sPaymentMethodId
     *
     * @return bool
     */
    public function HasPaymentMethod($sPaymentMethodId)
    {
        $oPaymentMethods = $this->GetValidPaymentMethods();

        return $oPaymentMethods->IsInList($sPaymentMethodId);
    }

    /**
     * return list of valid payment methods.
     *
     * @param bool $bRefresh - set to true to force a regeneration of the list
     *
     * @return TdbShopPaymentMethodList
     * @psalm-suppress InvalidNullableReturnType, NullableReturnStatement - In this instance we know that the return type cannot be null
     */
    public function GetValidPaymentMethods($bRefresh = false)
    {
        if (is_null($this->oValidPaymentMethods) || $bRefresh) {
            $this->oValidPaymentMethods = TdbShopPaymentMethodList::GetAvailableMethods($this->id);
            $this->oValidPaymentMethods->bAllowItemCache = true;
        }
        if (!is_null($this->oValidPaymentMethods)) {
            $this->oValidPaymentMethods->GoToStart();
        }

        return $this->oValidPaymentMethods;
    }

    /**
     * @param bool $bRefresh - set to true to force a regeneration of the list
     *
     * @return TdbShopShippingTypeList
     *                                 returns a list of all shipping types that can act on the current basket/user
     */
    public function GetActingShippingTypes($bRefresh = false)
    {
        if (is_null($this->oActingShippingTypeList) || $bRefresh) {
            $this->oActingShippingTypeList = TdbShopShippingTypeList::GetAvailableTypes($this->id);
            $this->oActingShippingTypeList->bAllowItemCache = true;
        }

        return $this->oActingShippingTypeList;
    }

    /**
     * return all active public shipping types for group.
     *
     * @return TdbShopShippingTypeList
     */
    public function GetPublicShippingTypes()
    {
        return TdbShopShippingTypeList::GetPublicShippingTypes($this->id);
    }

    /**
     * return all active public shipping types for group.
     *
     * @return TdbShopPaymentMethodList
     */
    public function GetPublicPaymentMethods()
    {
        return TdbShopPaymentMethodList::GetPublicPaymentMethods($this->id);
    }

    /**
     * @return float
     *               calculates the shipping costs for the current basket.
     *               The shipping costs are calculated using the following procedure:
     *               1.    The system fetches a list of all active shipping types (taking the user data and the active shipping group into account)
     *
     * 2.    The system creates a temporary copy of the basket content object
     * 3.    For each shipping type, we get all articles in the basket to which the shipping type would apply according to the rules defined for the shipping type by:
     *     a.    Loop through the temporary basket contents and move every article that matches the rule into the current shipping type
     *     b.    Check if the shipping type applies to the resulting collection of articles. If not, dump the articles back into the temporary basket. If it does, add the shipping type to the basket active shipping list (aActiveShippingList).
     * 4.    Now mark each article in the real basket with a pointer to the corresponding shipping type
     * 5.    Finally sum up the shipping costs for each active shipping type
     */
    public function GetShippingCostsForBasket()
    {
        if (is_null($this->dPrice)) {
            $this->dPrice = 0;
            $oActingShippingTypes = $this->GetActingShippingTypes();
            if (!is_null($oActingShippingTypes)) {
                $this->dPrice = $oActingShippingTypes->GetTotalPrice();
            }
        }

        return $this->dPrice;
    }

    /**
     * return the vat group for this shipping group.
     *
     * @return TdbShopVat|null
     */
    public function GetVat()
    {
        /** @var TdbShopVat|null $oVat */
        $oVat = $this->GetFromInternalCache('ovat');

        if (is_null($oVat)) {
            $oVat = null;
            $oShopConf = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
            if (true === $oShopConf->fieldShippingVatDependsOnBasketContents) {
                $oBasket = TShopBasket::GetInstance();
                $oVat = $oBasket->GetLargestVATObject();
                if(null !== $oVat) {
                    $this->SetInternalCache('ovat', $oVat);
                    return $oVat;
                }
            }

            $oVat = $this->GetFieldShopVat();
            if (is_null($oVat)) {
                $oShopConf = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
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

        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $oView->AddVar('oShop', $oShop);
        $oView->AddVar('oShippingGroup', $this);
        $oView->AddVar('oPaymentMethods', $this->GetValidPaymentMethodsSelectableByTheUser());
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
    }

    /**
     * return a the active list of payment handlers reduced to those, that the user may select in the payment step.
     *
     * @return TdbShopPaymentMethodList
     */
    public function GetValidPaymentMethodsSelectableByTheUser()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $oPaymentMethods = clone $this->GetValidPaymentMethods(true);
        $aInvalidMethods = [];

        $oPaymentMethods->GoToStart();
        while ($oPaymentMethod = $oPaymentMethods->Next()) {
            $oHandler = $oPaymentMethod->GetFieldShopPaymentHandler();
            if ($oHandler && $oHandler->isBlockForUserSelection()) {
                $aInvalidMethods[] = $connection->quote($oPaymentMethod->id);
            }
        }

        if (count($aInvalidMethods) > 0) {
            $oPaymentMethods->AddFilterString('`shop_payment_method`.`id` NOT IN (' . implode(',', $aInvalidMethods) . ')');
        }

        $oPaymentMethods->GoToStart();

        return $oPaymentMethods;
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
        $aViewVariables = array();

        return $aViewVariables;
    }

    /**
     * Get a list of shipping types based on a basket article list and the desired shipping country id.
     *
     * @param TShopBasketArticleList $oBasketArticleList
     * @param string                 $sDataCountryId
     *
     * @return TIterator
     */
    public function GetValidShippingTypesForBasketArticleListAndCountry($oBasketArticleList, $sDataCountryId)
    {
        $oValidShippingTypes = new TIterator();
        /** @var $oValidShippingTypes TIterator* */
        $oShippingTypes = $this->GetFieldShopShippingTypeList('position');
        while ($oShippingType = $oShippingTypes->Next()) {
            $bIsValid = $oShippingType->IsActive();
            $bIsValid = $bIsValid && $oShippingType->isValidForCurrentPortal();
            $bIsValid = $bIsValid && $oShippingType->IsValidForCurrentUser(false);
            $bIsValid = $bIsValid && $oShippingType->IsValidForCountry($sDataCountryId);
            if ($bIsValid) {
                $oAffectedArticles = $oBasketArticleList->GetArticlesAffectedByShippingType($oShippingType);
                $bIsValid = ($oAffectedArticles->Length() > 0);
            }
            if ($bIsValid) {
                $oValidShippingTypes->AddItem($oShippingType);
            }
        }

        return $oValidShippingTypes;
    }

    /**
     * Calculate the shipping costs for a basket article list to be expected for a certain shipping country id.
     *
     * @param TShopBasketArticleList $oBasketArticleList
     * @param string                 $sDataCountryId
     *
     * @return float
     */
    public function GetShippingCostsForBasketArticleListAndCountry($oBasketArticleList, $sDataCountryId)
    {
        /** @var Request $request */
        $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();

        $oBasketArticleList->GoToStart();

        $oOldBasket = clone TShopBasket::GetInstance();
        $oOldUser = clone TdbDataExtranetUser::GetInstance();

        $oTmpBasket = new TShopBasket();
        if (null !== $request && true === $request->hasSession()) {
            $request->getSession()->set(TShopBasket::SESSION_KEY_NAME, $oTmpBasket);
        }

        $oTmpUser = TdbDataExtranetUser::GetNewInstance();

        $oTmpShippingAddress = TdbDataExtranetUserAddress::GetNewInstance();
        $oTmpShippingAddress->fieldDataCountryId = $sDataCountryId;
        $oTmpShippingAddress->sqlData['data_country_id'] = $sDataCountryId;
        $oTmpShippingAddress->sqlData['lastname'] = 'Dummy';
        $oTmpShippingAddress->fieldLastname = 'Dummy';
        $oTmpShippingAddress->sqlData['city'] = 'Dummy';

        $oTmpUser->setFakedShippingAddressForUser($oTmpShippingAddress);

        $oTmpUser->fieldDataCountryId = $sDataCountryId;
        $oTmpUser->sqlData['data_country_id'] = $sDataCountryId;
        $oTmpUser->sqlData['lastname'] = 'Dummy';
        $oTmpUser->sqlData['city'] = 'Dummy';

        if (null !== $request && true === $request->hasSession()) {
            $request->getSession()->set(TdbDataExtranetUser::SESSION_KEY_NAME, $oTmpUser);
        }

        while ($oBasketArticle = $oBasketArticleList->Next()) {
            $oTmpBasket->AddItem($oBasketArticle);
        }

        $oTmpBasket->SetActiveShippingGroup($this);

        $oTmpBasket->RecalculateBasket();
        $oTmpBasket->ResetAllShippingMarkers();

        $dShippingCosts = $oTmpBasket->dCostShipping;


        if (null !== $request && true === $request->hasSession()) {
            $request->getSession()->set(TShopBasket::SESSION_KEY_NAME, $oOldBasket);
            $request->getSession()->set(TdbDataExtranetUser::SESSION_KEY_NAME, $oOldUser);
        }

        return $dShippingCosts;
    }

    /**
     * @param TShopBasketArticleList $oBasketArticleList
     * @param string $sDataCountryId
     *
     * @return TdbShopShippingGroupList
     */
    public static function getAvailableShippingGroupsForBasketArticleListAndCountry($oBasketArticleList, $sDataCountryId)
    {
        $oBasketArticleList->GoToStart();

        $oOldBasket = clone TShopBasket::GetInstance();
        $oOldUser = clone TdbDataExtranetUser::GetInstance();

        $oTmpBasket = new TShopBasket();
        /** @var Request $request */
        $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
        if (null !== $request && true === $request->hasSession()) {
            $request->getSession()->set(TShopBasket::SESSION_KEY_NAME, $oTmpBasket);
        }

        $oTmpUser = TdbDataExtranetUser::GetNewInstance();
        $oTmpUser->sqlData['data_country_id'] = $sDataCountryId;
        $oTmpUser->sqlData['city'] = 'Dummy';
        $oTmpUser->sqlData['lastname'] = 'Dummy';
        $oTmpShippingAddress = TdbDataExtranetUserAddress::GetNewInstance();
        $oTmpShippingAddress->fieldDataCountryId = $sDataCountryId;
        $oTmpShippingAddress->sqlData['data_country_id'] = $sDataCountryId;
        $oTmpUser->setFakedShippingAddressForUser($oTmpShippingAddress);

        if (null !== $request && true === $request->hasSession()) {
            $request->getSession()->set(TdbDataExtranetUser::SESSION_KEY_NAME, $oTmpUser);
        }

        while ($oBasketArticle = $oBasketArticleList->Next()) {
            $oNewBasketArticle = new TShopBasketArticle();
            $oNewBasketArticle->Load($oBasketArticle->id);
            $oNewBasketArticle->dAmount = $oBasketArticle->dAmount;
            $oTmpBasket->AddItem($oNewBasketArticle);
        }

        $oTmpBasket->RecalculateBasket();
        $oTmpBasket->ResetAllShippingMarkers();

        $oList = $oTmpBasket->GetAvailableShippingGroups();

        if (null !== $request && true === $request->hasSession()) {
            $request->getSession()->set(TShopBasket::SESSION_KEY_NAME, $oOldBasket);
            $request->getSession()->set(TdbDataExtranetUser::SESSION_KEY_NAME, $oOldUser);
        }

        return $oList;
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
