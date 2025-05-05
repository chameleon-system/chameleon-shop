<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MTShopWishlistBasketCore extends MTShopBasketCore
{
    protected $bAllowHTMLDivWrapping = true;

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'AddToWishlist';
        $this->methodCallAllowed[] = 'RemoveFromWishlist';
        $this->methodCallAllowed[] = 'MoveToNoticeList';
    }

    /**
     * Adds the article passed to the wishlist of the user (if the user is logged in).
     *
     * @param string $sArticleId
     * @param float $dAmount
     * @param string $sMessageHandler
     * @param bool $bIsInternalCall
     *
     * @return void
     */
    protected function AddToWishlist($sArticleId = null, $dAmount = null, $sMessageHandler = null, $bIsInternalCall = false)
    {
        $oGlobal = TGlobal::instance();
        $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        if (is_null($sArticleId) && array_key_exists(self::URL_ITEM_ID_NAME, $aRequestData)) {
            $sArticleId = $aRequestData[self::URL_ITEM_ID_NAME];
            if (empty($sArticleId)) {
                $sArticleId = null;
            }
        }
        if (is_null($dAmount) && array_key_exists(self::URL_ITEM_AMOUNT_NAME, $aRequestData)) {
            $dAmount = $aRequestData[self::URL_ITEM_AMOUNT_NAME];
            if (empty($dAmount)) {
                $dAmount = 1;
            }
        }

        if (is_null($sMessageHandler) && array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aRequestData)) {
            $sMessageHandler = $aRequestData[self::URL_MESSAGE_CONSUMER_NAME];
            if (empty($sMessageHandler)) {
                $sMessageHandler = null;
            }
        }
        if (is_null($sMessageHandler)) {
            $sMessageHandler = self::MSG_CONSUMER_NAME;
        }

        $oMsgManager = TCMSMessageManager::GetInstance();
        if (is_null($sArticleId)) {
            $oMsgManager->AddMessage($sMessageHandler, 'ERROR-SHOP-WISHLIST-ADD-ITEM-NO-ID-GIVEN');
        } else {
            $oItem = TdbShopArticle::GetNewInstance();
            /** @var $oItem TdbShopArticle */
            if ($oItem->Load($sArticleId)) {
                $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
                $oExtranet = TdbDataExtranet::GetInstance();
                $aInfoData = ['sLinkLoginStart' => '<a href="'.$oExtranet->GetLinkLoginPage().'">', 'sLinkLoginEnd' => '</a>', 'sLinkWishlistStart' => '<a href="'.$oShop->GetLinkToSystemPage('wishlist').'">', 'sLinkWishlistEnd' => '</a>', 'sLinkArticleStart' => '<a href="'.$oItem->getLink().'">', 'sLinkArticleEnd' => '</a>', 'sArticleName' => TGlobal::OutHTML($oItem->GetName()), 'dAddedAmount' => $dAmount];
                $oUser = TdbDataExtranetUser::GetInstance();
                if (!$oUser->IsLoggedIn()) {
                    $oMsgManager->AddMessage($sMessageHandler, 'WISHLIST-USER-NOT-LOGGED-IN', $aInfoData);
                } else {
                    // add item to list
                    $dNewAmountOnList = $oUser->AddArticleIdToWishlist($sArticleId, $dAmount);

                    $aInfoData['dNewAmount'] = $dNewAmountOnList;
                    if (false === $dNewAmountOnList) {
                        // article was already on notice list...
                        $oMsgManager->AddMessage($sMessageHandler, 'WISHLIST-ITEM-ALREADY-ON-LIST', $aInfoData);
                    } else {
                        $oMsgManager->AddMessage($sMessageHandler, 'WISHLIST-ADDED-ITEM', $aInfoData);
                    }
                }
            } else {
                $oMsgManager->AddMessage($sMessageHandler, 'ERROR-SHOP-WISHLIST-INVALID-ITEM-ID');
            }
        }

        // now redirect either to requested page, or to calling page
        $iRedirectNodeId = null;
        if (array_key_exists(self::URL_REDIRECT_NODE_ID_NAME, $aRequestData)) {
            $iRedirectNodeId = $aRequestData[self::URL_REDIRECT_NODE_ID_NAME];
            if (empty($iRedirectNodeId)) {
                $iRedirectNodeId = null;
            }
        }

        if (!$bIsInternalCall) {
            $this->RedirectAfterEvent($iRedirectNodeId);
        }
    }

    /**
     * remove an item from the users wishlist.
     *
     * @param string $sPkgShopWishlistArticleId - the pkg_shop_wishlist_article_id to remove from the list
     * @param string $sMessageHandler
     * @param bool $bIsInternalCall
     *
     * @return void
     */
    protected function RemoveFromWishlist($sPkgShopWishlistArticleId = null, $sMessageHandler = null, $bIsInternalCall = false)
    {
        $oGlobal = TGlobal::instance();
        $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        if (is_null($sPkgShopWishlistArticleId) && array_key_exists(self::URL_ITEM_ID_NAME, $aRequestData)) {
            $sPkgShopWishlistArticleId = $aRequestData[self::URL_ITEM_ID_NAME];
        }

        if (is_null($sMessageHandler) && array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aRequestData)) {
            $sMessageHandler = $aRequestData[self::URL_MESSAGE_CONSUMER_NAME];
            if (empty($sMessageHandler)) {
                $sMessageHandler = null;
            }
        }
        if (is_null($sMessageHandler)) {
            $sMessageHandler = self::MSG_CONSUMER_NAME;
        }
        $oMessageManager = TCMSMessageManager::GetInstance();
        $oUser = TdbDataExtranetUser::GetInstance();
        if (!$oUser->IsLoggedIn()) {
            $oMessageManager->AddMessage($sMessageHandler, 'WISHLIST-USER-NOT-LOGGED-IN');
        } else {
            $oWishlistItem = TdbPkgShopWishlistArticle::GetNewInstance();
            /* @var $oWishlistItem TdbPkgShopWishlistArticle */
            $oWishlistItem->Load($sPkgShopWishlistArticleId);
            $oArticle = $oWishlistItem->GetFieldShopArticle();
            if ($oArticle) {
                $aMessageData = ['sArticleLinkStart' => '<a href="'.TGlobal::OutHTML($oArticle->getLink()).'">', 'sArticleLinkEnd' => '</a>', 'sArticleName' => $oArticle->GetName()];
            } else {
                $aMessageData = [];
            }
            $oUser->RemoveArticleFromWishlist($sPkgShopWishlistArticleId);
            $oMessageManager->AddMessage($sMessageHandler, 'WISHLIST-REMOVED-ITEM', $aMessageData);
        }

        $iRedirectNodeId = null;
        if (array_key_exists(self::URL_REDIRECT_NODE_ID_NAME, $aRequestData)) {
            $iRedirectNodeId = $aRequestData[self::URL_REDIRECT_NODE_ID_NAME];
            if (empty($iRedirectNodeId)) {
                $iRedirectNodeId = null;
            }
        }

        if (!$bIsInternalCall) {
            $this->RedirectAfterEvent($iRedirectNodeId);
        }
    }

    /**
     * @return string[]
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgShop/shopBasket'));

        return $aIncludes;
    }
}
