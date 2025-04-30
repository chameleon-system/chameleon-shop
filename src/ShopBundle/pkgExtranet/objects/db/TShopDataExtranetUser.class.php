<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * shop specific extranet user extensions.
 * /**/
class TShopDataExtranetUser extends TShopDataExtranetUserAutoParent
{
    public const COOKIE_NAME_HISTORY = 'shopuserarticleviewhistory';
    public const COOKIE_NAME_NOTICELIST = 'shopuserarticlenoticelist';
    public const MAX_NOTICE_LIST_COOKIE_LENGTH = 17; // this should stay under the critical 4kb string size

    /**
     * the article ids last viewed by the user (ie. on the detail page) will be stored here
     * Note: the although the list may grow longer (the complete history will be saved for logged in users)
     * we will only keep the first 100 in this list.
     *
     * @var array
     */
    protected $aArticleViewHistory;

    /**
     * the notice list (merkzettel) of the user. Note: this list is stored in session and for logged in users
     * in the database.
     *
     * @var array
     */
    protected $aNoticeList;

    /**
     * we use the post insert hook to set the customer number.
     */
    protected function PostInsertHook()
    {
        parent::PostInsertHook();

        // we need to add an customer number to the order... since generation of this number may differ
        // from shop to shop, we have added the method to fetch a new customer number to the shop class
        if (empty($this->sqlData['customer_number']) || empty($this->sqlData['shop_id'])) {
            $aUpdateData = [];
            if (empty($this->sqlData['customer_number'])) {
                $aUpdateData['customer_number'] = $this->GetCustomerNumber();
            }
            if (empty($this->sqlData['shop_id'])) {
                $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
                $aUpdateData['shop_id'] = $oShop->id;
            }
            if (count($aUpdateData) > 0) {
                $this->SaveFieldsFast($aUpdateData);
            }
        }
        TdbDataExtranetGroup::UpdateAutoAssignToUser($this);
    }

    /**
     * @return void
     */
    protected function PostSaveHook()
    {
        parent::PostSaveHook();

        // change newsletter email address if user is registered for a newsletter
        $oNewsletter = TdbPkgNewsletterUser::GetInstanceForActiveUser();
        if (!is_null($oNewsletter)) {
            $aNewsletterData = $oNewsletter->sqlData;
            $aNewsletterData['data_extranet_user_id'] = $this->id;
            $bHasChanged = false;
            if (isset($aNewsletterData['email']) && $aNewsletterData['email'] != $this->GetUserEMail()) {
                $aNewsletterData['email'] = $this->GetUserEMail();
                $bHasChanged = true;
            }
            if (isset($aNewsletterData['data_extranet_salutation_id']) && $aNewsletterData['data_extranet_salutation_id'] != $this->fieldDataExtranetSalutationId) {
                $aNewsletterData['data_extranet_salutation_id'] = $this->fieldDataExtranetSalutationId;
                $bHasChanged = true;
            }
            if (isset($aNewsletterData['lastname']) && $aNewsletterData['lastname'] != $this->fieldLastname) {
                $aNewsletterData['lastname'] = $this->fieldLastname;
                $bHasChanged = true;
            }
            if (isset($aNewsletterData['firstname']) && $aNewsletterData['firstname'] != $this->fieldFirstname) {
                $aNewsletterData['firstname'] = $this->fieldFirstname;
                $bHasChanged = true;
            }
            if ($bHasChanged) {
                $oNewsletter->LoadFromRow($aNewsletterData);
                $oNewsletter->Save();
            }
        }
    }

    public function __sleep()
    {
        $aData = parent::__sleep();
        $aData[] = 'aArticleViewHistory';
        $aData[] = 'aNoticeList';

        return $aData;
    }

    /**
     * returns the users customer number, if set.
     *
     * @return int
     */
    public function GetCustomerNumber()
    {
        $sCustNr = parent::GetCustomerNumber();
        if (empty($sCustNr)) {
            $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
            $sCustNr = $oShop->GetNextFreeCustomerNumber();
            $aData = $this->sqlData;
            $aData['customer_number'] = $sCustNr;
            $this->LoadFromRow($aData);
        }

        return $this->fieldCustomerNumber;
    }

