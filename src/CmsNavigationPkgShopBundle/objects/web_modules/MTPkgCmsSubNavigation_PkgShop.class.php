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

/**
 * sub navi using category tree if on an active category.
 */
class MTPkgCmsSubNavigation_PkgShop extends MTPkgCmsSubNavigation_PkgShopAutoParent
{
    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapperVisitor instance to the mapper.
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
     * @param bool $bCachingEnabled - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $activeRootCategory = ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveRootCategory();
        if (null === $activeRootCategory) {
            parent::Accept($oVisitor, $bCachingEnabled, $oCacheTriggerManager);

            return;
        }
        $oCacheTriggerManager->addTrigger('shop_category');
        $oRootNode = new TPkgShopPrimaryNavigation_TPkgCmsNavigationNode_Category();
        $oRootNode->loadFromNode($activeRootCategory);
        $aTree = [$oRootNode];
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

    private function getActiveRootCategoryId(): ?string
    {
        return ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveRootCategory()?->id;
    }

    private function getActiveCategoryId(): ?string
    {
        return $this->getActiveCategory()?->id;
    }

    private function getActiveCategory(): ?TdbShopCategory
    {
        return ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory();
    }
}
