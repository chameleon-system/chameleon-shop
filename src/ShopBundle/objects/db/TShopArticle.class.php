<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Routing\PortalAndLanguageAwareRouterInterface;
use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\UrlNormalization\UrlNormalizationUtil;
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use ChameleonSystem\ShopBundle\Event\UpdateProductStockEvent;
use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopStockMessageDataAccessInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface;
use ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsServiceInterface;
use ChameleonSystem\ShopBundle\ProductVariant\ProductVariantNameGeneratorInterface;
use ChameleonSystem\ShopBundle\ShopEvents;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

if (!defined('PKG_SHOP_PRODUCT_URL_KEY_FIELD')) {
    define('PKG_SHOP_PRODUCT_URL_KEY_FIELD', 'cmsident');
}

class TShopArticle extends TShopArticleAutoParent implements ICMSSeoPatternItem, IPkgShopVatable, ICmsLinkableObject
{
    const VIEW_PATH = 'pkgShop/views/db/TShopArticle';
    const MSG_CONSUMER_BASE_NAME = 'tshoparticlenr';
    const URL_EXTERNAL_TO_BASKET_REQUEST = 'kaufen';
    const CMS_LINKABLE_OBJECT_PARAM_CATEGORY = 'iCategoryId';
    /*
     * the constant URL_ID_IDENT is used by the smart URL Handler TCMSSmartURLHandler_ShopProductV2 to identify the product
     */
    const URL_ID_IDENT = '_pid_';

    /**
     * @var float holds the original item price
     */
    public $dPrice = 0;

    /**
     * the volumn of the article.
     *
     * @var float
     */
    public $dVolume = 0;

    /**
     * used to store the pre-discounted price of the article.
     *
     * @var null|array
     */
    protected $aPriceBeforeDiscount = null;

    protected function PostLoadHook()
    {
        parent::PostLoadHook();
        $this->dPrice = $this->GetPrice();
        if (false == TGlobal::IsCMSMode()) {
            $this->SetPriceBasedOnActiveDiscounts();
        }
        $this->dVolume = ($this->fieldSizeHeight * $this->fieldSizeWidth * $this->fieldSizeLength);
    }

    /**
     * return true if the item is active (ie may be shown in the detail view of the shop).
     *
     * @return bool
     */
    public function AllowDetailviewInShop()
    {
        return $this->isActive() && !$this->fieldVirtualArticle;
    }