    /**
     * save view history to user data...
     */
    protected function PostLoginHook()
    {
        parent::PostLoginHook();
        $this->PostLoginHookMergeTemporaryHistoryWithDatabaseHistory();
        $this->PostLoginHookMergeTemporaryNoticeListWithDatabaseHistory();
    }

    /**
     * takes the article view history from session and merges it with the data
     * in the database. this is done when the user logs in to make the history permanent.
     *
     * @return void
     */
    protected function PostLoginHookMergeTemporaryHistoryWithDatabaseHistory()
    {
        // merge current data with user data
        $aHistory = $this->GetArticleViewHistory();
        $aTmpList = array_reverse($aHistory, true);
        foreach (array_keys($aTmpList) as $histKey) {
            if (is_null($aTmpList[$histKey]->id)) {
                $aData = $aTmpList[$histKey]->sqlData;
                $aData['data_extranet_user_id'] = $this->id;
                $aTmpList[$histKey]->LoadFromRow($aData);
                $aTmpList[$histKey]->AllowEditByAll(true);
                $aTmpList[$histKey]->Save();
            }
        }
        $this->aArticleViewHistory = null;
        $this->GetArticleViewHistory();
        // remove items from cookie...
        $domain = TCMSSmartURLData::GetActive()->sOriginalDomainName;
        if ('www.' == substr($domain, 0, 4)) {
            $domain = substr($domain, 4);
        }
        setcookie(TdbDataExtranetUser::COOKIE_NAME_HISTORY, '', time() - 3600, '/', '.'.$domain, false, true);
    }

    /**
     * takes the notice list from session and merges it with the data
     * in the database. this is done when the user logs in to make the notice list permanent.
     *
     * @return void
     */
    protected function PostLoginHookMergeTemporaryNoticeListWithDatabaseHistory()
    {
        // merge current notice list with existing notice list
        $aNoticeList = $this->GetNoticeListArticles();
        $aTmpList = array_reverse($aNoticeList, true);
        foreach (array_keys($aTmpList) as $iArticleId) {
            if (is_null($aTmpList[$iArticleId]->id)) {
                // check if the user has such an item on the list... if he does, no change
                $oItem = TdbShopUserNoticeList::GetNewInstance();
                /** @var $oItem TdbShopUserNoticeList */
                if (!$oItem->LoadFromFields(['data_extranet_user_id' => $this->id, 'shop_article_id' => $iArticleId])) {
                    $aData = $aTmpList[$iArticleId]->sqlData;
                    $aData['data_extranet_user_id'] = $this->id;
                    $aTmpList[$iArticleId]->LoadFromRow($aData);
                    $aTmpList[$iArticleId]->AllowEditByAll(true);
                    $aTmpList[$iArticleId]->Save();
                }
            }
        }
        $this->aNoticeList = null;
        $this->GetNoticeListArticles();
        // remove items from cookie...
        $domain = TCMSSmartURLData::GetActive()->sOriginalDomainName;
        if ('www.' == substr($domain, 0, 4)) {
            $domain = substr($domain, 4);
        }
        setcookie(TdbDataExtranetUser::COOKIE_NAME_NOTICELIST, '', time() - 3600, '/', '.'.$domain, false, true);
    }

    /**
     * return alias for the user.
     *
     * @return string
     */
    public function GetUserAlias()
    {
        $sAlias = trim($this->fieldAliasName);
        if (empty($sAlias)) {
            $sAlias = $this->fieldFirstname.' '.mb_substr($this->fieldLastname, 0, 1).'.';
        }

        return $sAlias;
    }

    /**
     * get all reviews that have been published.
     *
     * @return TdbShopArticleReviewList
     */
    public function GetReviewsPublished()
    {
        return TdbShopArticleReviewList::GetPublishedReviewsForUser($this->id, $this->iLanguageId);
    }

