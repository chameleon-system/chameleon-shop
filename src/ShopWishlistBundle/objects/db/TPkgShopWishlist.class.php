<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopWishlist extends TAdbPkgShopWishlist
{
    const VIEW_PATH = 'pkgShopWishlist/views/db/TPkgShopWishlist';
    const URL_PARAMETER_FILTER_DATA = 'aPkgShopWishlist';
    const MSG_CONSUMER_BASE_NAME = 'TPkgShopWishlist';

    /**
     * adds an article to the wishlist - returns the new amount of that article on the list.
     *
     * @param string $sArticleId
     * @param float  $dAmount
     * @param string $sComment   - optional comment
     *
     * @return float
     */
    public function AddArticle($sArticleId, $dAmount = 1, $sComment = null)
    {
        $dNewAmount = 0;
        $aItemData = array();
        $oItem = TdbPkgShopWishlistArticle::GetNewInstance();
        /** @var $oItem TdbPkgShopWishlistArticle */
        if ($oItem->LoadFromFields(array('pkg_shop_wishlist_id' => $this->id, 'shop_article_id' => $sArticleId))) {
            $aItemData = $oItem->sqlData;
            $aItemData['amount'] += $dAmount;
        } else {
            $aItemData['pkg_shop_wishlist_id'] = $this->id;
            $aItemData['shop_article_id'] = $sArticleId;
            $aItemData['amount'] = $dAmount;
            $aItemData['datecreated'] = date('Y-m-d H:i:s');
        }
        if (!is_null($sComment)) {
            $aItemData['comment'] = $oItem->LoadFromRow($aItemData);
        }
        $oItem->LoadFromRow($aItemData);
        $oItem->AllowEditByAll(true);
        $oItem->Save();
        $dNewAmount = $aItemData['amount'];

        return $dNewAmount;
    }

    /**
     * return the msg consumer name for the wishlist instance.
     *
     * @return string
     */
    public function GetMsgConsumerName()
    {
        return TdbPkgShopWishlist::MSG_CONSUMER_BASE_NAME.$this->id;
    }

    /**
     * render the filter.
     *
     * @param string $sViewName     - name of the view
     * @param string $sViewType     - where to look for the view
     * @param array  $aCallTimeVars - optional parameters to pass to render method
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Customer', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        $oMsgManager = TCMSMessageManager::GetInstance();
        $sMessages = '';
        if ($oMsgManager->ConsumerHasMessages($this->GetMsgConsumerName())) {
            $sMessages = $oMsgManager->RenderMessages($this->GetMsgConsumerName());
        }
        $oView->AddVar('sMessages', $sMessages);
        $oView->AddVar('oWishlist', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbPkgShopWishlist::VIEW_PATH, $sViewType);
    }

    /**
     * return link to the private view of the wishlist.
     *
     * @param string $sMode                - select a mode of the wishlist (such as SendForm)
     * @param array  $aAdditionalParameter
     *
     * @return string
     */
    public function GetLink($sMode = '', $aAdditionalParameter = array())
    {
        if (!empty($sMode)) {
            $aAdditionalParameter[MTPkgShopWishlistCore::URL_MODE_PARAMETER_NAME] = $sMode;
        }
        $oShopConfig = TdbShop::GetInstance();

        return $oShopConfig->GetLinkToSystemPage('wishlist', $aAdditionalParameter, true);
    }

    /**
     * return the public link to the wishlist.
     *
     * @param array $aAdditionalParameter
     *
     * @return string
     */
    public function GetPublicLink($aAdditionalParameter = array())
    {
        $oShopConfig = TdbShop::GetInstance();
        $aAdditionalParameter[MTPkgShopWishlistPublicCore::URL_PARAMETER_NAME] = array('id' => $this->id);

        return $oShopConfig->GetLinkToSystemPage('wishlist-public', $aAdditionalParameter, true);
    }

    /**
     * returns the number of wishlist items on the list.
     *
     * @return int
     */
    public function GetNumberOfItemsInList()
    {
        $oWishlistItems = &$this->GetFieldPkgShopWishlistArticleList();

        return $oWishlistItems->Length();
    }

    /**
     * Artikel der Wunschliste.
     *
     * @return TdbPkgShopWishlistArticleList
     */
    public function &GetFieldPkgShopWishlistArticleList()
    {
        $oWishlistItems = $this->GetFromInternalCache('oPkgShopWishlistArticleList');
        if (is_null($oWishlistItems)) {
            $oWishlistItems = TdbPkgShopWishlistArticleList::GetListForPkgShopWishlistId($this->id, $this->iLanguageId);
            $oWishlistItems->bAllowItemCache = true;
            $this->SetInternalCache('oPkgShopWishlistArticleList', $oWishlistItems);
        }
        $oWishlistItems->GoToStart();

        return $oWishlistItems;
    }

    /**
     * Wunschslisten Mailhistory.
     *
     * @return TdbPkgShopWishlistMailHistoryList
     */
    public function &GetFieldPkgShopWishlistMailHistoryList()
    {
        $oWishlistHistoryItems = $this->GetFromInternalCache('oPkgShopWishlistMailHistoryList');
        if (is_null($oWishlistHistoryItems)) {
            $oWishlistHistoryItems = TdbPkgShopWishlistMailHistoryList::GetListForPkgShopWishlistId($this->id, $this->iLanguageId);
            $oWishlistHistoryItems->bAllowItemCache = true;
            $this->SetInternalCache('oPkgShopWishlistMailHistoryList', $oWishlistHistoryItems);
        }

        return $oWishlistHistoryItems;
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
     * return description text as html.
     *
     * @return string
     */
    public function GetDescriptionAsHTML()
    {
        $sText = trim($this->fieldDescription);
        $sText = TGlobal::OutHTML($sText);
        $sText = nl2br($sText);

        return $sText;
    }

    /**
     * send the wishlist per mail to a user.
     *
     * @param string $sToMail
     * @param string $sToName
     * @param string $sComment
     *
     * @return bool
     */
    public function SendPerMail($sToMail, $sToName, $sComment)
    {
        $bSendSuccess = false;
        $oOwner = $this->GetFieldDataExtranetUser();
        $oMail = TdbDataMailProfile::GetProfile('SendWishlist');
        $aMailData = array('to_name' => $sToName, 'to_mail' => $sToMail, 'comment' => $sComment, 'sWishlistURL' => $this->GetPublicLink());
        $oMail->AddDataArray($aMailData);
        $aUserData = $oOwner->GetObjectPropertiesAsArray();
        $oMail->AddDataArray($aUserData);
        $oMail->ChangeFromAddress($oOwner->GetUserEMail(), $oOwner->fieldFirstname.' '.$oOwner->fieldLastname);
        $oMail->ChangeToAddress($sToMail, $sToName);
        if ($oMail->SendUsingObjectView('emails', 'Customer')) {
            $bSendSuccess = true;
            $oHistory = TdbPkgShopWishlistMailHistory::GetNewInstance();
            /** @var $oHistory TdbPkgShopWishlistMailHistory */
            $aData = array('to_name' => $sToName, 'to_email' => $sToMail, 'comment' => $sComment, 'datesend' => date('Y-m-d H:i:s'), 'pkg_shop_wishlist_id' => $this->id);
            $oHistory->LoadFromRow($aData);
            $oHistory->AllowEditByAll(true);
            $oHistory->Save();
        }

        return $bSendSuccess;
    }
}
