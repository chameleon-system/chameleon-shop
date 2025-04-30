<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class MTFooterCategory extends MTPkgViewRendererAbstractModuleMapper
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
        $oCacheTriggerManager->addTrigger('pkg_shop_footer_category');
        $oFooterList = TdbPkgShopFooterCategoryList::GetListForShopId($this->getShopService()->getId());
        $aTree = [];
        while ($oFooter = $oFooterList->Next()) {
            $aTree[] = $this->getSubNavi($oFooter);
        }

        $oVisitor->SetMappedValue('aTree', $aTree);
    }

    /**
     * @return array
     */
    private function getSubNavi(TdbPkgShopFooterCategory $oFooter)
    {
        $aTree = [
            'bIsActive' => '',
            'bIsExpanded' => '',
            'sLink' => '',
            'sTitle' => $oFooter->fieldName,
            'sSeoTitle' => '',
            'aChildren' => [],
        ];
        $oCat = $oFooter->GetFieldShopCategory();
        if (null !== $oCat) {
            $aTree['sLink'] = $oCat->GetLink();
            $oCatList = $oCat->GetFieldShopCategoryList();
            while ($oSubCat = $oCatList->Next()) {
                $aTree['aChildren'][] = [
                    'bIsActive' => '',
                    'bIsExpanded' => '',
                    'sLinkURL' => $oSubCat->GetLink(),
                    'sTitle' => $oSubCat->fieldName,
                    'sSeoTitle' => '',
                    'aChildren' => [],
                ];
            }
        }

        return $aTree;
    }

    /**
     * {@inheritdoc}
     */
    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters['shop'] = $this->getShopService()->getId();

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function _AllowCache()
    {
        return true;
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