    /**
     * add an article to the view history.
     *
     * @param int $iArticleId
     *
     * @return void
     */
    public function AddArticleIdToViewHistory($iArticleId)
    {
        if (is_null($this->aArticleViewHistory)) {
            $this->GetArticleViewHistory();
        }
        $iMaxQueueLength = 100;
        $shop = $this->getShopService()->getActiveShop();
        if ($shop->fieldDataExtranetUserShopArticleHistoryMaxArticleCount < $iMaxQueueLength && $shop->fieldDataExtranetUserShopArticleHistoryMaxArticleCount > 0) {
            $iMaxQueueLength = $shop->fieldDataExtranetUserShopArticleHistoryMaxArticleCount;
        }

        // check if the item being pushed is already in the end of the queue
        // if the item is on the queue, then we want to move it to the front
        reset($this->aArticleViewHistory);
        $item = null;
        $iKey = null;
        foreach (array_keys($this->aArticleViewHistory) as $histKey) {
            if (is_null($iKey) && $this->aArticleViewHistory[$histKey]['shop_article_id'] === $iArticleId) {
                $item = $this->aArticleViewHistory[$histKey];
                $iKey = $histKey;
                break;
            }
        }

        if (!is_null($iKey)) {
            unset($this->aArticleViewHistory[$iKey]);
            $item['datecreated'] = date('Y-m-d H:i:s');
            $itemObject = TdbDataExtranetUserShopArticleHistory::GetNewInstance($item);
            if (!is_null($this->id) && $this->IsLoggedIn()) {
                $itemObject->Save();
            }
            array_unshift($this->aArticleViewHistory, $item);
        } else {
            $itemObject = TdbDataExtranetUserShopArticleHistory::GetNewInstance();
            $aData = ['shop_article_id' => $iArticleId, 'datecreated' => date('Y-m-d H:i:s'), 'data_extranet_user_id' => $this->id];
            $itemObject->LoadFromRow($aData);
            if (!is_null($this->id) && $this->IsLoggedIn()) {
                $itemObject->Save();
            }
            array_unshift($this->aArticleViewHistory, $itemObject->sqlData);
        }

        // if the list is full, remove last item
        if (count($this->aArticleViewHistory) > $iMaxQueueLength) {
            array_pop($this->aArticleViewHistory);
        }

        // if the user is logged in, we need to make sure the list does not go over the limit set in the shop config
        if (!is_null($this->id) && $this->IsLoggedIn()) {
            if ($shop->fieldDataExtranetUserShopArticleHistoryMaxArticleCount > 0) {
                TdbDataExtranetUserShopArticleHistoryList::ReducedListForUser($shop->fieldDataExtranetUserShopArticleHistoryMaxArticleCount, $this->id);
            }
        }

        // save list to cookie for users not signed in
        if (SHOP_ALLOW_SAVING_ARTICLE_HISTORY_IN_COOKIE && !$this->IsLoggedIn()) {
            if (0 !== $shop->fieldDataExtranetUserShopArticleHistoryMaxCookieSize) {
                reset($this->aArticleViewHistory);
                $aHistory = [];
                foreach (array_keys($this->aArticleViewHistory) as $iHistKey) {
                    $aHistory[] = $this->aArticleViewHistory[$iHistKey];
                }
                $sHistory = json_encode($aHistory);
                $sHistory = base64_encode($sHistory);
                $dByteLength = mb_strlen($sHistory, '8bit');
                $dKByteLength = $dByteLength / 1024;

                if ($dKByteLength > $shop->fieldDataExtranetUserShopArticleHistoryMaxCookieSize) {
                    if (is_array($this->aArticleViewHistory)) {
                        array_pop($this->aArticleViewHistory);
                    }
                    reset($this->aArticleViewHistory);
                    foreach (array_keys($this->aArticleViewHistory) as $iHistKey) {
                        $aHistory[] = $this->aArticleViewHistory[$iHistKey];
                    }
                    $sHistory = json_encode($aHistory);
                    $sHistory = base64_encode($sHistory);
                    $dByteLength = mb_strlen($sHistory, '8bit');
                    $dKByteLength = $dByteLength / 1024;
                }
            }
            $aHistory = [];
            reset($this->aArticleViewHistory);
            foreach (array_keys($this->aArticleViewHistory) as $iHistKey) {
                $aHistory[] = $this->aArticleViewHistory[$iHistKey];
            }
            $sHistory = json_encode($aHistory);
            $sHistory = base64_encode($sHistory);
            $iLifeTime = 31536000; // 1 year
            if ($iLifeTime > CHAMELEON_MAX_COOKIE_LIFETIME) {
                $iLifeTime = CHAMELEON_MAX_COOKIE_LIFETIME;
            }
            $expireTime = $iLifeTime + time();
            $domain = TCMSSmartURLData::GetActive()->sOriginalDomainName;
            // drop 'www' subdomain
            if ('www.' == substr($domain, 0, 4)) {
                $domain = substr($domain, 4);
            }
            setcookie(TdbDataExtranetUser::COOKIE_NAME_HISTORY, $sHistory, $expireTime, '/', '.'.$domain, false, true);
        }
    }

