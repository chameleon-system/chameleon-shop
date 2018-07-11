<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_CategorySubCategories extends AbstractPkgShopMapper_Category
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

        $oVisitor->SetMappedValue('sCategoryName', $oCategory->GetName());
        $aSubCategories = array();
        $oSubCategories = $oCategory->GetChildren();
        // todo - we should generate the data here using a config like ViewRenderer::generateSourceObjectForObjectList -> but without having the view be fixed in this mapper
        while ($oSubCategory = $oSubCategories->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oSubCategory->table, $oSubCategory->id);
            }
            $aData = array(
                'sImageId' => $oSubCategory->fieldImage,
                'sHeadline' => $oSubCategory->GetName(),
                'sLink' => $oSubCategory->GetLink(),
                'sTeaserText' => $oSubCategory->GetTextField('description_short'),
            );
            $aSubCategories[] = $aData;
        }
        $oVisitor->SetMappedValue('aTeaserList', $aSubCategories);
    }
}
