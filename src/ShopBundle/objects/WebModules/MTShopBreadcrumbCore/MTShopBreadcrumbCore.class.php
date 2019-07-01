<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class MTShopBreadcrumbCore extends MTBreadcrumbCore
{
    protected $bAllowHTMLDivWrapping = true;

    public function &Execute()
    {
        parent::Execute();

        $oShop = TdbShop::GetInstance();
        $oActiveCategory = $oShop->GetActiveCategory();
        $oActiveItem = $oShop->GetActiveItem();

        // if we have an active category... generate path there
        if (!is_null($oActiveCategory) || !is_null($oActiveItem)) {
            // we want to use the normal breadcrum view... so we need to generate a breadcrumb class that can simulate the required methos (getlink, gettarget and getname)
            $breadcrumb = new TCMSPageBreadcrumb();
            /** @var $breadcrumb TCMSPageBreadcrumb */
            $aList = array();

            if (!is_null($oActiveCategory)) {
                $aCatPath = TdbShop::GetActiveCategoryPath();
                foreach (array_keys($aCatPath) as $iCatId) {
                    $oItem = new TShopBreadcrumbItemCategory();
                    /** @var $oItem TShopBreadcrumbItemCategory */
                    $oItem->oItem = $aCatPath[$iCatId];
                    $aList[] = $oItem;
                }
            }
            if (!is_null($oActiveItem)) {
                $oItem = new TShopBreadcrumbItemArticle();
                /** @var $oItem TShopBreadcrumbItemArticle */
                $oItem->oItem = $oActiveItem;
                $aList[] = &$oItem;
            }

            foreach (array_keys($aList) as $index) {
                $breadcrumb->AddItem($aList[$index]);
            }

            $this->data['oBreadcrumb'] = &$breadcrumb;

            return $this->data;
        }

        $activeManufacturer = $this->getShopService()->getActiveManufacturer();
        if (null !== $activeManufacturer) {
            $breadcrumb = new TCMSPageBreadcrumb();

            $breadcrumbItem = new TShopBreadcrumbItemManufacturer($activeManufacturer);
            $breadcrumb->AddItem($breadcrumbItem);

            $this->data['oBreadcrumb'] = $breadcrumb;

            return $this->data;
        }

        return $this->data;
    }

    /**
     * return an assoc array of parameters that describe the state of the module.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $aParameters = parent::_GetCacheParameters();

        // TODO doesn't _GetCacheTableInfos() handle this completely?

        $oShop = TdbShop::GetInstance();
        $oActiveCategory = $oShop->GetActiveCategory();
        $oActiveItem = $oShop->GetActiveItem();
        if (!is_null($oActiveCategory)) {
            $aParameters['iactivecategoryid'] = $oActiveCategory->id;
        }
        if (!is_null($oActiveItem)) {
            $aParameters['iactiveitemid'] = $oActiveItem->id;
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
        $aTables = parent::_GetCacheTableInfos();

        $oShop = TdbShop::GetInstance();
        $oActiveCategory = $oShop->GetActiveCategory();
        $oActiveItem = $oShop->GetActiveItem();
        if (!is_null($oActiveCategory)) {
            $aTables[] = array('table' => 'shop_category', 'id' => '');
        }

        if (!is_null($oActiveItem)) {
            $aTables[] = array('table' => 'shop_article', 'id' => $oActiveItem->id);
        }

        $activeManufacturer = $this->getShopService()->getActiveManufacturer();
        if (null !== $activeManufacturer) {
            $aTables[] = array('table' => 'shop_manufacturer', 'id' => $activeManufacturer->id);
        }

        return $aTables;
    }

    private function getShopService(): ShopServiceInterface
    {
        return ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