    /**
     * returns the ids of up to the last 100 articles viewed.
     *
     * @return array
     */
    public function GetArticleViewHistory()
    {
        if (is_null($this->aArticleViewHistory)) {
            $this->aArticleViewHistory = [];
            $iMaxQueueLength = 100;
            $shop = $this->getShopService()->getActiveShop();
            if ($shop->fieldDataExtranetUserShopArticleHistoryMaxArticleCount < $iMaxQueueLength && $shop->fieldDataExtranetUserShopArticleHistoryMaxArticleCount > 0) {
                $iMaxQueueLength = $shop->fieldDataExtranetUserShopArticleHistoryMaxArticleCount;
            }

            if ($this->IsLoggedIn()) {
                // fetch from user
                $iNumRecsAdded = 0;
                $oHistoryList = $this->GetFieldDataExtranetUserShopArticleHistoryList();
                while (($oHistoryItem = $oHistoryList->Next()) && $iNumRecsAdded <= $iMaxQueueLength) {
                    $this->aArticleViewHistory[] = $oHistoryItem->sqlData;
                }
            } else {
                // fetch from cookie
                $aTmpHist = null;
                if (array_key_exists(TdbDataExtranetUser::COOKIE_NAME_HISTORY, $_COOKIE)) {
                    $sHistory = base64_decode($_COOKIE[TdbDataExtranetUser::COOKIE_NAME_HISTORY]);
                    $aTmpHist = json_decode($sHistory, true);
                }
                if (is_array($aTmpHist)) {
                    reset($aTmpHist);
                    foreach ($aTmpHist as $iKey => $aHistItemData) {
                        $oHistoryItem = TdbDataExtranetUserShopArticleHistory::GetNewInstance();
                        $oHistoryItem->LoadFromRow($aHistItemData);
                        $this->aArticleViewHistory[] = $oHistoryItem->sqlData;
                    }
                }
            }
        }

        $historyList = [];
        foreach ($this->aArticleViewHistory as $item) {
            $historyList[] = TdbDataExtranetUserShopArticleHistory::GetNewInstance($item);
        }

        return $historyList;
    }

    /**
     * returns the of the articles on the notice list. note: we limit the notice list to 1000 items.
     *
     * @return array
     */
    public function GetNoticeListArticles()
    {
        if (is_null($this->aNoticeList)) {
            $this->aNoticeList = [];
            $iMaxQueueLength = 1000;

            if ($this->IsLoggedIn()) {
                // fetch from user
                $iNumRecsAdded = 0;
                $oNoticeList = $this->GetFieldShopUserNoticeListList();
                while (($oNoticeListItem = $oNoticeList->Next()) && $iNumRecsAdded <= $iMaxQueueLength) {
                    if (!empty($oNoticeListItem->fieldShopArticleId)) {
                        $this->aNoticeList[$oNoticeListItem->fieldShopArticleId] = $oNoticeListItem->sqlData;
                    }
                }
            } else {
                // fetch from cookie
                $aTmpList = null;
                if (array_key_exists(TdbDataExtranetUser::COOKIE_NAME_NOTICELIST, $_COOKIE)) {
                    $sNoticeListString = base64_decode($_COOKIE[TdbDataExtranetUser::COOKIE_NAME_NOTICELIST]);
                    $aTmpList = json_decode($sNoticeListString, true);
                }
                if (is_array($aTmpList)) {
                    reset($aTmpList);
                    foreach ($aTmpList as $aNoticeListItemData) {
                        $oNoticeListItem = TdbShopUserNoticeList::GetNewInstance();
                        $oNoticeListItem->LoadFromRow($aNoticeListItemData);
                        $this->aNoticeList[$oNoticeListItem->fieldShopArticleId] = $oNoticeListItem->sqlData;
                    }
                }
            }
        }

        $noticeList = [];
        foreach ($this->aNoticeList as $item) {
            $item = TdbShopUserNoticeList::GetNewInstance($item);
            $noticeList[$item->fieldShopArticleId] = $item;
        }

        return $noticeList;
    }

