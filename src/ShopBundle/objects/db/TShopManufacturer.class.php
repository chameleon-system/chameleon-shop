<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Util\UrlNormalization\UrlNormalizationUtil;

class TShopManufacturer extends TShopManufacturerAutoParent
{
    const VIEW_PATH = 'pkgShop/views/db/TShopManufacturer';
    const FILTER_KEY_NAME = 'shop_manufacturer_id';

    /**
     * return link to the product pages for the manufacturer.
     *
     * @param bool $bUseAbsoluteURL
     *
     * @return string
     */
    public function GetLinkProducts($bUseAbsoluteURL = false)
    {
        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $sLink = $oShop->GetLinkToSystemPage('manufacturer');
        if ('.html' == substr($sLink, -5)) {
            $sLink = substr($sLink, 0, -5).'/';
        }
        $sLink = $sLink.$this->getUrlNormalizationUtil()->normalizeUrl($this->fieldName).'/id/'.urlencode($this->id);

        return $sLink;
    }

    /**
     * returns a link that restricts the current search to the category.
     *
     * @return string
     */
    public function GetSearchRestrictionLink()
    {
        // get current search... then add filter
        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $oSearchCache = $oShop->GetActiveSearchObject();
        //$oSearchCache->aFilter[TdbShopManufacturer::FILTER_KEY_NAME] = $this->id;
        return $oSearchCache->GetSearchLink(array(TdbShopManufacturer::FILTER_KEY_NAME => $this->id));
    }

    /**
     * return an instance for the current filter (if the filter defines a manufacturer)
     * null if it does not.
     *
     * @return TdbShopManufacturer|null
     */
    public static function GetInstanceForCurrentFilter()
    {
        static $oInstance = false;
        if (false === $oInstance) {
            $oInstance = null;
            $aFilter = TdbShop::GetActiveFilter();
            if (array_key_exists('shop_manufacturer_id', $aFilter) && !empty($aFilter['shop_manufacturer_id'])) {
                $oInstance = TdbShopManufacturer::GetNewInstance();
                /** @var $oInstance TdbShopManufacturer */
                if (!$oInstance->Load($aFilter['shop_manufacturer_id'])) {
                    $oInstance = null;
                }
            }
        }

        return $oInstance;
    }

    /**
     * used to display the manufacturer.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Core', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        $oView->AddVar('oManufacturer', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbShopManufacturer::VIEW_PATH, $sViewType);
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
     * returns the number of search hits for a manufacturer.
     *
     * @param int  $iShopSearchCacheId
     * @param bool $bApplyActiveFilter - set to true if you want to count only hits that match the current filter
     *
     * @return int
     */
    public function GetNumberOfHitsForSearchCacheId($iShopSearchCacheId, $bApplyActiveFilter = false)
    {
        $iNumHits = 0;

        if ($bApplyActiveFilter) {
            $query = "SELECT COUNT(DISTINCT `shop_search_cache_item`.`id`) AS hits
                    FROM `shop_search_cache_item`
              INNER JOIN `shop_article` ON `shop_search_cache_item`.`shop_article_id` = `shop_article`.`id`
               LEFT JOIN `shop_article_shop_category_mlt` ON `shop_article`.`id` = `shop_article_shop_category_mlt`.`source_id`
                   WHERE `shop_search_cache_item`.`shop_search_cache_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($iShopSearchCacheId)."'
                     AND `shop_article`.`shop_manufacturer_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                  ";
            $sFilter = TdbShop::GetActiveFilterString(TdbShopManufacturer::FILTER_KEY_NAME);
            if (!empty($sFilter)) {
                $query .= " AND {$sFilter}";
            }

            $query .= ' GROUP BY `shop_article`.`shop_manufacturer_id`';
        //        echo "<pre>".$query."</pre><br>";
        } else {
            $query = "SELECT COUNT(DISTINCT `shop_search_cache_item`.`id`) AS hits
                    FROM `shop_search_cache_item`
              INNER JOIN `shop_article` ON `shop_search_cache_item`.`shop_article_id` = `shop_article`.`id`
                   WHERE `shop_search_cache_item`.`shop_search_cache_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($iShopSearchCacheId)."'
                     AND `shop_article`.`shop_manufacturer_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                GROUP BY `shop_article`.`shop_manufacturer_id`
                 ";
        }
        if ($row = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
            $iNumHits = $row['hits'];
        }

        return $iNumHits;
    }

    /**
     * return the icon for the manufacturer. returns false if none found.
     *
     * @param bool $bReturnDefaultImageIfNoneSet
     * @return TCMSImage|false
     */
    public function GetIcon($bReturnDefaultImageIfNoneSet = false)
    {
        /** @var TCMSImage|null $oIcon */
        $oIcon = $this->GetFromInternalCache('oIcon');
        if (is_null($oIcon)) {
            $oIcon = $this->GetImage(0, 'cms_media_id', $bReturnDefaultImageIfNoneSet);
            if (is_null($oIcon)) {
                $oIcon = false;
            }
            $this->SetInternalCache('oIcon', $oIcon);
        }

        return $oIcon;
    }

    /**
     * return the logo for the manufacturer. if none has been set, it will return the icon instead
     * if there is no icon either, it will return false.
     *
     * @param bool $bReturnDefaultImageIfNoneSet
     * @return TCMSImage|false
     */
    public function GetLogo($bReturnDefaultImageIfNoneSet = false)
    {
        /** @var TCMSImage|null $oLogo */
        $oLogo = $this->GetFromInternalCache('oLogo');
        if (is_null($oLogo)) {
            $oLogo = $this->GetImage(1, 'cms_media_id', $bReturnDefaultImageIfNoneSet);
            if (is_null($oLogo)) {
                $oLogo = $this->GetIcon($bReturnDefaultImageIfNoneSet);
            }
            if (is_null($oLogo)) {
                $oLogo = false;
            }
            $this->SetInternalCache('oLogo', $oLogo);
        }

        return $oLogo;
    }

    /**
     * @return false|void
     */
    protected function PostLoadHook()
    {
        if (!TGlobal::IsCMSMode() && is_array($this->sqlData) && in_array('active', $this->sqlData)) {
            if ('0' === $this->sqlData['active']) {
                $this->sqlData = false;

                return false;
            }
        }

        return parent::PostLoadHook();
    }

    /**
     * @return UrlNormalizationUtil
     */
    private function getUrlNormalizationUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.url_normalization');
    }
}
