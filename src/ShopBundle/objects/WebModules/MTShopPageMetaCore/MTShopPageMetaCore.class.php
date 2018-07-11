<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class MTShopPageMetaCore extends MTPageMetaCore
{
    public function _GetTitle()
    {
        // alter the path if we have an active category or an active item
        $sTitle = '';

        //default SEO pattern of the page
        $sTitle = $this->GetSeoPatternString();

        if (strlen($sTitle) < 1) {
            $oShop = TdbShop::GetInstance();
            $oActiveCategory = $oShop->GetActiveCategory();
            $oActiveItem = $oShop->GetActiveItem();

            if (is_null($oActiveCategory) && is_null($oActiveItem)) {
                $sTitle = parent::_GetTitle();
            } else {
                $sTitle = $this->_GetPortalName();

                $aList = array();

                if (!is_null($oActiveItem)) {
                    $oItem = new TShopBreadcrumbItemArticle();
                    /** @var $oItem TShopBreadcrumbItemArticle */
                    $oItem->oItem = $oActiveItem;
                    $aList[] = $oItem;
                }

                if (!is_null($oActiveCategory)) {
                    $oItem = new TShopBreadcrumbItemCategory();
                    /** @var $oItem TShopBreadcrumbItemCategory */
                    $oItem->oItem = $oActiveCategory;
                    $aList[] = $oItem;
                    $oCurrentCategory = &$oActiveCategory;

                    while ($oParent = &$oCurrentCategory->GetParent()) {
                        $oItem = new TShopBreadcrumbItemCategory();
                        /** @var $oItem TShopBreadcrumbItemCategory */
                        $oItem->oItem = $oParent;
                        $aList[] = $oItem;
                        $oCurrentCategory = &$oParent;
                    }
                }
                $aList = array_reverse($aList);

                foreach (array_keys($aList) as $index) {
                    $sTitle .= ' - '.$aList[$index]->GetName();
                }
            }
        }

        return $sTitle;
    }

    protected function _GetMetaData()
    {
        $aMetaData = parent::_GetMetaData();

        $oShop = TdbShop::GetInstance();
        $oActiveCategory = $oShop->GetActiveCategory();
        $oActiveItem = $oShop->GetActiveItem();

        if (!is_null($oActiveCategory) || !is_null($oActiveItem)) {
            // add keywords from category
            $sDescription = '';
            if (!is_null($oActiveItem)) {
                $sDescription .= $oActiveItem->GetMetaDescription();
            } elseif (!is_null($oActiveCategory)) {
                $sDescription .= $oActiveCategory->GetMetaDescription();
            }
            $sDescription = trim($sDescription);

            if (!is_null($oActiveCategory)) {
                $aKeywords = $oActiveCategory->GetMetaKeywords();
            }
            if (!is_null($oActiveItem)) {
                $aTmpKeywords = $oActiveItem->GetMetaKeywords();
                //$aKeywords = array_merge($aKeywords,$aTmpKeywords);
                $aKeywords = $aTmpKeywords;
            }
            // remove doubles
            $aClean = array();
            foreach ($aKeywords as $sWord) {
                if (!in_array($sWord, $aClean) && strlen($sWord) > 3) {
                    $aClean[] = $sWord;
                }
            }

            if (!empty($sDescription)) {
                $aMetaData['name']['description'] = $sDescription;
            }
            if (count($aClean) > 0) {
                $aMetaData['name']['keywords'] = implode(', ', $aClean);
            }
        }

        return $aMetaData;
    }

    /**
     * return an assoc array of parameters that describe the state of the module.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $aParameters = parent::_GetCacheParameters();

        $oShop = TdbShop::GetInstance();
        $oActiveCategory = $oShop->GetActiveCategory();
        $oActiveItem = $oShop->GetActiveItem();

        if (!is_null($oActiveCategory)) {
            $aParameters['activecategoryid'] = $oActiveCategory->id;
        }
        if (!is_null($oActiveItem)) {
            $aParameters['activeitemid'] = $oActiveItem->id;
        }

        return $aParameters;
    }

    /**
     * if the content that is to be cached comes from the database (as ist most often the case)
     * then this function should return an array of assoc arrays that point to the
     * tables and records that are associated with the content. one table entry has
     * two fields:
     *   - table - the name of the table
     *   - id    - the record in question. if this is empty, then any record change in that
     *             table will result in a cache clear.
     *
     * @return array
     */
    public function _GetCacheTableInfos()
    {
        $tableInfo = parent::_GetCacheTableInfos();

        $iarticle = '';
        $oShop = TdbShop::GetInstance();
        $oActiveItem = $oShop->GetActiveItem();
        if (!is_null($oActiveItem)) {
            $iarticle = $oActiveItem->id;
        }

        $tableInfo[] = array('table' => 'shop_article', 'id' => $iarticle);
        $tableInfo[] = array('table' => 'shop_category', 'id' => '');

        return $tableInfo;
    }

    /**
     * Get SEO pattern string.
     *
     * @param bool $dont_replace Do not replace pattern with values
     *
     * @return string
     */
    protected function GetSeoPatternString($dont_replace = false)
    {
        //return ''; //why? //#7767
        $sPattern = ''; //"[{PORTAL_NAME}] - [{PAGE_NAME}]";
        /** @var $oSeoRenderer TCMSRenderSeoPattern */
        $oSeoRenderer = new TCMSRenderSeoPattern();

        $sTmpPattern = parent::GetSeoPatternString(true);
        if (strlen(trim($sTmpPattern)) > 0) {
            $sPattern = $sTmpPattern;
        }

        $oActiveArticle = TdbShop::GetActiveItem();
        if ($oActiveArticle) {
            $arrRepl = $oActiveArticle->GetSeoPattern($sPattern);
            $oSeoRenderer->AddPatternReplaceValues($arrRepl);

            return $oSeoRenderer->RenderPattern($sPattern);
        } else {
            $oActiveCategory = TdbShop::GetActiveCategory();
            if ($oActiveCategory) {
                $arrRepl = $oActiveCategory->GetSeoPattern($sPattern);
                $oSeoRenderer->AddPatternReplaceValues($arrRepl);

                return $oSeoRenderer->RenderPattern($sPattern);
            }
        }

        return parent::GetSeoPatternString();
    }

    /**
     * return the canonical URL for the page.
     *
     * @return string
     */
    protected function GetMetaCanonical()
    {
        $sCanonical = '';
        $shopService = $this->getShopService();
        $oShopItem = $shopService->getActiveProduct();
        /** @var $oShopItem TdbShopArticle */
        if ($oShopItem) {
            $oCat = $oShopItem->GetPrimaryCategory();
            $iCat = '';
            if ($oCat) {
                $iCat = $oCat->id;
            }
            $sCanonical = $oShopItem->GetDetailLink(true, $iCat);
        } else {
            $category = $shopService->getActiveCategory();
            if ($category) {
                $currentPage = $this->getInputFilterUtil()->getFilteredInput(TdbShopArticleList::URL_LIST_CURRENT_PAGE, 0);
                $additionalParam = array();
                if (0 !== $currentPage) {
                    $additionalParam = array(TdbShopArticleList::URL_LIST_CURRENT_PAGE => $currentPage);
                }
                $activePage = $this->getActivePageService()->getActivePage();

                return $activePage->GetRealURLPlain($additionalParam, true);
            }
            $sCanonical = parent::GetMetaCanonical();
        }

        return $sCanonical;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return InputFilterUtilInterface
     */
    private function getInputFilterUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.input_filter');
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
