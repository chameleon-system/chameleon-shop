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
 * exposes methods that should be callable from all shop modules (via ajax or via get/post)
 * IMPORTANT: do not extend this class. instead extend from MTShopCentralHandlerCore.
/**/
class MTShopCentralHandlerCoreEndPoint extends TUserModelBase
{
    const URL_DATA = 'aShopCentralHandlerData';
    const URL_CALLING_SPOT_NAME = 'sCallingSpotName';

    /**
     * @var string|null
     */
    protected $sCallingSpotName = null;

    /**
     * @var array
     */
    protected $aUserData = array();

    public function Init()
    {
        parent::Init();
        $this->aUserData = $this->global->GetUserData(self::URL_DATA);
        if (!is_array($this->aUserData)) {
            $this->aUserData = array();
        }
        if (array_key_exists(self::URL_CALLING_SPOT_NAME, $this->aUserData)) {
            $this->sCallingSpotName = $this->aUserData[self::URL_CALLING_SPOT_NAME];
        }
    }

    /**
     * returns an article view.
     *
     * @param string $sShopArticleId
     * @param string $sViewName
     * @param string $sViewType
     * @param array  $aCallTimeVars
     *
     * @return stdClass
     */
    protected function GetArticleView($sShopArticleId = null, $sViewName = 'standard', $sViewType = 'Customer', $aCallTimeVars = array())
    {
        $oReturnObject = new stdClass();

        $oArticle = TdbShopArticle::GetNewInstance();
        $oArticle->Load($sShopArticleId);
        if ($oArticle->IsVariant()) {
            TdbShop::RegisterActiveVariantForSpot($this->sCallingSpotName, $oArticle->fieldVariantParentId, $oArticle->id);
        }
        $oReturnObject->sItemPage = $oArticle->Render($sViewName, $sViewType, $aCallTimeVars);
        $sKey = $oArticle->id;
        if ($oArticle->IsVariant()) {
            $sKey = $oArticle->fieldVariantParentId;
        }
        $oReturnObject->iListKey = 'key'.md5($sKey);

        return $oReturnObject;
    }

    /**
     * if this function returns true, then the result of the execute function will be cached.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return true;
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