    /**
     * add an article to the notice list.
     *
     * @param int $iArticleId
     * @param float $iAmount
     *
     * @return float|false - new amount on list
     */
    public function AddArticleIdToNoticeList($iArticleId, $iAmount = 1)
    {
        $dNewAmountOnList = 0;

        if (is_null($this->aNoticeList)) {
            $this->GetNoticeListArticles();
        }
        // check if the item being pushed is already in the end of the queue
        $iMaxQueueLength = 1000;

        $queNotEmpty = (count($this->aNoticeList) > 0);

        if (array_key_exists($iArticleId, $this->aNoticeList)) {
            // we do not allow placing an article more than once on the notice list
            $dNewAmountOnList = false;
        } else {
            $oNoticeListItem = TdbShopUserNoticeList::GetNewInstance();
            /** @var $oNoticeListItem TdbShopUserNoticeList */
            $data = $this->getNoticeListData($iArticleId, $iAmount);
            $oNoticeListItem->LoadFromRow($data);
            if (!is_null($this->id) && $this->IsLoggedIn()) {
                $oNoticeListItem->Save();
            }
            $this->aNoticeList[$oNoticeListItem->fieldShopArticleId] = $oNoticeListItem->sqlData;
        }

        if (false !== $dNewAmountOnList) {
            // remove item if amount drops to zero
            if ($this->aNoticeList[$iArticleId]['amount'] <= 0) {
                unset($this->aNoticeList[$iArticleId]);
            } else {
                $dNewAmountOnList = $this->aNoticeList[$iArticleId]['amount'];
            }

            // if the list is full, remove last item
            if (count($this->aNoticeList) > $iMaxQueueLength) {
                array_pop($this->aNoticeList);
            }

            // save list to cookie for users not signed in
            if (!$this->IsLoggedIn()) {
                $this->CommitNoticeListToCookie();
            }
        }

        return $dNewAmountOnList;
    }

    protected function getNoticeListData(string $productId, int $amount): array
    {
        return [
            'shop_article_id' => $productId,
            'date_added' => date('Y-m-d H:i:s'),
            'data_extranet_user_id' => $this->id,
            'amount' => $amount,
        ];
    }

    /**
     * save user notice list to cookie.
     *
     * @return void
     */
    protected function CommitNoticeListToCookie()
    {
        $aNoticeList = [];
        reset($this->aNoticeList);
        $counter = 0;
        foreach (array_keys($this->aNoticeList) as $iItemId) {
            if ($counter++ >= self::MAX_NOTICE_LIST_COOKIE_LENGTH) {
                break;
            }

            if (true === $this->aNoticeList[$iItemId] instanceof TdbShopUserNoticeList) {
                // this is for backwards compatibility reasons with old cookie data.
                // @deprecated can be removed in 2025 when all old cookies lost their validity
                $noticeListItemData = $this->aNoticeList[$iItemId]->sqlData;
            } else {
                $noticeListItemData = $this->aNoticeList[$iItemId];
            }

            $aNoticeList[] = $noticeListItemData;
        }
        $sNoticeList = json_encode($aNoticeList);
        $sNoticeList = base64_encode($sNoticeList);
        $iLifeTime = 31536000; // 1 year
        if ($iLifeTime > CHAMELEON_MAX_COOKIE_LIFETIME) {
            $iLifeTime = CHAMELEON_MAX_COOKIE_LIFETIME;
        }
        $expireTime = $iLifeTime + time();
        $domain = TCMSSmartURLData::GetActive()->sOriginalDomainName;
        // drop 'www' subdomain
        if ('www.' === substr($domain, 0, 4)) {
            $domain = substr($domain, 4);
        }
        setcookie(TdbDataExtranetUser::COOKIE_NAME_NOTICELIST, $sNoticeList, $expireTime, '/', '.'.$domain, false, true);
    }

