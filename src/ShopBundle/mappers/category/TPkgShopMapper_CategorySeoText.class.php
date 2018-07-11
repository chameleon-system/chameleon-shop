<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_CategorySeoText extends AbstractPkgShopMapper_Category
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
        $oVisitor->SetMappedValue('sText', $oCategory->GetTextField('seo_text'));
    }
}
