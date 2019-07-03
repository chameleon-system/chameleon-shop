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
            $existingBreadcrumb = $this->data['oBreadcrumb'] ?? new TCMSPageBreadcrumb();
            $this->data['oBreadcrumb'] = $this->replaceLastBreadcrumbItem($existingBreadcrumb, $activeManufacturer);

            return $this->data;
        }

        return $this->data;
    }

    private function replaceLastBreadcrumbItem(TCMSPageBreadcrumb $existingBreadcrumb, TdbShopManufacturer $activeManufacturer): TCMSPageBreadcrumb
    {
        $replacedBreadcrumb = new TCMSPageBreadcrumb();

        while (false !== ($item = $existingBreadcrumb->next())) {
            if (true === $existingBreadcrumb->IsLast()) {
                break; // omit last one
            }

            $replacedBreadcrumb->AddItem($item);
        }

        $breadcrumbItem = new TShopBreadcrumbItemManufacturer($activeManufacturer);
        $replacedBreadcrumb->AddItem($breadcrumbItem);

        return $replacedBreadcrumb;
    }

    /**
     * {@inheritDoc}
     */
    public function _GetCacheParameters()
    {
        $aParameters = parent::_GetCacheParameters();

        $oShop = TdbShop::GetInstance();
        $oActiveCategory = $oShop->GetActiveCategory();
        $oActiveItem = $oShop->GetActiveItem();
        if (!is_null($oActiveCategory)) {
            $aParameters['iactivecategoryid'] = $oActiveCategory->id;
        }
        if (!is_null($oActiveItem)) {
            $aParameters['iactiveitemid'] = $oActiveItem->id;
        }

        $activeManufacturer = $this->getShopService()->getActiveManufacturer();
        if (null !== $activeManufacturer) {
            $aParameters['activemanufacturer'] = $activeManufacturer->id;
        }

        return $aParameters;
    }

    /**
     * {@inheritDoc}
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
