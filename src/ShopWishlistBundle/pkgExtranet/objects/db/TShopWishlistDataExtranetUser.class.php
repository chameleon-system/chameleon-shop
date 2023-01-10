<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopWishlistDataExtranetUser extends TShopWishlistDataExtranetUserAutoParent
{
    /**
     * add an article to the wishlist.
     *
     * @param string $sArticleId
     * @param float  $dAmount
     *
     * @return float - new amount on list
     */
    public function AddArticleIdToWishlist($sArticleId, $dAmount = 1)
    {
        $dNewAmountOnList = 0;
        $oWishlist = $this->GetWishlist(true);
        $dNewAmountOnList = $oWishlist->AddArticle($sArticleId, $dAmount);

        return $dNewAmountOnList;
    }

    /**
     * remove an article form the wishlist.
     *
     * @param string $sPkgShopWishlistArticleId - the id of the item on the list
     *
     * @return void
     */
    public function RemoveArticleFromWishlist($sPkgShopWishlistArticleId)
    {
        $oWishlist = $this->GetWishlist(true);
        $oWishlistItem = TdbPkgShopWishlistArticle::GetNewInstance();
        /** @var $oWishlistItem TdbPkgShopWishlistArticle */
        if ($oWishlistItem->LoadFromFields(array('pkg_shop_wishlist_id' => $oWishlist->id, 'id' => $sPkgShopWishlistArticleId))) {
            $oWishlistItem->AllowEditByAll(true);
            $oWishlistItem->Delete();
        }
    }

    /**
     * return the users wishlist. if no wishlist exists, the method will return
     * null - unless the option bCreateIfNotExists is set, then we will
     * create a new on.
     *
     * @param bool $bCreateIfNotExists
     *
     * @return TdbPkgShopWishlist|null
     */
    public function GetWishlist($bCreateIfNotExists = false)
    {
        /** @var TdbPkgShopWishlist|null $oWishlist */
        $oWishlist = $this->GetFromInternalCache('oUserWishlist');

        if (is_null($oWishlist)) {
            $oWishlists = $this->GetFieldPkgShopWishlistList();
            if ($oWishlists->Length() > 0) {
                /** @var TdbPkgShopWishlist $oWishlist */
                $oWishlist = $oWishlists->Current();
            }

            if (is_null($oWishlist) && $bCreateIfNotExists) {
                $oWishlist = TdbPkgShopWishlist::GetNewInstance();
                if (!$oWishlist->LoadFromField('data_extranet_user_id', $this->id)) {
                    $aBaseData = array('data_extranet_user_id' => $this->id, 'is_public' => '0');
                    $oWishlist->LoadFromRow($aBaseData);
                    $oWishlist->Save();
                }
            }
            $this->SetInternalCache('oUserWishlist', $oWishlist);
        }

        return $oWishlist;
    }
}