    /**
     * remove an article form the notice list.
     *
     * @param string $sArticleId
     *
     * @return void
     */
    public function RemoveArticleFromNoticeList($sArticleId)
    {
        $this->GetNoticeListArticles();
        if (array_key_exists($sArticleId, $this->aNoticeList)) {
            unset($this->aNoticeList[$sArticleId]);
            if ($this->IsLoggedIn()) {
                $noticeListItemObject = TdbShopUserNoticeList::GetNewInstance();
                if (true === $noticeListItemObject->LoadFromFields(
                    [
                        'data_extranet_user_id' => $this->id,
                        'shop_article_id' => $sArticleId,
                    ]
                )) {
                    $noticeListItemObject->Delete();
                }
            } else {
                $this->CommitNoticeListToCookie();
            }
        }
    }

    /**
     * validates the user data.
     *
     * @param string $sFormDataName - the array name used for the form. send error messages here
     *
     * @return bool
     */
    public function ValidateData($sFormDataName = null)
    {
        if (is_null($sFormDataName)) {
            $sFormDataName = TdbDataExtranetUser::MSG_FORM_FIELD;
        }
        $bIsValid = parent::ValidateData($sFormDataName);
        $oMsgManager = TCMSMessageManager::GetInstance();

        // check postal code for country
        $bHasCountry = (array_key_exists('data_country_id', $this->sqlData) && !empty($this->sqlData['data_country_id']));
        $bHasPostalcode = (array_key_exists('postalcode', $this->sqlData) && !empty($this->sqlData['postalcode']));
        if ($bHasCountry && $bHasPostalcode) {
            $oCountry = TdbDataCountry::GetNewInstance();
            /** @var $oCountry TdbDataCountry */
            if ($oCountry->Load($this->sqlData['data_country_id'])) {
                if (!$oCountry->IsValidPostalcode($this->sqlData['postalcode'])) {
                    $oMsgManager->AddMessage($sFormDataName.'-postalcode', 'ERROR-USER-FIELD-INVALID-POSTALCODE');
                    $bIsValid = false;
                }
            }
        }

        return $bIsValid;
    }

    /**
     * return the total order value of the customer.
     *
     * @param string|null $sStartDate
     * @param string|null $sEndDate
     *
     * @return float
     */
    public function GetTotalOrderValue($sStartDate = null, $sEndDate = null)
    {
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $query = 'SELECT SUM(value_total) AS ordervalue
              FROM `shop_order`
             WHERE `data_extranet_user_id` = '.$connection->quote($this->id)."
               AND (`shop_order`.`id` IS NULL OR `shop_order`.`canceled` = '0')";
        if (null !== $sStartDate) {
            $query .= ' AND `datecreated` >= '.$connection->quote($sStartDate).' ';
        }
        if (null !== $sEndDate) {
            $query .= ' AND `datecreated` <= '.$connection->quote($sEndDate).' ';
        }
        $query .= ' GROUP BY `data_extranet_user_id`';

        $dValue = 0;
        $row = $connection->fetchAssociative($query);
        if (false !== $row && null !== $row['ordervalue']) {
            $dValue = $row['ordervalue'];
        }

        return $dValue;
    }

    /**
     * return an array of variable names that are flagged as protected and
     * will be saved to the new session on logout.
     *
     * @return array
     */
    protected function GetProtectedSessionVariables()
    {
        $aProtectedSessionVars = parent::GetProtectedSessionVariables();
        $aProtectedSessionVars[] = TShopBasket::SESSION_KEY_NAME;

        return $aProtectedSessionVars;
    }

