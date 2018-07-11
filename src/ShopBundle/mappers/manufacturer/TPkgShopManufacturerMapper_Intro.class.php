<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopManufacturerMapper_Intro extends AbstractPkgShopMapper_Manufacturer
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oManufacturer TdbShopManufacturer */
        $oManufacturer = $oVisitor->GetSourceObject('oObject');
        if ($oManufacturer && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oManufacturer->table, $oManufacturer->id);
        }
        $sMediaLogoId = $oManufacturer->GetImageCMSMediaId(1, 'cms_media_id');
        if (empty($sMediaLogoId) || 1 == $sMediaLogoId) {
            $sMediaLogoId = $oManufacturer->GetImageCMSMediaId(0, 'cms_media_id');
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger('cms_media', $sMediaLogoId);
            }
        }
        $oVisitor->SetMappedValue('sImageId', $sMediaLogoId);
        $oVisitor->SetMappedValue('sHeadline', $oManufacturer->GetName());
        $oVisitor->SetMappedValue('sText', $oManufacturer->GetTextField('description'));
    }
}
