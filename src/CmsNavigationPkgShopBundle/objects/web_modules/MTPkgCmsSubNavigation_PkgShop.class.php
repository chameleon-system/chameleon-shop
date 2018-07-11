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
 * sub navi using category tree if on an active category.
/**/
class MTPkgCmsSubNavigation_PkgShop extends MTPkgCmsSubNavigation_PkgShopAutoParent
{
    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapeprVisitor instance to the mapper.
     *
     * The mapper has to fill the values it is responsible for in the visitor.
     *
     * example:
     *
     * $foo = $oVisitor->GetSourceObject("foomodel")->GetFoo();
     * $oVisitor->SetMapperValue("foo", $foo);
     *
     *
     * To be able to access the desired source object in the visitor, the mapper has
     * to declare this requirement in its GetRequirements method (see IViewMapper)
     *
     * @param \IMapperVisitorRestricted     $oVisitor
     * @param bool                          $bCachingEnabled      - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     *
     * @return
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $oActiveRootCategory = TdbShop::GetActiveRootCategory();
        if (null === $oActiveRootCategory) {
            parent::Accept($oVisitor, $bCachingEnabled, $oCacheTriggerManager);

            return;
        }
        $oCacheTriggerManager->addTrigger('shop_category');
        $oRootNode = new TPkgShopPrimaryNavigation_TPkgCmsNavigationNode_Category();
        $oRootNode->loadFromNode($oActiveRootCategory);
        $aTree = array($oRootNode);
        $oVisitor->SetMappedValue('aTree', $aTree);
    }

    public function _AllowCache()
    {
        return true;
    }

    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();

        $parameters['activeRootCategoryId'] = $this->getActiveRootCategoryId();
        $parameters['activeCategoryId'] = $this->getActiveCategoryId();

        return $parameters;
    }

    private function getActiveRootCategoryId()
    {
        $rootCategory = $this->getActiveRootCategory();
        if (null === $rootCategory) {
            return null;
        }

        return $rootCategory->id;
    }

    /**
     * @return TdbShopCategory
     */
    private function getActiveRootCategory()
    {
        return TdbShop::GetActiveRootCategory();
    }

    private function getActiveCategoryId()
    {
        $category = $this->getActiveCategory();
        if (null === $category) {
            return null;
        }

        return $category->id;
    }

    /**
     * @return TdbShopCategory
     */
    private function getActiveCategory()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory();
    }
}
