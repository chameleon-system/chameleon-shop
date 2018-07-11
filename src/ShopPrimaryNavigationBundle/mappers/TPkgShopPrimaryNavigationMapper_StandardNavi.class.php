<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPrimaryNavigationMapper_StandardNavi extends AbstractViewMapper
{
    /**
     * A mapper has to specify its requirements by providing th passed MapperRequirements instance with the
     * needed information and returning it.
     *
     * example:
     *
     * $oRequirements->NeedsSourceObject("foo",'stdClass','default-value');
     * $oRequirements->NeedsSourceObject("bar");
     * $oRequirements->NeedsMappedValue("baz");
     *
     * @param IMapperRequirementsRestricted $oRequirements
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oPortal', 'TdbCmsPortal');
    }

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
        /** @var $oPortal TdbCmsPortal */
        $oPortal = $oVisitor->GetSourceObject('oPortal');

        $oNodeList = TdbPkgShopPrimaryNaviList::GetListForCmsPortalId($oPortal->id);

        $aTree = array();
        while ($oNode = $oNodeList->Next()) {
            $aTree[] = $oNode->getPkgCmsNavigationNodeObject();
        }
        $oVisitor->SetMappedValue('aTree', $aTree);
        if ($bCachingEnabled) {
            $oCacheTriggerManager->addTrigger('cms_tpl_page');
            $oCacheTriggerManager->addTrigger('cms_tree');
            $oCacheTriggerManager->addTrigger('shop_category');
            $oCacheTriggerManager->addTrigger('pkg_shop_primary_navi');
        }
    }
}