    /**
     * Returns Contributors for article for given types.
     *
     * @param array $aContributorTypes (from field 'identifier')
     *
     * @return TdbShopContributorList
     */
    public function &GetContributorList($aContributorTypes)
    {
        if (!is_array($aContributorTypes)) {
            $aContributorTypes = array($aContributorTypes);
        }
        $aContributorTypes = TTools::MysqlRealEscapeArray($aContributorTypes);
        $sQuery = "SELECT `shop_contributor`.*
                   FROM `shop_article_contributor`
              LEFT JOIN `shop_contributor` ON `shop_article_contributor`.`shop_contributor_id` = `shop_contributor`.`id`
              LEFT JOIN `shop_contributor_type` ON `shop_article_contributor`.`shop_contributor_type_id` = `shop_contributor_type`.`id`
                  WHERE `shop_contributor_type`.`identifier` in ('".implode("', '", $aContributorTypes)."')
                    AND `shop_article_contributor`.`shop_article_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
               ORDER BY `shop_article_contributor`.`position`
              ";

        return TdbShopContributorList::GetList($sQuery);
    }

    /**
     * fetch the base price of the item.
     *
     * @return float
     */
    protected function GetPrice()
    {
        return $this->fieldPrice;
    }

    /**
     * return the base price for 1 base unit as defined through the shop_unit_of_measurement and quantity_in_units.
     *
     * @return float|bool
     */
    public function GetBasePrice()
    {
        $dBasePrice = $this->GetFromInternalCache('dBasePrice');
        if (is_null($dBasePrice)) {
            $dBasePrice = false;
            $oUnitsOfMeasurement = $this->GetFieldShopUnitOfMeasurement();
            if ($oUnitsOfMeasurement) {
                $dBasePrice = $oUnitsOfMeasurement->GetBasePrice($this->dPrice, $this->fieldQuantityInUnits);
            }
            $this->SetInternalCache('dBasePrice', $dBasePrice);
        }

        return $dBasePrice;
    }

    /**
     * return all published reviews for the article.
     *
     * @return TdbShopArticleReviewList
     */
    public function &GetReviewsPublished()
    {
        $oReviews = $this->GetFromInternalCache('oPublishedReviews');
        if (is_null($oReviews)) {
            $oReviews = &TdbShopArticleReviewList::GetPublishedReviews($this->id, $this->iLanguageId);
            $this->SetInternalCache('oPublishedReviews', $oReviews);
        }

        return $oReviews;
    }

    /**
     * return the average rating of the article based on the customer reviews for the article.
     *
     * @param bool $bRecount - force a recount of the actual data
     *
     * @return float
     */
    public function GetReviewAverageScore($bRecount = false)
    {
        if ($bRecount) {
            $oReviews = $this->GetReviewsPublished();

            return $oReviews->GetAverageScore();
        } else {
            return $this->getProductStatsService()->getStats($this->id)->getReviewAverage();
        }
    }

    /**
     * return the total number of reviews (published).
     *
     * @param bool $bRecount - force a recount of the actual data
     *
     * @return int
     */
    public function GetReviewCount($bRecount = false)
    {
        if ($bRecount) {
            $oReviews = $this->GetReviewsPublished();

            return $oReviews->Length();
        } else {
            return $this->getProductStatsService()->getStats($this->id)->getReviews();
        }
    }

    /**
     * return the vat group of the article.
     *
     * @return TdbShopVat
     */
    public function GetVat()
    {
        $oVat = $this->GetFromInternalCache('ovat');
        if (is_null($oVat)) {
            $oVat = $this->getOwnVat();
            if (is_null($oVat)) {
                // try to fetch from article group
                $oArticleGroups = $this->GetArticleGroups();
                $oVat = $oArticleGroups->GetMaxVat();
            }

            if (is_null($oVat)) {
                // try to fetch from category
                $oCategories = $this->GetFieldShopCategoryList();
                $oVat = $oCategories->GetMaxVat();
            }

            if (is_null($oVat)) {
                // try to fetch from shop
                $oShopConfig = TdbShop::GetInstance();
                $oVat = $oShopConfig->GetVat();
            }
            $this->SetInternalCache('ovat', $oVat);
        }

        return $oVat;
    }

    /**
     * Returns the VAT based on $fieldShopVatId.
     * When saving through the table editor, fields are not initialized,
     * so we are falling back to the value from sqlData here
     * (this is a workaround chosen for simplicity, this would need to be changed in the table editor instead).
     *
     * @return null|TdbShopVat
     */
    private function getOwnVat()
    {
        $vatId = $this->fieldShopVatId;
        if ('' === $vatId && true === isset($this->sqlData['shop_vat_id'])) {
            $vatId = $this->sqlData['shop_vat_id'];
        }
        $vat = TdbShopVat::GetNewInstance();
        if ('' === $vatId || false === $vat->Load($vatId)) {
            return null;
        }

        return $vat;
    }

    /**
     * return the link to the detail view of the product.
     *
     * @param bool                $bAbsolute           set to true to include the domain in the link
     * @param null|string         $sAnchor
     * @param array               $aOptionalParameters supported optional parameters:
     *                                                 TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY - (string) force the article link to be within the given category id (only works if the category is assigned to the article)
     * @param TdbCmsPortal|null   $portal
     * @param TdbCmsLanguage|null $language
     *
     * @return string
     */
    public function getLink($bAbsolute = false, $sAnchor = null, $aOptionalParameters = array(), \TdbCmsPortal $portal = null, \TdbCmsLanguage $language = null)
    {
        // if no category is given, fetch the first category of the article
        $shopService = $this->getShopService();
        $oShop = $shopService->getActiveShop();
        $sCategoryId = null;
        if (true === array_key_exists(TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY, $aOptionalParameters)) {
            $sCategoryId = $aOptionalParameters[TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY];
            unset($aOptionalParameters[TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY]);
        }
        if (is_array($aOptionalParameters) && 0 === count($aOptionalParameters)) {
            $aOptionalParameters = null;
        }

        $aKey = array(
            'class' => __CLASS__,
            'method' => 'GetDetailLink',
            'bIncludePortalLink' => $bAbsolute,
            TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY => $sCategoryId,
            'id' => $this->id,
            'table' => $this->table,
        );
        if (is_null($sCategoryId)) {
            $oActiveCategory = $shopService->getActiveCategory();
            if ($oActiveCategory) {
                $aKey[TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY] = $oActiveCategory->id;
            }
        }
        $sKey = TCacheManager::GetKey($aKey);
        $sLink = $this->GetFromInternalCache('link'.$sKey);
        if (is_null($sLink)) {
            if (!array_key_exists('product_url_mode', $oShop->sqlData)) {
                $oShop->sqlData['product_url_mode'] = 'V1';
            }
            switch ($oShop->sqlData['product_url_mode']) {
                case 'V2':
                    $sLink = $this->GetDetailLinkV2($bAbsolute, $sCategoryId, $portal, $language);
                    break;
                case 'V1':
                default:
                    $sLink = $this->GetDetailLinkV1($bAbsolute, $sCategoryId);
                    break;
            }
            $this->SetInternalCache('link'.$sKey, $sLink);
        }
        if (null !== $aOptionalParameters) {
            $sLink .= $this->getUrlUtil()->getArrayAsUrl($aOptionalParameters, '?', '&');
        }

        if (null !== $sAnchor) {
            $sLink .= '#'.urlencode($sAnchor);
        }

        return $sLink;
    }

    /**
     * @deprecated use new getLink($sAnchor = null, $bAbsolute = false, $aOptionalParameters = array()) method - see method documentation how to pass old parameters
     *
     * return the link to the detail view of the product
     *
     * @param bool $bIncludePortalLink - set to true to include the domain in the link
     * @param int  $iCategoryId        - pass a category to force a category (only works if the category is assigned to the article)
     *
     * @return string
     */
    public function GetDetailLink($bIncludePortalLink = false, $iCategoryId = null)
    {
        return $this->getLink(
            $bIncludePortalLink,
            null,
            array(TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY => $iCategoryId)
        );
    }

    /**
     * return the link to the detail view of the product (classic URL).
     *
     * @param bool $bIncludePortalLink - set to true to include the domain in the link
     * @param int  $iCategoryId        - pass a category to force a category (only works if the category is assigned to the article)
     *
     * @return string
     */
    protected function GetDetailLinkV1($bIncludePortalLink = false, $iCategoryId = null)
    {
        // if no category is given, fetch the first category of the article
        $oCategory = null;

        $oShopConfig = TdbShop::GetInstance();

        if (!is_null($iCategoryId)) {
            $oCategory = TdbShopCategory::GetNewInstance();
            /** @var $oCategory TdbShopCategory */
            if (!$oCategory->Load($iCategoryId) || false == $oCategory->AllowDisplayInShop()) {
                $oCategory = null;
            }
        }
        if (is_null($oCategory)) {
            // try to fetch the category form the current active category... if the product is in that category
            $oActiveCategory = $oShopConfig->GetActiveCategory();
            if (!is_null($oActiveCategory)) {
                if ($this->IsInCategory(array($oActiveCategory->id))) {
                    $oCategory = &$oActiveCategory;
                }
            }
        }
        if (is_null($oCategory)) {
            $oCategory = &$this->GetPrimaryCategory();
        }
        $sCategoryURLPath = '';
        if (!is_null($oCategory)) {
            $sCategoryURLPath = $oCategory->GetURLPath();
        }
        $sArticlePath = $sCategoryURLPath;
        if (!empty($sArticlePath)) {
            $sArticlePath .= '/';
        }
        $sArticlePath .= $this->getUrlNormalizationUtil()->normalizeUrl($this->fieldName).'/id/'.urlencode($this->id);

        $sPageLink = $oShopConfig->GetLinkToSystemPage('product', null, $bIncludePortalLink);
        $oConf = &TdbCmsConfig::GetInstance();

        if ('.html' == substr($sPageLink, -5)) {
            $sPageLink = substr($sPageLink, 0, -5).'/';
        } elseif ('/' != substr($sPageLink, -1)) {
            $sPageLink .= '/';
        }
        if ('/' == substr($sArticlePath, 0, 1)) {
            $sArticlePath = substr($sArticlePath, 1);
        }
        $sLink = $sPageLink.$sArticlePath;

        return $sLink;
    }

    /**
     * a better SEO URL (depends on TCMSSmartURLHandler_ShopProductV2).
     *
     * @param bool           $bIncludePortalLink - set to true to include the domain in the link
     * @param int            $iCategoryId        - pass a category to force a category (only works if the category is assigned to the article)
     * @param TdbCmsPortal   $portal
     * @param TdbCmsLanguage $language
     *
     * @return string
     */
    protected function GetDetailLinkV2($bIncludePortalLink = false, $iCategoryId = null, \TdbCmsPortal $portal = null, \TdbCmsLanguage $language = null)
    {
        $aParts = array();
        $aNameParts = array();
        $urlNormalizationUtil = $this->getUrlNormalizationUtil();
        $aNameParts[] = $urlNormalizationUtil->normalizeUrl($this->fieldName);
        $oManufacturer = $this->GetFieldShopManufacturer();
        if (is_null($oManufacturer)) {
            $aParts[] = '-';
        } else {
            $aParts[] = $urlNormalizationUtil->normalizeUrl($oManufacturer->fieldName);
        }

        $oCategory = null;
        if (!is_null($iCategoryId)) {
            $oCategory = TdbShopCategory::GetNewInstance();
            if (!$oCategory->Load($iCategoryId) || false == $oCategory->AllowDisplayInShop()) {
                $oCategory = null;
            }
        }
        if (is_null($oCategory)) {
            // try to fetch the category from the currently active category... if the product is in that category
            $oActiveCategory = $this->getShopService()->getActiveCategory();
            if (!is_null($oActiveCategory)) {
                if ($this->IsInCategory(array($oActiveCategory->id))) {
                    $oCategory = &$oActiveCategory;
                }
            }
        }
        // if no category is given, fetch the first category of the article
        if (is_null($oCategory)) {
            $oCategory = &$this->GetPrimaryCategory();
        }
        if (is_null($oCategory)) {
            $aParts[] = '-';
        } else {
            $oRootCat = $oCategory->GetRootCategory();
            if ($oRootCat) {
                $aParts[] = $urlNormalizationUtil->normalizeUrl($oRootCat->fieldName);
            }
            $sCatName = $oCategory->fieldNameProduct;
            if (empty($sCatName) && ($oCategory->id != $oRootCat->id)) {
                $sCatName = $oCategory->fieldName;
            }
            if (!empty($sCatName)) {
                $aNameParts[] = $urlNormalizationUtil->normalizeUrl($sCatName);
            }
        }

        $productPath = strtolower(join('/', $aParts));
        $productName = strtolower(join('-', $aNameParts));
        if (null === $oCategory) {
            $catId = 0;
        } else {
            $catId = $oCategory->sqlData['cmsident'];
        }
        $parameters = array(
            'productPath' => $productPath,
            'productName' => $productName,
            'catid' => $catId,
            'identifier' => $this->sqlData[PKG_SHOP_PRODUCT_URL_KEY_FIELD],
        );

        $router = $this->getFrontendRouter();

        if (true === $bIncludePortalLink) {
            $referenceType = UrlGeneratorInterface::ABSOLUTE_URL;
        } else {
            $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        }

        return $router->generateWithPrefixes('shop_article', $parameters, $portal, $language, $referenceType);
    }

    /**
     * return the link to the review page of the product.
     *
     * @param bool $bIncludePortalLink - set to true to include the domain in the link
     * @param int  $iCategoryId        - pass a category to force a category (only works if the category is assigned to the article)
     *
     * @return string
     */
    public function GetReviewFormLink($bIncludePortalLink = false, $iCategoryId = null)
    {
        return $this->GetDetailLink($bIncludePortalLink, $iCategoryId).'#review'.TGlobal::OutHTML($this->id);
    }

    /**
     * generates a link that can be used to add this product to the basket.
     *
     * @param bool   $bIncludePortalLink     - include domain in link
     * @param bool   $bRedirectToBasket      - redirect to basket page after adding product
     * @param bool   $bReplaceBasketContents - set to true if you want the contents of the basket to be replaced by the product wenn added to basket
     * @param bool   $bGetAjaxParameter      - set to true if you want to get basket link for ajax call
     * @param string $sMessageConsumer       - set custom message consumer
     *
     * @return string
     */
    public function GetToBasketLink($bIncludePortalLink = false, $bRedirectToBasket = false, $bReplaceBasketContents = false, $bGetAjaxParameter = false, $sMessageConsumer = MTShopBasketCore::MSG_CONSUMER_NAME_MINIBASKET)
    {
        $sLink = '';
        $aParameters = $this->GetToBasketLinkParameters($bRedirectToBasket, $bReplaceBasketContents, $bGetAjaxParameter, $sMessageConsumer);
        // convert module_fnc to array to string
        $aIncludeParams = TdbShop::GetURLPageStateParameters();
        $oGlobal = TGlobal::instance();
        foreach ($aIncludeParams as $sKeyName) {
            if ($oGlobal->UserDataExists($sKeyName) && !array_key_exists($sKeyName, $aParameters)) {
                $aParameters[$sKeyName] = $oGlobal->GetUserData($sKeyName);
            }
        }

        return $this->generateLinkForToBasketParameters($aParameters, $bIncludePortalLink);
    }

    /**
     *Creates to basket link from given to basket parameters.
     *
     * @param array $aParameters
     * @param $bIncludePortalLink
     *
     * @return string
     */
    protected function generateLinkForToBasketParameters($aParameters = array(), $bIncludePortalLink)
    {
        $activePage = $this->getActivePageService()->getActivePage();
        if (!is_object($activePage)) {
            $sLink = '?'.TTools::GetArrayAsURL($aParameters);
            if ($bIncludePortalLink) {
                /** @var Request $request */
                $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
                $sLink = $request->getSchemeAndHttpHost().$sLink;
            }
        } else {
            $sLink = $activePage->GetRealURLPlain($aParameters, $bIncludePortalLink);
        }

        return $sLink;
    }

    /**
     * returns a url to place the item in the basket from an external location.
     *
     * @return string
     */
    public function GetToBasketLinkForExternalCalls()
    {
        return 'http://'.$this->getPortalDomainService()->getActiveDomain()->getInsecureDomainName().'/'.TdbShopArticle::URL_EXTERNAL_TO_BASKET_REQUEST.'/id/'.urlencode($this->id);
    }

    /**
     * return parameters needed for a to basket call.
     *
     * @param bool   $bRedirectToBasket      - redirect to basket page after adding product
     * @param bool   $bReplaceBasketContents - set to true if you want the contents of the basket to be replaced by the product wenn added to basket
     * @param bool   $bGetAjaxParameter      - set to true if you want to get basket link for ajax call
     * @param string $sMessageConsumer       - set custom message consumer
     *
     * @return array
     */
    public function GetToBasketLinkParameters($bRedirectToBasket = false, $bReplaceBasketContents = false, $bGetAjaxParameter = false, $sMessageConsumer = MTShopBasketCore::MSG_CONSUMER_NAME_MINIBASKET)
    {
        $aParameters = $this->getToBasketLinkBasketParameters($bRedirectToBasket, $bReplaceBasketContents, $bGetAjaxParameter, $sMessageConsumer);
        $aParameters = $this->getToBasketLinkOtherParameters($aParameters);

        return $aParameters;
    }

    /**
     * return link to send a friend form.
     *
     * @param string $sLinkText - the text to display in the link
     * @param string $sCSSClass - the css classname of the a element
     *
     * @return string
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    public function GetSendAFriendLink($sLinkText, $sCSSClass = '')
    {
        $sLink = '';
        $oShop = TdbShop::GetInstance();
        $sLink = $oShop->GetLinkToSystemPageAsPopUp($sLinkText, 'tell-a-friend', array(MTShopArticleCatalogCore::URL_ITEM_ID => $this->id), false, 700, 450, $sCSSClass);

        return $sLink;
    }

    /**
     * generate a link used to add the article to the notice list.
     *
     * @param bool $bIncludePortalLink - include domain in link
     * @param bool $sMsgConsumerName
     *
     * @return string
     */
    public function GetToNoticeListLink($bIncludePortalLink = false, $sMsgConsumerName = false)
    {
        if (false === $sMsgConsumerName) {
            $sMsgConsumerName = $this->GetMessageConsumerName();
        }
        $oShopConfig = TdbShop::GetInstance();

        $aParameters = array(
            'module_fnc' => array(
                $oShopConfig->GetBasketModuleSpotName() => 'AddToNoticeList',
            ),
            MTShopBasketCore::URL_ITEM_ID => $this->id,
            MTShopBasketCore::URL_ITEM_AMOUNT => 1,
            MTShopBasketCore::URL_MESSAGE_CONSUMER => $sMsgConsumerName,
        );

        $aExcludeParameters = TCMSSmartURLData::GetActive()->getSeoURLParameters();

        foreach ($aParameters as $sKey => $sVal) {
            $aExcludeParameters[] = $sKey;
        }

        // now add all OTHER parameters
        $aOtherParameters = TGlobal::instance()->GetUserData(null, $aExcludeParameters);
        foreach ($aOtherParameters as $sKey => $sVal) {
            $aParameters[$sKey] = $sVal;
        }

        $activePage = $this->getActivePageService()->getActivePage();

        return $activePage->GetRealURLPlain($aParameters, $bIncludePortalLink);
    }

    /**
     * return link that can be used to remove the item from the notice list.
     *
     * @return string
     */
    public function GetRemoveFromNoticeListLink($bIncludePortalLink = false, $sMsgConsumerName = false)
    {
        $oShopConfig = TdbShop::GetInstance();
        if (false === $sMsgConsumerName) {
            $sMsgConsumerName = $this->GetMessageConsumerName();
        }
        $aParameters = array('module_fnc['.$oShopConfig->GetBasketModuleSpotName().']' => 'RemoveFromNoticeList', MTShopBasketCore::URL_ITEM_ID => $this->id, MTShopBasketCore::URL_MESSAGE_CONSUMER => $sMsgConsumerName);
        $aExcludeParameters = TCMSSmartURLData::GetActive()->getSeoURLParameters();

        foreach ($aParameters as $sKey => $sVal) {
            $aExcludeParameters[] = $sKey;
        }

        // now add all OTHER parameters
        $aOtherParameters = TGlobal::instance()->GetUserData(null, $aExcludeParameters);
        foreach ($aOtherParameters as $sKey => $sVal) {
            $aParameters[$sKey] = $sVal;
        }

        $oActivePage = $this->getActivePageService()->getActivePage();

        $sLink = $oActivePage->GetRealURLPlain($aParameters, $bIncludePortalLink);

        return $sLink;
    }

    /**
     * return the primary category of the article (usually just the first one found.
     *
     * @return TdbShopCategory
     */
    public function &GetPrimaryCategory()
    {
        $oCategory = null;
        // if this is a variant, then we want to take the parent object instead - at least if no data is set for the child
        if (!empty($this->fieldShopCategoryId)) {
            $oCategory = &$this->GetFieldShopCategory();
            if (!is_null($oCategory) && false == $oCategory->AllowDisplayInShop()) {
                $oCategory = null;
            }
        }

        if (is_null($oCategory)) {
            $oCategories = &$this->GetFieldShopCategoryList();
            $oCategories->GoToStart();
            if ($oCategories->Length() > 0) {
                $oCategory = &$oCategories->Current();
            }
        }
        if (is_null($oCategory) && $this->IsVariant()) {
            $oParent = &$this->GetFieldVariantParent();
            $oCategory = &$oParent->GetPrimaryCategory();
        }

        return $oCategory;
    }

    /**
     * return all categories assigned to the article.
     *
     * @param string $sOrderBy
     *
     * @return TdbShopCategoryList
     */
    public function &GetFieldShopCategoryList($sOrderBy = '')
    {
        $oCategories = &$this->GetFromInternalCache('oCategories');
        if (is_null($oCategories)) {
            $oCategories = &TdbShopCategoryList::GetArticleCategories($this->id, $this->iLanguageId);
            $this->SetInternalCache('oCategories', $oCategories);
        }

        return $oCategories;
    }

    /**
     * return all article groups assigend to the article.
     *
     * @return TdbShopArticleGroupList
     */
    public function &GetArticleGroups()
    {
        $oArticleGroups = &$this->GetFromInternalCache('oArticleGroups');
        if (is_null($oArticleGroups)) {
            $oArticleGroups = &TdbShopArticleGroupList::GetArticleGroups($this->id);
            $this->SetInternalCache('oArticleGroups', $oArticleGroups);
        }

        return $oArticleGroups;
    }

    /**
     * used to display an article.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     * @param bool   $bAllowCache   - set to false if you want to suppress caching for the call
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Core', $aCallTimeVars = array(), $bAllowCache = true)
    {
        if (!$this->IsVariant()) {
            $sActiveVariantForCurrentSpot = TdbShop::GetRegisteredActiveVariantForCurrentSpot($this->id);
            if (false != $sActiveVariantForCurrentSpot) {
                // render the variant instead of the current article
                $oVariant = TdbShopArticle::GetNewInstance();
                /** @var $oVariant TdbShopArticle */
                $oVariant->Load($sActiveVariantForCurrentSpot);

                return $oVariant->Render($sViewName, $sViewType, $aCallTimeVars);
            }
        }

        $oView = new TViewParser();

        $aMessageConsumerToCheck = array(self::MSG_CONSUMER_BASE_NAME.$this->id);
        if ($this->IsVariant()) {
            $aMessageConsumerToCheck[] = self::MSG_CONSUMER_BASE_NAME.$this->fieldVariantParentId;
        }

        $sMessages = $this->GetMessages($aMessageConsumerToCheck);

        $oView->AddVar('sMessages', $sMessages);
        $oView->AddVar('oArticle', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
    }

    /**
     * uses message manager to render messages and return them as string.
     *
     * @param $aMessageConsumerToCheck
     *
     * @return string
     */
    protected function GetMessages($aMessageConsumerToCheck)
    {
        $sMessages = '';
        $oMsgManager = TCMSMessageManager::GetInstance();

        foreach ($aMessageConsumerToCheck as $sMessageConsumer) {
            if ($oMsgManager->ConsumerHasMessages($sMessageConsumer)) {
                $sMessages .= $oMsgManager->RenderMessages($sMessageConsumer);
            }
        }

        return $sMessages;
    }

    /**
     * add cache parameters (trigger clear for render).
     *
     * @param array $aCacheParameters
     */
    protected function AddCacheParameters(&$aCacheParameters)
    {
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
     * return the preview image for the article using the image size given by sImageSizeName
     * Note: if the article has no image, then the view 'no-preview-image-defined' in the article object view dir
     *       will be rendered instead (viewType will be the same as passed to this function - so make sure to define the view
     *       in your extensions if you call this method with sViewType = 'Customer'.
     *
     * @param string $sImageSizeName - sImageSizeName is a system name defined in the shop_article_image_size table
     * @param string $sViewName      - use view name to render the image using different views (views can be found in ./objectviews/shop/TShopArticlePreviewImage)
     * @param string $sViewType      - defines in which objectviews dir the system looks for the view (Core, Custom-Core, or Customer)
     *
     * @return string
     */
    public function RenderPreviewThumbnail($sImageSizeName, $sViewName = 'simple', $sViewType = 'Core', $aEffects = array())
    {
        $sHTML = '';
        $oPreviewImage = &$this->GetImagePreviewObject($sImageSizeName);
        if (!is_null($oPreviewImage)) {
            $sHTML = $oPreviewImage->Render($sViewName, $sViewType, $aEffects);
        } else {
            $oView = new TViewParser();
            /** @var $oView TViewParser */
            $oView->AddVar('oArticle', $this);
            $oView->AddVar('sImageSizeName', $sImageSizeName);
            $sHTML = $oView->RenderObjectPackageView('no-preview-image-defined', self::VIEW_PATH, $sViewType);
        }

        return $sHTML;
    }

    /**
     * return the primary image object of the shop article. this is either the cms_media_default_preview_image_id
     * or the  first image in the image list.
     *
     * @return TdbShopArticleImage
     */
    public function GetPrimaryImage()
    {
        $oPrimaryImage = &$this->GetFromInternalCache('oPrimaryImage');
        if (is_null($oPrimaryImage)) {
            if (!empty($this->fieldCmsMediaDefaultPreviewImageId) && (!is_numeric($this->fieldCmsMediaDefaultPreviewImageId) || intval($this->fieldCmsMediaDefaultPreviewImageId) > 1000)) {
                $oShop = TdbShop::GetInstance();
                $aData = array('shop_article_id' => $this->id, 'cms_media_id' => $this->fieldCmsMediaDefaultPreviewImageId, 'position' => 1);
                $oPrimaryImage = TdbShopArticleImage::GetNewInstance();
                $oPrimaryImage->LoadFromRow($aData);
            } else {
                $oImages = &$this->GetFieldShopArticleImageList();
                $activePage = $this->getActivePageService()->getActivePage();
                if (0 == $oImages->Length() && (!is_null($activePage))) {
                    $oShop = TdbShop::GetInstance();
                    $aData = array('shop_article_id' => $this->id, 'cms_media_id' => $oShop->fieldNotFoundImage, 'position' => 1);
                    $oPrimaryImage = TdbShopArticleImage::GetNewInstance();
                    $oPrimaryImage->LoadFromRow($aData);
                } else {
                    $oImages->GoToStart();
                    $oPrimaryImage = &$oImages->Current();
                }
            }

            if (false === $oPrimaryImage) {
                $oPrimaryImage = null;
                if ($this->IsVariant()) {
                    $oParent = $this->GetFieldVariantParent();
                    if (null != $oParent) {
                        $oPrimaryImage = $oParent->GetPrimaryImage();
                    }
                }
            }
            $this->SetInternalCache('oPrimaryImage', $oPrimaryImage);
        }

        return $oPrimaryImage;
    }

    /**
     * Enter description here...
     *
     * @param string $sImageSizeName - the image size name (found in shop_article_image_size)
     *
     * @return TdbShopArticlePreviewImage
     */
    public function &GetImagePreviewObject($sImageSizeName)
    {
        // check if the type has been defined...
        $oPreviewObject = TdbShopArticlePreviewImage::GetNewInstance();
        /** @var $oPreviewObject TdbShopArticlePreviewImage */
        if (!$oPreviewObject->LoadByName($this, $sImageSizeName)) {
            // if the article is a varaint, try the parent
            if ($this->IsVariant()) {
                $oParent = &$this->GetFieldVariantParent();
                $oPreviewObject = null;
                if (null != $oParent) {
                    $oPreviewObject = $oParent->GetImagePreviewObject($sImageSizeName);
                }
            } else {
                $oPreviewObject = null;
            }
        }

        return $oPreviewObject;
    }

    /* SECTION: CACHE RELEVANT METHODS FOR THE RENDER METHOD

    /**
     * Add view based clear cache triggers for the Render method here
     *
     * @param array $aClearTriggers - clear trigger array (with current contents)
     * @param string $sViewName - view being requested
     * @param string $sViewType - location of the view (Core, Custom-Core, Customer)
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function AddClearCacheTriggers(&$aClearTriggers, $sViewName, $sViewType)
    {
        if (!empty($this->fieldShopUnitOfMeasurementId)) {
            $aClearTriggers[] = array('table' => 'shop_unit_of_measurement', 'id' => $this->fieldShopUnitOfMeasurementId);
        }
    }

    /**
     * used to set the id of a clear cache (ie. related table).
     *
     * @param string $sTableName - the table name
     *
     * @return int|null|string
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function GetClearCacheTriggerTableValue($sTableName)
    {
        $sValue = '';
        switch ($sTableName) {
            case $this->table:
                $sValue = $this->id;
                break;

            default:
                break;
        }

        return $sValue;
    }

    /**
     * returns an array with all table names that are relevant for the render function.
     *
     * @param string $sViewName - the view name being requested (if know by the caller)
     * @param string $sViewType - the view type (core, custom-core, customer) being requested (if know by the caller)
     *
     * @return array
     */
    public static function GetCacheRelevantTables($sViewName = null, $sViewType = null)
    {
        $aTables = array();
        $aTables[] = 'shop_article';
        $aTables[] = 'shop_article_shop_category_mlt';
        $aTables[] = 'shop_category';
        $aTables[] = 'shop_article_shop_mlt';
        $aTables[] = 'shop_article_image';
        $aTables[] = 'shop_article_shop_article_mlt';
        $aTables[] = 'shop_manufacturer';
        $aTables[] = 'shop_article_review';
        $aTables[] = 'shop_article_preview_image';
        $aTables[] = 'shop_article_shop_article_group_mlt';
        $aTables[] = 'shop_article_contributor';
        $aTables[] = 'shop_contributor';
        $aTables[] = 'shop_contributor_type';
        $aTables[] = 'shop_bundle_article';
        $aTables[] = 'shop_article_marker';
        $aTables[] = 'shop_attribute';
        $aTables[] = 'shop_attribute_value';

        return $aTables;
    }

    /**
     * return true if the article is in at least one of the groups.
     *
     * @param array $aGroupList
     *
     * @return bool
     */
    public function IsInArticleGroups($aGroupList)
    {
        $bIsInGroups = false;
        $aArticleGroups = $this->GetMLTIdList('shop_article_group');
        $aIntersec = array_intersect($aArticleGroups, $aGroupList);

        if (0 == count($aIntersec) && 0 == count($aArticleGroups) && $this->IsVariant()) {
            $oParent = &$this->GetFieldVariantParent();
            if ($oParent) {
                $bIsInGroups = $oParent->IsInArticleGroups($aGroupList);
            }
        } else {
            $bIsInGroups = (count($aIntersec) > 0);
        }

        return $bIsInGroups;
    }

    /**
     * returns an article object with additional elements needed for exports and
     * interfaces like full URL image links, attachment links and so on.
     *
     * When called from CMS BackEnd it WILL use the first portal/shop found
     *
     * @return stdClass
     */
    public function GetExportObject()
    {
        $oExportObject = new stdClass();
        $oExportObject->id = $this->id;
        foreach ($this as $sPropName => $sPropVal) {
            if ('field' == substr($sPropName, 0, 5)) {
                $oExportObject->{$sPropName} = $sPropVal;
            }
        }

        // Add additional infos that might be useful for an export

        // Images
        if (TGlobal::IsCMSMode()) {
            $oPortals = &TdbCmsPortalList::GetList();
            $oPortal = $oPortals->Current();
            $oShop = &TdbShop::GetInstance($oPortal->id);
        } else {
            $oShop = TdbShop::GetInstance();
        }
        $oImageSizeList = &TdbShopArticleImageSizeList::GetListForShopId($oShop->id);
        $oExportObject->aImages = array();
        $oImagePropertyList = &$this->GetFieldShopArticleImageList();
        $aImageData = array('original' => array(), 'thumb' => array());
        while ($oImageProperty = &$oImagePropertyList->Next()) {
            $oImage = $oImageProperty->GetImage(0, 'cms_media_id');
            if (null !== $oImage) {
                $aImageData['original'][] = $oImage->GetFullURL();
                $aImageData['originalLocal'][] = $oImage->GetFullLocalPath();
            }
        }
        $oImageSizeList->GoToStart();
        while ($oImageSize = &$oImageSizeList->Next()) {
            $oPreviewImageDescription = &$this->GetImagePreviewObject($oImageSize->fieldNameInternal);
            if (null !== $oPreviewImageDescription) {
                $oImage = $oPreviewImageDescription->GetImageThumbnailObject();
                $aImageData['thumb'][$oImageSize->fieldNameInternal] = $oImage->GetFullURL();
                $aImageData['thumbLocal'][$oImageSize->fieldNameInternal] = $oImage->GetFullLocalPath();
            }
        }
        $oExportObject->aImages = $aImageData;

        // DeepLink
        $oExportObject->fieldDeepLink = $this->GetDetailLink(true);

        // Author Lost
        $oContributorList = $this->GetContributorList(array('author'));
        $aContributorList = array();
        while ($oContributor = $oContributorList->Next()) {
            $aContributorList[] = $oContributor->GetName();
        }

        $oExportObject->aAuthors = $aContributorList;

        // Stock Message
        $oExportObject->oStockMessage = $this->GetFieldShopStockMessage();

        return $oExportObject;
    }

    /**
     * return true if the article is in at least one of the categories.
     *
     * @param array $aCategoryList
     *
     * @return bool
     */
    public function IsInCategory($aCategoryList)
    {
        $bIsInCategory = false;
        if (in_array($this->fieldShopCategoryId, $aCategoryList)) {
            $bIsInCategory = true;
        } else {
            $aArticleCategories = $this->GetMLTIdList('shop_category_mlt');
            $aIntersec = array_intersect($aArticleCategories, $aCategoryList);
            if (0 == count($aIntersec) && 0 == count($aArticleCategories) && $this->IsVariant()) {
                $oParent = &$this->GetFieldVariantParent();
                if ($oParent) {
                    $bIsInCategory = $oParent->IsInCategory($aCategoryList);
                }
            } else {
                $bIsInCategory = (count($aIntersec) > 0);
            }
        }

        return $bIsInCategory;
    }

    /**
     * increase the product view counter by one.
     */
    public function UpdateProductViewCount()
    {
        if (CMS_SHOP_TRACK_ARTICLE_DETAIL_VIEWS && !is_null($this->id)) {
            $this->getProductStatsService()->add($this->id, ProductStatisticsServiceInterface::TYPE_DETAIL_VIEWS, 1);
        }
    }

    /**
     * updates the review stats for the article.
     */
    public function UpdateStatsReviews()
    {
        $sArticleId = $this->id;
        if ($this->IsVariant()) {
            $sArticleId = $this->fieldVariantParentId;
        }
        $iCount = $this->GetReviewCount(true);
        $dAvrg = $this->GetReviewAverageScore(true);
        $this->getProductStatsService()->set($this->id, ProductStatisticsServiceInterface::TYPE_REVIEW_AVERAGE, $dAvrg);
        $this->getProductStatsService()->set($this->id, ProductStatisticsServiceInterface::TYPE_REVIEW_COUNT, $iCount);
        TCacheManager::PerformeTableChange($this->table, $sArticleId);
    }

    /**
     * return keywords for the meta tag on article detail pages.
     *
     * @return array
     */
    public function GetMetaKeywords()
    {
        if (strlen(trim(strip_tags($this->fieldMetaKeywords))) > 0) {
            $aKeywords = explode(',', trim(strip_tags($this->fieldMetaKeywords)));
        } else {
            $aKeywords = explode(' ', $this->fieldName.' '.strip_tags($this->fieldDescriptionShort));
        }

        return $aKeywords;
    }

    /**
     * return meta description.
     *
     * @return string
     */
    public function GetMetaDescription()
    {
        $sDes = trim($this->fieldMetaDescription);
        if (empty($sDes)) {
            $sDes = mb_substr(strip_tags($this->fieldDescriptionShort.' '.$this->fieldDescription), 0, 160);
            $sDes = trim($sDes);
            if (empty($sDes)) {
                $sDes = $this->fieldName;
            }
        }

        return $sDes;
    }

    /**
     * loads the owning bundle order item IF this item belongs to a bundle. returns false if it is not.
     *
     * @return TdbShopOrderItem
     */
    public function &GetOwningBundleItem()
    {
        $oOwningBundleItem = $this->GetFromInternalCache('oOwningBundleItem');
        if (is_null($oOwningBundleItem)) {
            $oOwningBundleItem = false;
            if (!is_null($this->id)) {
                $query = "SELECT `shop_article`.*
                      FROM `shop_article`
                INNER JOIN `shop_bundle_article` ON `shop_article`.`id` = `shop_bundle_article`.`shop_article_id`
                     WHERE `shop_bundle_article`.`bundle_article_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                   ";
                if ($aOwner = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                    $oOwningBundleItem = TdbShopArticle::GetNewInstance();
                    /** @var $oOwningBundleItem TdbShopArticle */
                    $oOwningBundleItem->LoadFromRow($aOwner);
                }
            }
            $this->SetInternalCache('oOwningBundleItem', $oOwningBundleItem);
        }

        return $oOwningBundleItem;
    }

    /**
     * if this order item belongs to a bundle, then this method will return the connecting table.
     *
     * @return TdbShopBundleArticle
     */
    public function GetOwningBundleConnection()
    {
        $oOwningBundleConnection = $this->GetFromInternalCache('oOwningBundleConnection');
        if (is_null($oOwningBundleConnection)) {
            $oOwningBundleConnection = false;
            if (!is_null($this->id)) {
                $oOwningOrderItem = TdbShopBundleArticle::GetNewInstance();
                /** @var $oOwningOrderItem TdbShopBundleArticle */
                if (!$oOwningOrderItem->LoadFromField('bundle_article_id', $this->id)) {
                    $oOwningOrderItem = false;
                }
            }
            $this->SetInternalCache('oOwningBundleConnection', $oOwningBundleConnection);
        }

        return $oOwningBundleConnection;
    }

    /**
     * returns true if the item belongs to a bundle.
     *
     * @return bool
     */
    public function BelongsToABundle()
    {
        return false !== $this->GetOwningBundleConnection();
    }

    /**
     * return true if the article is a variant.
     *
     * @return bool
     */
    public function IsVariant()
    {
        return !empty($this->fieldVariantParentId);
    }

    /**
     * return true if the article has variants.
     *
     * @param bool $bCheckForActiveVariantsOnly
     *
     * @return bool
     */
    public function HasVariants($bCheckForActiveVariantsOnly = false)
    {
        if ($this->IsVariant()) {
            return false;
        }
        $sInternalCacheKey = 'bHasVariants';
        if ($bCheckForActiveVariantsOnly) {
            $sInternalCacheKey .= 'OnlyActive';
        }
        $bHasVariants = &$this->GetFromInternalCache($sInternalCacheKey);
        if (is_null($bHasVariants)) {
            $bHasVariants = false;
            if (!empty($this->fieldShopVariantSetId)) {
                $oVariants = $this->GetFieldShopArticleVariantsList(array(), $bCheckForActiveVariantsOnly);
                $bHasVariants = (!is_null($oVariants) && $oVariants->Length() > 0);
            }
            $this->SetInternalCache($sInternalCacheKey, $bHasVariants);
        }

        return $bHasVariants;
    }

    /**
     * load list of article variants.
     *
     * @param array $aSelectedTypeValues - restrict list to values matching this preselection (format: array(shop_variant_type_id=>shop_variant_type_value_id,...)
     * @param bool  $bLoadOnlyActive
     *
     * @return TdbShopArticleList
     */
    public function &GetFieldShopArticleVariantsList($aSelectedTypeValues = array(), $bLoadOnlyActive = true)
    {
        $sKey = 'oFieldShopArticleVariantsList'.serialize($aSelectedTypeValues);
        if ($bLoadOnlyActive) {
            $sKey .= 'active';
        } else {
            $sKey .= 'inactive';
        }
        $oVariantList = &$this->GetFromInternalCache($sKey);
        if (null === $oVariantList) {
            $connection = $this->getDatabaseConnection();
            $query = '';

            if (count($aSelectedTypeValues) > 0) {
                $query = 'SELECT `shop_article`.*
                            FROM `shop_article`
                       LEFT JOIN `shop_article_shop_variant_type_value_mlt` ON `shop_article`.`id` = `shop_article_shop_variant_type_value_mlt`.`source_id`
                       LEFT JOIN `shop_variant_type_value` ON `shop_article_shop_variant_type_value_mlt`.`target_id` = `shop_variant_type_value`.`id`
                           WHERE `shop_article`.`variant_parent_id` = %s';
                $query = sprintf($query, $connection->quote($this->id));
                $aRestriction = array();
                foreach ($aSelectedTypeValues as $sShopVariantTypeId => $sShopVariantTypeValueId) {
                    $aRestriction[] = sprintf(
                        '(`shop_variant_type_value`.`shop_variant_type_id` = %s AND `shop_variant_type_value`.`id` = %s)',
                        $connection->quote($sShopVariantTypeId),
                        $connection->quote($sShopVariantTypeValueId)
                    );
                }
                $query .= ' AND ('.implode(' OR ', $aRestriction).')';
                $query .= $this->getActiveRestriction($bLoadOnlyActive);
                $query .= ' GROUP BY `shop_article`.`id` HAVING COUNT(`shop_article`.`id`) = '.count($aSelectedTypeValues);
            } else {
                $query = 'SELECT `shop_article`.*
                            FROM `shop_article`
                           WHERE `shop_article`.`variant_parent_id` = %s ';
                $query = sprintf($query, $connection->quote($this->id));
                $query .= $this->getActiveRestriction($bLoadOnlyActive, $query);
            }

            $oVariantList = TdbShopArticleList::GetList($query);
            $oVariantList->bAllowItemCache = true;
            $this->SetInternalCache($sKey, $oVariantList);
        }
        $oVariantList->GoToStart();

        return $oVariantList;
    }

    private function getActiveRestriction(bool $loadOnlyActive): string
    {
        if (false === $loadOnlyActive) {
            return '';
        }

        $activeArticleQueryRestriction = TdbShopArticleList::GetActiveArticleQueryRestriction(false);
        if ('' === $activeArticleQueryRestriction) {
            return '';
        }

        return ' AND ('.$activeArticleQueryRestriction.')';
    }

    /**
     * returns the variant value set for this article as a string (if the article
     * is a variant. returns empty string if not).
     *
     * @param bool $bAsURLName
     *
     * @return string
     *
     * @deprecated since 6.2.0 - use ProductVariantNameGenerator::generateName() instead.
     */
    public function GetVariantName($bAsURLName = false)
    {
        if (true === $bAsURLName) {
            $nameType = ProductVariantNameGeneratorInterface::VARIANT_NAME_TYPE_URL;
        } else {
            $nameType = ProductVariantNameGeneratorInterface::VARIANT_NAME_TYPE_DEFAULT;
        }

        return $this->getProductVariantNameGenerator()->generateName($this, $nameType);
    }

    /**
     * return variant with the lowest price.
     *
     * @return TdbShopArticle
     */
    public function GetLowestPricedVariant()
    {
        $oLowestPrictedVariant = null;

        $oVariants = $this->GetFieldShopArticleVariantsList();
        while ($oVariant = $oVariants->Next()) {
            if (is_null($oLowestPrictedVariant) || $oVariant->fieldPrice < $oLowestPrictedVariant->fieldPrice) {
                $oLowestPrictedVariant = $oVariant;
            }
        }

        return $oLowestPrictedVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function &GetFieldShopVariantTypeValueList($sOrderBy = '')
    {
        $oVariantValueList = &$this->GetFromInternalCache('oFieldShopVariantTypeValueList');
        if (is_null($oVariantValueList)) {
            $query = "SELECT `shop_variant_type_value`.*
                    FROM `shop_variant_type_value`
              INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
              INNER JOIN `shop_article_shop_variant_type_value_mlt` ON `shop_variant_type_value`.`id` = `shop_article_shop_variant_type_value_mlt`.`target_id`
                   WHERE `shop_article_shop_variant_type_value_mlt`.`source_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                ORDER BY `shop_variant_type`.`position`
                 ";
            $oVariantValueList = TdbShopVariantTypeValueList::GetList($query, $this->iLanguageId);
            $this->SetInternalCache('oFieldShopVariantTypeValueList', $oVariantValueList);
        }
        $oVariantValueList->GoToStart();

        return $oVariantValueList;
    }

    /**
     * returns an array of IDs of all variant articles of this article.
     *
     * @param array $aSelectedTypeValues - restrict list to values matching this preselection (format: array(shop_variant_type_id=>shop_variant_type_value_id,...)
     * @param bool  $bLoadActiveOnly     - set to false if you want to load inactive articles too
     *
     * @return array
     */
    public function getVariantIDList($aSelectedTypeValues = array(), $bLoadActiveOnly = true)
    {
        $aArticleIdList = array();
        $oVariantList = null;
        if ($this->IsVariant()) {
            $oParent = &$this->GetFieldVariantParent();
            $oVariantList = $oParent->GetFieldShopArticleVariantsList($aSelectedTypeValues, $bLoadActiveOnly);
        } else {
            $oVariantList = $this->GetFieldShopArticleVariantsList($aSelectedTypeValues, $bLoadActiveOnly);
        }

        if ($oVariantList->Length() > 0) {
            $oVariantList->GoToStart();
            while ($oVariantArticle = $oVariantList->Next()) {
                $aArticleIdList[] = MySqlLegacySupport::getInstance()->real_escape_string($oVariantArticle->id);
            }
        }

        return $aArticleIdList;
    }

    /**
     * return all variant values that are available for the given type and this article.
     *
     * @param TdbShopVariantType $oVariantType
     * @param array              $aSelectedTypeValues - restrict list to values matching this preselection (format: array(shop_variant_type_id=>shop_variant_type_value_id,...)
     *
     * @return TdbShopVariantTypeValueList
     */
    public function GetVariantValuesAvailableForType($oVariantType, $aSelectedTypeValues = array())
    {
        $oVariantValueList = null;
        $aArticleIdList = $this->getVariantIDList($aSelectedTypeValues, true);

        if (count($aArticleIdList) > 0) {
            $query = "SELECT `shop_variant_type_value`.*
                FROM `shop_variant_type_value`
          INNER JOIN `shop_article_shop_variant_type_value_mlt` ON `shop_variant_type_value`.`id` = `shop_article_shop_variant_type_value_mlt`.`target_id`
               WHERE `shop_variant_type_value`.`shop_variant_type_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oVariantType->id)."'
                 AND `shop_article_shop_variant_type_value_mlt`.`source_id` IN ('".implode("','", $aArticleIdList)."')
            GROUP BY `shop_variant_type_value`.`id`
            ORDER BY `shop_variant_type_value`.`".MySqlLegacySupport::getInstance()->real_escape_string($oVariantType->fieldShopVariantTypeValueCmsfieldname).'`
             ';

            $oVariantValueList = TdbShopVariantTypeValueList::GetList($query);
        }

        return $oVariantValueList;
    }

    /**
     * return all variant values that are available for the given type and this article.
     *
     * @param TdbShopVariantType $oVariantType
     * @param array              $aSelectedTypeValues - restrict list to values matching this preselection (format: array(shop_variant_type_id=>shop_variant_type_value_id,...)
     *
     * @return null|TdbShopVariantTypeValueList
     */
    public function GetVariantValuesAvailableForTypeIncludingInActive($oVariantType, $aSelectedTypeValues = array())
    {
        $oVariantValueList = null;
        $aArticleIdList = $this->getVariantIDList($aSelectedTypeValues, false);

        if (count($aArticleIdList) > 0) {
            $query = "SELECT `shop_variant_type_value`.*, SUM(CASE WHEN `shop_article`.`active` = '1' THEN 1 ELSE 0 END) AS articleactive
                    FROM `shop_variant_type_value`
              INNER JOIN `shop_article_shop_variant_type_value_mlt` ON `shop_variant_type_value`.`id` = `shop_article_shop_variant_type_value_mlt`.`target_id`
              LEFT JOIN `shop_article` ON `shop_article`.`id` = `shop_article_shop_variant_type_value_mlt`.`source_id`
                   WHERE `shop_variant_type_value`.`shop_variant_type_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oVariantType->id)."'
                     AND `shop_article_shop_variant_type_value_mlt`.`source_id` IN ('".implode("','", $aArticleIdList)."')
                GROUP BY `shop_variant_type_value`.`id`
                ORDER BY `shop_variant_type_value`.`".MySqlLegacySupport::getInstance()->real_escape_string($oVariantType->fieldShopVariantTypeValueCmsfieldname).'`
                 ';
            $oVariantValueList = TdbShopVariantTypeValueList::GetList($query);
        }

        return $oVariantValueList;
    }

    /**
     * returns a list of variants for the current article each with a unqiue value for $sVariantTypeIdentifier.
     *
     * @param string $sVariantTypeIdentifier
     *
     * @return TdbShopArticleList
     */
    public function GetVariantsForVariantTypeName($sVariantTypeIdentifier)
    {
        $oArticleList = &$this->GetFromInternalCache('VariantsForVariantTypeName'.$sVariantTypeIdentifier);
        if (is_null($oArticleList)) {
            $oArticleList = null;
            $oVariantSet = &$this->GetFieldShopVariantSet();
            if (!is_null($oVariantSet)) {
                $oVariantType = $oVariantSet->GetVariantTypeForIdentifier($sVariantTypeIdentifier);
                if (!is_null($oVariantType)) {
                    $sQueryAddition = '';
                    if ($this->IsVariant()) {
                        $sCommmonParentId = $this->fieldVariantParentId;
                        // make sure the current article is part of the list
                        $sActingValueForTypeId = $this->GetVariantTypeActiveValue($oVariantType->id);
                        $sQueryAddition .= "AND ((`shop_article`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."' AND `shop_variant_type_value`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sActingValueForTypeId)."') OR (`shop_article`.`id` != '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."' AND `shop_variant_type_value`.`id` != '".MySqlLegacySupport::getInstance()->real_escape_string($sActingValueForTypeId)."'))";
                    } else {
                        $sCommmonParentId = $this->id;
                    }
                    $sActiveArticleRestriction = TdbShopArticleList::GetActiveArticleQueryRestriction(false);
                    if (!empty($sActiveArticleRestriction)) {
                        $sQueryAddition = $sQueryAddition.' AND ('.$sActiveArticleRestriction.')';
                    }

                    $query = "SELECT `shop_article`.*
                        FROM `shop_article`
                  INNER JOIN `shop_article_shop_variant_type_value_mlt` ON `shop_article`.`id` = `shop_article_shop_variant_type_value_mlt`.`source_id`
                  INNER JOIN `shop_variant_type_value` ON `shop_article_shop_variant_type_value_mlt`.`target_id` = `shop_variant_type_value`.`id`
                       WHERE `shop_article`.`variant_parent_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sCommmonParentId)."'
                         AND `shop_variant_type_value`.`shop_variant_type_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oVariantType->id)."'
                         {$sQueryAddition}
                    GROUP BY `shop_variant_type_value`.`id`
                     ";
                    if (!empty($oVariantType->fieldShopVariantTypeValueCmsfieldname)) {
                        $query .= 'ORDER BY `shop_variant_type_value`.`'.MySqlLegacySupport::getInstance()->real_escape_string($oVariantType->fieldShopVariantTypeValueCmsfieldname).'`';
                    }
                    //            print_r($query);exit();
                    $oArticleList = TdbShopArticleList::GetList($query);
                    $oArticleList->bAllowItemCache = true;
                    $this->SetInternalCache('VariantsForVariantTypeName'.$sVariantTypeIdentifier, $oArticleList);
                }
            }
        }

        return $oArticleList;
    }

    /**
     * returns the variant type value for the given identifier.
     *
     * note: there was a typo in the method name GetActiveVaraintValue
     * please change your custom code to use the renamed method
     * (the old method name is redirected, but will be removed in the future)
     *
     * @param string $sVariantTypeIdentifier
     *
     * @return TdbShopVariantTypeValue - returns null if $sVariantTypeIdentifier was not found
     */
    public function GetActiveVariantValue($sVariantTypeIdentifier)
    {
        $oVariantValueObject = null;
        $aVariantValues = &$this->GetFromInternalCache('aActiveVariantValues');
        if (is_null($aVariantValues)) {
            $query = "SELECT `shop_variant_type_value`.*, `shop_variant_type`.`identifier` AS variantTypeName
                    FROM `shop_variant_type_value`
              INNER JOIN `shop_article_shop_variant_type_value_mlt` ON `shop_variant_type_value`.`id` = `shop_article_shop_variant_type_value_mlt`.`target_id`
              INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
                   WHERE `shop_article_shop_variant_type_value_mlt`.`source_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                 ";
            $oVariantValues = TdbShopVariantTypeValueList::GetList($query);
            while ($oVariantValue = $oVariantValues->Next()) {
                $aVariantValues[$oVariantValue->sqlData['variantTypeName']] = $oVariantValue;
            }
            $this->SetInternalCache('aActiveVariantValues', $aVariantValues);
        }
        if (is_array($aVariantValues) && array_key_exists($sVariantTypeIdentifier, $aVariantValues)) {
            $oVariantValueObject = $aVariantValues[$sVariantTypeIdentifier];
        }

        return $oVariantValueObject;
    }

    /**
     * returns the id of the active value for the given variant type.
     *
     * @param string $sVariantTypeId
     *
     * @return string
     */
    public function GetVariantTypeActiveValue($sVariantTypeId)
    {
        $sValue = false;
        $aVariantValueIds = $this->GetFromInternalCache('aActiveVariantValueIds');
        if (is_null($aVariantValueIds)) {
            $oValueList = &$this->GetFieldShopVariantTypeValueList();
            while ($oValue = $oValueList->Next()) {
                $aVariantValueIds[$oValue->fieldShopVariantTypeId] = $oValue->id;
            }
            $oValueList->GoToStart();
            $this->SetInternalCache('aActiveVariantValueIds', $aVariantValueIds);
        }
        if (is_array($aVariantValueIds) && array_key_exists($sVariantTypeId, $aVariantValueIds)) {
            $sValue = $aVariantValueIds[$sVariantTypeId];
        }

        return $sValue;
    }

    /**
     * render the variant selection html using the display handler defined through the variant set.
     *
     * @param string $sViewName
     * @param string $sViewType
     *
     * @return string
     */
    public function RenderVariantSelection($sViewName = 'vStandard', $sViewType = 'Customer')
    {
        $sHTML = '';
        $oVariantSet = null;
        if ($this->IsVariant()) {
            $oParent = &$this->GetFieldVariantParent();

            $oVariantSet = &$oParent->GetFieldShopVariantSet();
        } else {
            $oVariantSet = &$this->GetFieldShopVariantSet();
        }
        if (!is_null($oVariantSet)) {
            $oHandler = $oVariantSet->GetFieldShopVariantDisplayHandler();
            $sHTML = $oHandler->Render($this, $sViewName, $sViewType);
        }

        return $sHTML;
    }

    /**
     * returns the variant set of the article (or its parent, if this is a variant).
     *
     * @return TdbShopVariantSet
     *
     * @deprecated You can use $this->GetFieldShopVariantSet() instead - it does the same thing
     */
    public function &GetVariantSet()
    {
        return $this->GetFieldShopVariantSet();
    }

    /**
     * return the variant matching the shop_variant_type_value paris.
     *
     * @param array $aTypeValuePairs
     *
     * @return TdbShopArticle
     *
     * @deprecated since 6.2.13 - replaced by ProductVariantServiceInterface::getProductBasedOnSelection()
     */
    public function GetVariantFromValues($aTypeValuePairs)
    {
        $oArticle = null;
        if (!$this->IsVariant()) {
            $query = "SELECT `shop_article`.*
                    FROM `shop_article`
              INNER JOIN `shop_article_shop_variant_type_value_mlt` ON `shop_article`.`id` = `shop_article_shop_variant_type_value_mlt`.`source_id`
              INNER JOIN `shop_variant_type_value` ON `shop_article_shop_variant_type_value_mlt`.`target_id` = `shop_variant_type_value`.`id`
                   WHERE `shop_article`.`variant_parent_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                 ";
            $aParts = array();
            foreach ($aTypeValuePairs as $sVariantTypeId => $sVariantTypeValueId) {
                $aParts[] = "`shop_variant_type_value`.`shop_variant_type_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sVariantTypeId)."' AND `shop_variant_type_value`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sVariantTypeValueId)."'";
            }
            $query .= ' AND ('.implode(') OR (', $aParts).')';
            $query .= ' GROUP BY `shop_article`.`id`';
            $query .= ' HAVING COUNT(`shop_article`.`id`) = '.MySqlLegacySupport::getInstance()->real_escape_string(count($aParts));
            $query .= ' ORDER BY `shop_article`.`active` DESC'; //we want the active article if multiple present
            if ($aMatch = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                $oArticle = TdbShopArticle::GetNewInstance();
                $oArticle->LoadFromRow($aMatch);
            }
        }

        return $oArticle;
    }

    /**
     * article detail images
     * returns a list object of all detail article images. Image #1 is used for preview most often.
     * The image may be overwritten using images in the preview image list.
     *
     * note: we overwrite this method to: cache the data AND implement a fallback if the current
     * article is a variant without any images
     *
     * @return TdbShopArticleImageList
     */
    public function &GetFieldShopArticleImageList()
    {
        $oImages = &$this->GetFromInternalCache('FieldShopArticleImageList');
        if (is_null($oImages)) {
            $query = TdbShopArticleImageList::GetDefaultQuery($this->iLanguageId, "`shop_article_image`.`shop_article_id`= '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."' AND `shop_article_image`.`cms_media_id` NOT IN ('','0','1','2','3','4','5','6','7','8','9','11','12','13','14')"); // exclude broken records and template images
            $oImages = TdbShopArticleImageList::GetList($query);
            $oImages->bAllowItemCache = true;
            if (0 == $oImages->Length() && $this->IsVariant()) {
                $oParent = &$this->GetFieldVariantParent();
                if ($oParent) {
                    $oImages = &$oParent->GetFieldShopArticleImageList();
                } else {
                    trigger_error('Variants [ID = '.TGlobal::OutHTML($this->id).'] parent is missing', E_USER_WARNING);
                }
            }
            $this->SetInternalCache('FieldShopArticleImageList', $oImages);
        }
        if (!is_null($oImages)) {
            $oImages->GoToStart();
        }

        return $oImages;
    }

    /**
     * return the message consumer name of this article. note that varaints
     * always talk through their parents - so if you want to talk to the
     * variant directly, you will need to overwrite this method.
     *
     * @return string
     */
    public function GetMessageConsumerName()
    {
        $sId = $this->id;
        if ($this->IsVariant()) {
            $sId = $this->fieldVariantParentId;
        }

        return TdbShopArticle::MSG_CONSUMER_BASE_NAME.$sId;
    }

    /**
     * Get bundle article list - if we are a variant an no bundle articles are set, the we return the bundles of the parent.
     *
     * @return TdbShopBundleArticleList
     */
    public function &GetFieldShopBundleArticleList()
    {
        $oBundleList = parent::GetFieldShopBundleArticleList();
        if (0 == $oBundleList->Length() && $this->IsVariant()) {
            $oBundleList = $this->GetFieldVariantParent()->GetFieldShopBundleArticleList();
        }
        $oBundleList->bAllowItemCache = true;

        return $oBundleList;
    }

    /**
     * article properties/markers - if we are a variant and no markers are set, then we return the markers of
     * the parent.
     *
     * @param string $sOrderBy - an sql order by string (without the order by)
     *
     * @return TdbShopArticleMarkerList
     */
    public function GetFieldShopArticleMarkerList($sOrderBy = '')
    {
        $oMarkerList = $this->GetMLT('shop_article_marker_mlt', 'TdbShopArticleMarker', $sOrderBy, 'CMSDataObjects', 'Core');
        if (0 == $oMarkerList->Length() && $this->IsVariant()) {
            $oMarkerList = $this->GetFieldVariantParent()->GetFieldShopArticleMarkerList($sOrderBy);
        }
        $oMarkerList->bAllowItemCache = true;

        return $oMarkerList;
    }

    /**
     * get article attribute value list - if we are a variant and no attribute values are set, then we return the attribute values of
     * the parent.
     *
     * @param string $sOrderBy - an sql order by string (without the order by)
     *
     * @return TdbShopAttributeValueList
     */
    public function GetFieldShopAttributeValueList($sOrderBy = '')
    {
        $oAttributeValueList = parent::GetFieldShopAttributeValueList($sOrderBy);
        if (0 == $oAttributeValueList->Length() && $this->IsVariant()) {
            $oAttributeValueList = $this->GetFieldVariantParent()->GetFieldShopAttributeValueList($sOrderBy);
        }
        $oAttributeValueList->bAllowItemCache = true;

        return $oAttributeValueList;
    }

    /**
     * return the name of the article optimized for search engines (includes manufacturer and category path).
     *
     * @return string
     */
    public function GetNameSEO()
    {
        $sFullProductName = $this->GetFromInternalCache('sFullSEOName');
        if (is_null($sFullProductName)) {
            $sFullProductName = '';
            $oManufacturer = $this->GetFieldShopManufacturer();
            if ($oManufacturer) {
                $sFullProductName .= $oManufacturer->fieldName.': ';
            }
            $oArticleCategory = $this->GetPrimaryCategory();
            if ($oArticleCategory) {
                $oCatBreadCrumb = $oArticleCategory->GetBreadcrumb();
                while ($oCatItem = $oCatBreadCrumb->Next()) {
                    $sFullProductName .= $oCatItem->GetName().' - ';
                }
            }
            $sFullProductName .= $this->fieldName;
            $this->SetInternalCache('sFullSEOName', $sFullProductName);
        }

        return $sFullProductName;
    }

    /**
     * return the name formated for the breadcrumb.
     */
    public function GetBreadcrumbName()
    {
        return $this->GetName();
    }

    /**
     * Get SEO pattern of current article.
     *
     * @param string $sPaternIn SEO pattern string
     *
     * @return array SEO pattern replace values
     */
    public function GetSeoPattern(&$sPaternIn)
    {
        //$sPaternIn = "[{PORTAL_NAME}] - [{CATEGORY_NAME}] - [{ARTICLE_NAME}]"; //default
        $aPatRepl = null;

        if (!empty($this->sqlData['seo_pattern'])) {
            $sPaternIn = $this->sqlData['seo_pattern'];
        }

        $aPatRepl = array();
        $activePage = $this->getActivePageService()->getActivePage();
        $aPatRepl['PORTAL_NAME'] = $activePage->GetPortal()->GetTitle();

        $activeCategory = $this->getShopService()->getActiveCategory();
        if (is_object($activeCategory)) {
            $aPatRepl['CATEGORY_NAME'] = $activeCategory->GetName();
        }

        $oManufacturer = $this->GetFieldShopManufacturer();
        if (is_object($oManufacturer)) {
            $aPatRepl['MANUFACTURER_NAME'] = $oManufacturer->GetName();
        }

        $aPatRepl['ARTICLE_NAME'] = $this->GetName();

        return $aPatRepl;
    }

    /**
     * variant set
     * Using variant sets it is possible to set variant types for the article variants
     * (e.g. Color and Size).
     *
     * @return TdbShopVariantSet
     */
    public function &GetFieldShopVariantSet()
    {
        $oItem = &$this->GetFromInternalCache('oFieldShopVariantSet');
        if (null === $oItem) {
            $oItem = &parent::GetFieldShopVariantSet();
            if (null === $oItem && $this->IsVariant()) {
                $oParent = &$this->GetFieldVariantParent();
                $oItem = &$oParent->GetFieldShopVariantSet();
            }
            $this->SetInternalCache('oFieldShopVariantSet', $oItem);
        }

        return $oItem;
    }

    /**
     * returns true if the article is buyable, false if it is not.
     *
     * @return bool
     */
    public function IsBuyable()
    {
        $bIsBuyable = &$this->GetFromInternalCache('bIsBuyable');
        if (is_null($bIsBuyable)) {
            $bIsBuyable = $this->isActive();
            if ($bIsBuyable) {
                $oShopConfig = TdbShop::GetInstance();

                if (isset($oShopConfig->fieldAllowPurchaseOfVariantParents) &&
                    !$oShopConfig->fieldAllowPurchaseOfVariantParents &&
                    ($this->HasVariants() || ('' != $this->fieldShopVariantSetId && false === $this->IsVariant()))) {
                    $bIsBuyable = false;
                }
            }
            $this->SetInternalCache('bIsBuyable', $bIsBuyable);
        }

        return $bIsBuyable;
    }

    /**
     * @return ProductStatisticsServiceInterface
     */
    protected function getProductStatsService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.product_stats_service');
    }

    /**
     * the methods recalculates the total available stock for a base article based on
     * the articles variants. You can call this either on the primary article or on
     * a variant - both calls will result in an update to the parent article.
     *
     * @param float $dNewStockValue     - optional: you can update the stock of the current article to this new value
     * @param bool  $bNewAmountIsDelta  - set to true if you want to increase or decrease the amount by some quantity
     * @param bool  $bUpdateSaleCounter - set to true if you also want to update the sales counter (IMPORTANT: changes the sales counter ONLY if $bNewAmountIsDelta is also set to true)
     * @param bool  $bForceUpdate       - set to true, if you want to trigger update action, even if nothing changed (needed for example, if an article is changed via the table editor)
     *
     * @return bool - return true if some data was changed
     */
    public function UpdateStock($dNewStockValue, $bNewAmountIsDelta = false, $bUpdateSaleCounter = false, $bForceUpdate = false)
    {
        if ($this->HasVariants()) {
            return false;
        }
        $oldStock = $this->getAvailableStock();
        // NOTE the below comparison always has true as result (compares int to double); see https://github.com/chameleon-system/chameleon-system/issues/120
        $stockIsChanging = ($bNewAmountIsDelta || $oldStock !== $dNewStockValue);
        if (false === $stockIsChanging && false === $bUpdateSaleCounter && false === $bForceUpdate) {
            return false;
        }

        if ($bNewAmountIsDelta) {
            $this->getInventoryService()->addStock($this->id, $dNewStockValue);
        } else {
            $this->getInventoryService()->setStock($this->id, $dNewStockValue);
        }

        if ($bUpdateSaleCounter && $bNewAmountIsDelta) {
            $dSaleAmount = -1 * $dNewStockValue;
            $this->getProductStatsService()->add($this->id, ProductStatisticsServiceInterface::TYPE_SALES, $dSaleAmount);
        }

        $newStock = $this->getAvailableStock();

        $bActive = $this->CheckActivateOrDeactivate($newStock);
        $this->setIsActive(1 === $bActive);

        // check if the article is part of a bundle... if it is, make sure the bundle article does not exceed the total number of single items
        $query = 'SELECT shop_article.*,
                     shop_bundle_article.amount AS ItemsPerBundle,
                     (shop_bundle_article.amount * shop_article_stock.amount) AS required_stock
                FROM shop_article
          INNER JOIN shop_bundle_article ON shop_article.id = shop_bundle_article.shop_article_id
           LEFT JOIN shop_article_stock ON shop_article.id = shop_article_stock.shop_article_id
               WHERE shop_bundle_article.bundle_article_id = :articleId
                 AND (shop_bundle_article.amount * shop_article_stock.amount) > :newStock
               ';
        $aBundleChangeList = $this->getDatabaseConnection()->fetchAll($query, array('articleId' => $this->id, 'newStock' => $newStock), array('articleId' => \PDO::PARAM_STR, 'newStock' => \PDO::PARAM_INT));
        foreach ($aBundleChangeList as $aBundleChange) {
            $iAllowedStock = floor($newStock / $aBundleChange['ItemsPerBundle']);
            $oBundleArticle = TdbShopArticle::GetNewInstance();
            $oBundleArticle->LoadFromRow($aBundleChange);
            $oBundleArticle->UpdateStock($iAllowedStock, false, false);
        }

        if ($oldStock !== $newStock || true === $bForceUpdate) {
            $this->StockWasUpdatedHook($oldStock, $newStock);
            $this->getEventDispatcher()->dispatch(ShopEvents::UPDATE_PRODUCT_STOCK, new UpdateProductStockEvent($this->id, $newStock, $oldStock));
        }

        return $oldStock !== $newStock;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        if ($this->fieldActive === $isActive) {
            return;
        }
        $activeValue = (true === $isActive) ? '1' : '0';
        $query = 'UPDATE shop_article SET `active` = :active WHERE id = :id';
        $affectedRows = $this->getDatabaseConnection()->executeUpdate($query, array('active' => $activeValue, 'id' => $this->id));

        $query = 'UPDATE shop_article SET variant_parent_is_active = :active WHERE variant_parent_id = :id';
        $this->getDatabaseConnection()->executeUpdate($query, array('active' => $activeValue, 'id' => $this->id));

        if ($affectedRows > 0) {
            $this->getCache()->callTrigger('shop_article', $this->id);
        }
    }

    /**
     * @param string $parentId
     * @param bool   $isActive
     */
    public function setVariantParentActive($parentId, $isActive)
    {
        if (true === $isActive) {
            $activeValue = 1;
        } else {
            $activeValue = 0;
        }

        $databaseConnection = $this->getDatabaseConnection();
        $query = 'UPDATE `shop_article`
                  SET `active` = :activeValue, `variant_parent_is_active` = :activeValue
                  WHERE `id` = :parentId';
        $parameters = array(
            'activeValue' => $activeValue,
            'parentId' => $parentId,
        );
        $affectedRows = $databaseConnection->executeUpdate($query, $parameters);

        if ($affectedRows > 0) {
            $this->getCache()->callTrigger('shop_article', $parentId);
        }

        $this->UpdateVariantParentActiveField();
    }

    protected function UpdateVariantParentActiveField()
    {
        $bParentActiveChanged = false;
        if ($this->IsVariant()) {
            $sParentId = $this->fieldVariantParentId;
            $oParentArticle = TdbShopArticle::GetNewInstance();
            $sActiveValue = $this->fieldVariantParentIsActive;
            if ($oParentArticle->Load($sParentId)) {
                if ($oParentArticle->fieldActive != $sActiveValue) {
                    $sActiveValue = $oParentArticle->fieldActive;
                    $bParentActiveChanged = true;
                }
            }
        } elseif ($this->HasVariants()) {
            $sParentId = $this->id;
            $sActiveValue = $this->fieldActive;
            $bParentActiveChanged = true;
        }
        if ($bParentActiveChanged) {
            if (true === $sActiveValue) {
                $sActiveValue = 1;
            } else {
                $sActiveValue = 0;
            }

            $this->sqlData['variant_parent_is_active'] = $sActiveValue;
            $this->fieldVariantParentIsActive = (1 == $sActiveValue) ? (true) : (false);
            $sQuery = "UPDATE `shop_article` SET `variant_parent_is_active` = '".MySqlLegacySupport::getInstance()->real_escape_string($sActiveValue)."'
                      WHERE `shop_article`.`variant_parent_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sParentId)."'";
            MySqlLegacySupport::getInstance()->query($sQuery);
        }
    }

    /**
     * hook called when the stock value of the article changed.
     *
     * @param float $dOldValue
     * @param float $dNewValue
     */
    protected function StockWasUpdatedHook($dOldValue, $dNewValue)
    {
    }

    /**
     * checks if we would deactivate the article if the stock of the article was dNewStockValue
     * returns 1 if the article remains active, 0 if it would be deactivated.
     *
     * @param float $dNewStockValue - the stock we want to check the state of the article for
     *
     * @return int
     */
    public function CheckActivateOrDeactivate($dNewStockValue = null)
    {
        if (null === $dNewStockValue) {
            $dNewStockValue = $this->getAvailableStock();
        }
        $isActive = 0;
        if ($this->fieldActive) {
            $isActive = 1;
        }
        $oStockMessage = &$this->GetFieldShopStockMessage();
        if ($oStockMessage) {
            if ($oStockMessage->fieldAutoActivateOnStock && $dNewStockValue > 0) {
                $isActive = 1;
            }
            if ($oStockMessage->fieldAutoDeactivateOnZeroStock && $dNewStockValue < 1) {
                $isActive = 0;
            }
        }

        return $isActive;
    }

    /**
     * @return ShopStockMessageDataAccessInterface
     */
    protected function getShopStockMessageDataAccess()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_stock_message_data_access');
    }

    /**
     * the method gets the shopstockmessage for the current article.
     *
     * @return null|TdbShopStockMessage
     */
    public function &GetFieldShopStockMessage()
    {
        $oItem = $this->GetFromInternalCache('oLookupshop_stock_message_id');
        if (null === $oItem) {
            $oItem = $this->getShopStockMessageDataAccess()->getStockMessage($this->fieldShopStockMessageId, $this->iLanguageId);
            if (null !== $oItem) {
                $this->SetInternalCache('oLookupshop_stock_message_id', $oItem);
            }
        }
        if (null !== $oItem && false === $oItem->GetArticle()) {
            $oItem->SetArticle($this);
        }

        if (null !== $oItem && false === is_array($oItem->sqlData)) {
            $oItem = null;
        }

        return $oItem;
    }

    /*
    * return the number of items on stock. this will either return the actual stock
    * count, OR 999.999 if an unlimited number of items may be purchased.
    * @return double
    */
    public function TotalStockAvailable()
    {
        $bStock = $this->getAvailableStock();
        // if the article is set to auto deactivate on zero stock, we have a limited
        // supply. if on the other hand, the article remains active on zero stock, we have an
        // unlimited supply
        if (1 == $this->CheckActivateOrDeactivate(0)) {
            // we may also not allow any more purchases than available stock, if the article is marked as show_preorder_on_zero_stock
            if (false == $this->fieldShowPreorderOnZeroStock) {
                $bStock = 999999;
            }
        }

        return $bStock;
    }

    /**
     * returns the real amount available.
     *
     * @return int
     */
    public function getAvailableStock()
    {
        return $this->getInventoryService()->getAvailableStock($this->id);
    }

    /**
     * @return ProductInventoryServiceInterface
     */
    protected function getInventoryService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.product_inventory_service');
    }

    /**
     * return a list of discounts matching the article - we only ever includ discounts that
     * have no basket restriction. If $bIncludeUserRestrictedDiscounts is set to true, we also return discounts that match the current user.
     *
     * @param bool $bIncludeUserRestrictedDiscounts
     *
     * @return TdbShopDiscountList
     */
    public function GetDiscountList($bIncludeUserRestrictedDiscounts = false)
    {
        $sDiscountListKey = 'oArticlePotentialDiscountList';
        if ($bIncludeUserRestrictedDiscounts) {
            $sDiscountListKey .= '-IncludesUserDiscounts';
        }
        $oDiscountList = $this->GetFromInternalCache($sDiscountListKey);
        if (is_null($oDiscountList)) {
            $oDiscountList = TdbShopDiscountList::GetActiveDiscountListForArticle($this, $bIncludeUserRestrictedDiscounts);
            $this->SetInternalCache($sDiscountListKey, $oDiscountList);
        }

        return $oDiscountList;
    }

    /*
    * returns what the product would cost, if the discounts defined through $this->GetDiscountList are applied.
    * (this is the price that a user ends up paying, if the item is added to the basket - ignoring basket specific discounts (they will be applied in the baske))
    * use this, if you want to show the user what the could save
    *
    * If there are no discounts, we instead return the standard price of the imte
    * @return double
    */
    public function GetPriceIfDiscounted()
    {
        $dPrice = $this->GetFromInternalCache('dDiscountedPrice');
        if (is_null($dPrice)) {
            $dPrice = $this->dPrice;
            $dDiscountSum = 0;
            $oDiscountList = $this->GetDiscountList();
            if ($oDiscountList && $oDiscountList->Length() > 0) {
                $oDiscountList->GoToStart();
                while ($oDiscount = $oDiscountList->Next()) {
                    if ('prozent' == $oDiscount->fieldValueType) {
                        $dDiscountSum += round($dPrice * ($oDiscount->fieldValue / 100), 2);
                    } else {
                        $dDiscountSum += $oDiscount->fieldValue;
                    }
                }
                $oDiscountList->GoToStart();
            }
            $dPrice = $dPrice - $dDiscountSum;
            if ($dPrice < 0) {
                $dPrice = 0;
            }
            $this->SetInternalCache('dDiscountedPrice', $dPrice);
        }

        return $dPrice;
    }

    /**
     * if called, we update fieldPrice and fieldPriceReference based on the GetPriceIfDiscounted() value.
     * Note: call this method whenever you want to show the savings that could result from a discount.
     */
    public function SetPriceBasedOnActiveDiscounts()
    {
        // this should only ever be executed once...
        $bDiscountedPriceUsed = $this->GetFromInternalCache('bDiscountedPriceUsed');
        if (is_null($bDiscountedPriceUsed) || false == $bDiscountedPriceUsed) {
            $bDiscountedPriceUsed = true;
            $dDiscountValue = $this->GetPriceIfDiscounted();
            if ($dDiscountValue < $this->fieldPrice) {
                $this->aPriceBeforeDiscount['fieldPrice'] = $this->fieldPrice;
                $this->aPriceBeforeDiscount['fieldPriceReference'] = $this->fieldPriceReference;
                $oLocal = &TCMSLocal::GetActive();
                $this->fieldPriceReference = $this->fieldPrice;
                $this->fieldPriceReferenceFormated = $this->fieldPriceFormated;
                $this->fieldPrice = $dDiscountValue;
                $this->fieldPriceFormated = $oLocal->FormatNumber($dDiscountValue, 2);
                $this->dPrice = $this->fieldPrice;
            }
            $this->SetInternalCache('bDiscountedPriceUsed', $bDiscountedPriceUsed);
        }
    }

    /**
     * if there are discounts affecting the article price pre-add-basket (ie set via SetPriceBasedOnActiveDiscounts())
     * then you can use this method to get the price of the article BEFORE the discounts were applied.
     *
     * @return float
     */
    public function getPriceWithoutActiveDiscounts()
    {
        if (true === isset($this->aPriceBeforeDiscount['fieldPrice'])) {
            return $this->aPriceBeforeDiscount['fieldPrice'];
        } else {
            return $this->fieldPrice;
        }
    }

    /**
     * if there are discounts affecting the article price pre-add-basket (ie set via SetPriceBasedOnActiveDiscounts())
     * then you can use this method to get the priceReference of the article BEFORE the discounts were applied.
     *
     * @return float
     */
    public function getPriceReferenceWithoutActiveDiscounts()
    {
        if (true === isset($this->aPriceBeforeDiscount['fieldPriceReference'])) {
            return $this->aPriceBeforeDiscount['fieldPriceReference'];
        } else {
            return $this->fieldPriceReference;
        }
    }

    /**
     * fallback for renamed/deprecated methods.
     *
     * @param string $name      - name of the method case sensitive
     * @param array  $arguments
     */
    public function __call($name, $arguments)
    {
        $aBackwardsCompatMethods = array();
        $aBackwardsCompatMethods['GetActiveVaraintValue'] = 'GetActiveVariantValue';

        if (array_key_exists($name, $aBackwardsCompatMethods)) {
            $sNewMethodName = $aBackwardsCompatMethods[$name];

            return $this->$sNewMethodName(implode(', ', $arguments));
        }
    }

    /**
     * Get all post and get parameters an add them to parameter list.
     *
     * @param array $aParameters
     *
     * @return array
     */
    private function getToBasketLinkOtherParameters($aParameters = array())
    {
        $aExcludeParameters = TCMSSmartURLData::GetActive()->getSeoURLParameters();

        foreach ($aParameters as $sKey => $sVal) {
            $aExcludeParameters[] = $sKey;
        }

        // now add all OTHER parameters
        $aOtherParameters = TGlobal::instance()->GetUserData(null, $aExcludeParameters);
        foreach ($aOtherParameters as $sKey => $sVal) {
            $aParameters[$sKey] = $sVal;
        }

        return $aParameters;
    }

    /**
     * Get only needed parameter for to basket link.
     * Use this instead of GetToBasketLinkParameters() if not want to add all get or post parameters.
     *
     * @param bool   $bRedirectToBasket      - redirect to basket page after adding product
     * @param bool   $bReplaceBasketContents - set to true if you want the contents of the basket to be replaced by the product wenn added to basket
     * @param bool   $bGetAjaxParameter      - set to true if you want to get basket link for ajax call
     * @param string $sMessageConsumer       - set custom message consumer
     *
     * @return array
     */
    public function getToBasketLinkBasketParameters($bRedirectToBasket = false, $bReplaceBasketContents = false, $bGetAjaxParameter = false, $sMessageConsumer = MTShopBasketCore::MSG_CONSUMER_NAME_MINIBASKET)
    {
        $oShopConfig = TdbShop::GetInstance();
        $aBasketData = array();
        $aParameters = array();
        if ($bGetAjaxParameter) {
            $aModuleFnc = array($oShopConfig->GetBasketModuleSpotName() => 'ExecuteAjaxCall');
            $aParameters['_fnc'] = 'AddToBasketAjax';
        } else {
            $aModuleFnc = array($oShopConfig->GetBasketModuleSpotName() => 'AddToBasket');
        }

        $aBasketData[MTShopBasketCore::URL_ITEM_ID_NAME] = $this->id;
        $aBasketData[MTShopBasketCore::URL_ITEM_AMOUNT_NAME] = 1;
        $aBasketData[MTShopBasketCore::URL_MESSAGE_CONSUMER_NAME] = $sMessageConsumer;
        if ($bRedirectToBasket) {
            $aBasketData[MTShopBasketCore::URL_REDIRECT_NODE_ID_NAME] = $oShopConfig->GetSystemPageNodeId('checkout');
        }
        if ($bReplaceBasketContents) {
            $aBasketData[MTShopBasketCore::URL_CLEAR_BASKET_NAME] = 'true';
        }
        $aParameters['module_fnc'] = $aModuleFnc;
        $aParameters[MTShopBasketCore::URL_REQUEST_PARAMETER] = $aBasketData;

        return $aParameters;
    }

    /**
     * Get to basket link containing only needed basket parameters.
     * Use this instead of GetToBasketLink() if not want to add all get or post parameters.
     *
     * @param bool   $bIncludePortalLink
     * @param bool   $bRedirectToBasket      - redirect to basket page after adding product
     * @param bool   $bReplaceBasketContents - set to true if you want the contents of the basket to be replaced by the product wenn added to basket
     * @param bool   $bGetAjaxParameter      - set to true if you want to get basket link for ajax call
     * @param string $sMessageConsumer       - set custom message consumer
     *
     * @return array
     */
    public function getToBasketLinkBasketParametersOnly($bIncludePortalLink = false, $bRedirectToBasket = false, $bReplaceBasketContents = false, $bGetAjaxParameter = false, $sMessageConsumer = MTShopBasketCore::MSG_CONSUMER_NAME_MINIBASKET)
    {
        $aParameters = $this->getToBasketLinkBasketParameters($bRedirectToBasket, $bReplaceBasketContents, $bGetAjaxParameter, $sMessageConsumer);

        return $this->generateLinkForToBasketParameters($aParameters, $bIncludePortalLink);
    }

    /**
     * Checks if the article is active.
     * For a variant to be active, the parent has to be active, too.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->fieldActive && $this->fieldVariantParentIsActive;
    }

    /**
     * @return UrlNormalizationUtil
     */
    private function getUrlNormalizationUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.url_normalization');
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }

    /**
     * @return UrlUtil
     */
    private function getUrlUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.url');
    }

    /**
     * @return PortalAndLanguageAwareRouterInterface
     */
    private function getFrontendRouter()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.router.chameleon_frontend');
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('event_dispatcher');
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    /**
     * @return ProductVariantNameGeneratorInterface
     */
    private function getProductVariantNameGenerator()
    {
        return ServiceLocator::get('chameleon_system_shop.product_variant.product_variant_name_generator');
    }

    private function getCache(): CacheInterface
    {
        return ServiceLocator::get('chameleon_system_cms_cache.cache');
    }
}