    /**
     * logs the user out.
     */
    public function Logout()
    {
        $oBasket = TShopBasket::GetInstance();
        if (is_array($oBasket->aCompletedOrderStepList)) {
            reset($oBasket->aCompletedOrderStepList);
            foreach ($oBasket->aCompletedOrderStepList as $sStepName => $bValue) {
                $oBasket->aCompletedOrderStepList[$sStepName] = false;
            }
        }
        parent::Logout();

        if (SHOP_CLEAR_BASKET_CONTENTS_ON_LOGOUT) {
            if (class_exists('TShopBasket')) {
                $oBasket = TShopBasket::GetInstance();
                $oBasket->ClearBasket();
            }
        }
    }

    /**
     * @param array $aAddressData
     *
     * @return bool
     */
    public function UpdateShippingAddress($aAddressData)
    {
        $oOldAddress = clone $this->GetShippingAddress();
        $oOldBillingAddress = clone $this->GetBillingAddress();
        $bUpdated = parent::UpdateShippingAddress($aAddressData);

        $oNewAddress = $this->GetShippingAddress();
        if (false === $oNewAddress->hasSameDataInSqlData($oOldAddress)) {
            $this->hookChangedShippingAddress($oOldAddress, $oNewAddress);
        }
        $oNewBillingAddress = $this->GetBillingAddress();
        if (false === $oNewBillingAddress->hasSameDataInSqlData($oOldBillingAddress)) {
            $this->hookChangedBillingAddress($oOldBillingAddress, $oNewBillingAddress);
        }

        return $bUpdated;
    }

    /**
     * @param string $sAddressId
     *
     * @return TdbDataExtranetUserAddress
     */
    public function SetAddressAsShippingAddress($sAddressId)
    {
        $oOldShippingAddress = clone $this->GetShippingAddress();
        $oOldBillingAddress = clone $this->GetBillingAddress();

        $oNewShippingAddress = parent::SetAddressAsShippingAddress($sAddressId);

        if (false === $oNewShippingAddress->hasSameDataInSqlData($oOldShippingAddress)) {
            $this->hookChangedShippingAddress($oOldShippingAddress, $oNewShippingAddress);
        }

        $oNewBillingAddress = $this->GetBillingAddress();
        if (false === $oNewBillingAddress->hasSameDataInSqlData($oOldBillingAddress)) {
            $this->hookChangedBillingAddress($oOldBillingAddress, $oNewBillingAddress);
        }

        return $oNewShippingAddress;
    }

    /**
     * @return bool
     */
    public function ShipToAddressOtherThanBillingAddress()
    {
        $oOldAddress = clone $this->GetShippingAddress();
        $oOldBillingAddress = clone $this->GetBillingAddress();

        $bUpdated = parent::ShipToAddressOtherThanBillingAddress();
        $oNewAddress = $this->GetShippingAddress();
        if (false === $oNewAddress->hasSameDataInSqlData($oOldAddress)) {
            $this->hookChangedShippingAddress($oOldAddress, $oNewAddress);
        }

        $oNewBillingAddress = $this->GetBillingAddress();
        if (false === $oNewBillingAddress->hasSameDataInSqlData($oOldBillingAddress)) {
            $this->hookChangedBillingAddress($oOldBillingAddress, $oNewBillingAddress);
        }

        return $bUpdated;
    }

    /**
     * @param bool $bSetShippingToBillingAddress
     *
     * @return bool
     */
    public function ShipToBillingAddress($bSetShippingToBillingAddress = false)
    {
        $oOldAddress = clone $this->GetShippingAddress();
        $oOldBillingAddress = clone $this->GetBillingAddress();
        $bUpdated = parent::ShipToBillingAddress($bSetShippingToBillingAddress);

        $oNewAddress = $this->GetShippingAddress();
        if (false === $oNewAddress->hasSameDataInSqlData($oOldAddress)) {
            $this->hookChangedShippingAddress($oOldAddress, $oNewAddress);
        }

        $oNewBillingAddress = $this->GetBillingAddress();
        if (false === $oNewBillingAddress->hasSameDataInSqlData($oOldBillingAddress)) {
            $this->hookChangedBillingAddress($oOldBillingAddress, $oNewBillingAddress);
        }

        return $bUpdated;
    }

