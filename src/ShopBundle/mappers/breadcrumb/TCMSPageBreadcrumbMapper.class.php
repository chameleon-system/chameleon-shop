<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSPageBreadcrumbMapper extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oBreadCrumb', 'TCMSPageBreadcrumb');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oBreadCrumb TCMSPageBreadcrumb */
        $oBreadCrumb = $oVisitor->GetSourceObject('oBreadCrumb');
        $aTree = array();
        $oBreadCrumb->GoToStart();
        while ($oNode = $oBreadCrumb->Next()) { /*@var $oNode TCMSTreeNode */
            $aTree[] = array('bIsActive' => false,
                             'bIsExpanded' => true,
                             'sLink' => $oNode->GetLink(),
                             'sTitle' => $oNode->GetName(), );
        }
        $oVisitor->SetMappedValue('aTree', $aTree);
    }
}
