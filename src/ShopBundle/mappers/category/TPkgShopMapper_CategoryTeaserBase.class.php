<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_CategoryTeaserBase extends AbstractPkgShopMapper_Category
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oCategory TdbShopCategory */
        $oCategory = $oVisitor->GetSourceObject('oObject');
        if ($oCategory && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oCategory->table, $oCategory->id);
        }
        $oRootCategory = $oCategory->GetRootCategory();

        $aData = array();
        $sImageId = false;
        if (!is_numeric($oCategory->fieldImage) || $oCategory->fieldImage > 100) {
            $sImageId = $oCategory->fieldImage;
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger('cms_media', $sImageId);
            }
        }
        $aData['sImageId'] = $sImageId;
        $aData['sHeadline'] = $oCategory->GetName();
        $aData['sLink'] = $oCategory->GetLink();
        $aData['sTeaserText'] = $oCategory->GetTextField('description_short');

        if ($oRootCategory && $oRootCategory->id !== $oCategory->id) {
            if ($oRootCategory && $bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oRootCategory->table, $oRootCategory->id);
            }
            $aData['sTopic'] = $oRootCategory->fieldName;
        }

        $oVisitor->SetMappedValueFromArray($aData);
    }
}