    /**
     * @param string $sAddressId
     *
     * @return TdbDataExtranetUserAddress
     */
    public function SetAddressAsBillingAddress($sAddressId)
    {
        $oOldShippingAddress = clone $this->GetShippingAddress();
        $oOldBillingAddress = clone $this->GetBillingAddress();
        $oNewBillingAddress = parent::SetAddressAsBillingAddress($sAddressId);

        $oNewShippingAddress = $this->GetShippingAddress();
        if (false === $oNewShippingAddress->hasSameDataInSqlData($oOldShippingAddress)) {
            $this->hookChangedShippingAddress($oOldShippingAddress, $oNewShippingAddress);
        }
        if (false === $oNewBillingAddress->hasSameDataInSqlData($oOldBillingAddress)) {
            $this->hookChangedBillingAddress($oOldBillingAddress, $oNewBillingAddress);
        }

        return $oNewBillingAddress;
    }

    /**
     * @param array $aAddressData
     *
     * @return bool
     */
    public function UpdateBillingAddress($aAddressData)
    {
        $oOldShippingAddress = clone $this->GetShippingAddress();
        $oOldBillingAddress = clone $this->GetBillingAddress();

        $bUpdated = parent::UpdateBillingAddress($aAddressData);
        $oNewShippingAddress = $this->GetShippingAddress();
        if (false === $oNewShippingAddress->hasSameDataInSqlData($oOldShippingAddress)) {
            $this->hookChangedShippingAddress($oOldShippingAddress, $oNewShippingAddress);
        }
        $oNewBillingAddress = $this->GetBillingAddress();
        if (false === $oNewBillingAddress->hasSameDataInSqlData($oOldBillingAddress)) {
            $this->hookChangedBillingAddress($oOldBillingAddress, $oNewBillingAddress);
        }

        return $bUpdated;
    }

    /**
     * hook called when the user changes the shipping address.
     *
     * @return void
     */
    protected function hookChangedShippingAddress(
        TdbDataExtranetUserAddress $oOldAddress,
        TdbDataExtranetUserAddress $oNewAddress
    ) {
        if ($oOldAddress->fieldDataCountryId != $oNewAddress->fieldDataCountryId) {
            // trigger shipping country change
            $oEvent = TPkgShop_TPkgCmsEvent_ChangeShippingCountry::GetNewInstance(
                $this,
                TPkgShop_TPkgCmsEvent_ChangeShippingCountry::CONTEXT_PKG_SHOP,
                TPkgShop_TPkgCmsEvent_ChangeShippingCountry::NAME_USER_CHANGED_SHIPPING_COUNTRY,
                ['oOld' => $oOldAddress, 'oNew' => $oNewAddress]
            );
            TPkgCmsEventManager::GetInstance()->NotifyObservers($oEvent);
        }
    }

    /**
     * hook called when the user changes the shipping address.
     *
     * @return void
     */
    protected function hookChangedBillingAddress(
        TdbDataExtranetUserAddress $oOldAddress,
        TdbDataExtranetUserAddress $oNewAddress
    ) {
        if ($oOldAddress->fieldDataCountryId != $oNewAddress->fieldDataCountryId) {
            // trigger shipping country change
            $oEvent = TPkgShop_TPkgCmsEvent_ChangeBillingCountry::GetNewInstance(
                $this,
                TPkgShop_TPkgCmsEvent_ChangeBillingCountry::CONTEXT_PKG_SHOP,
                TPkgShop_TPkgCmsEvent_ChangeBillingCountry::NAME_USER_CHANGED_SHIPPING_COUNTRY,
                ['oOld' => $oOldAddress, 'oNew' => $oNewAddress]
            );
            TPkgCmsEventManager::GetInstance()->NotifyObservers($oEvent);
        }
    }

    /**
     * @return ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface
     */
    private function getShopService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
