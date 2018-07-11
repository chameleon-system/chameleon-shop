<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleManufacturer extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        if ($oArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
        }

        $oManufacturer = $oArticle->GetFieldShopManufacturer();

        if (null === $oManufacturer) {
            return;
        }
        if ($oManufacturer && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oManufacturer->table, $oManufacturer->id);
        }

        $sSizeTable = $oManufacturer->GetTextField('sizetable');

        $aManufacturerData = array(
            'sManufacturerLogoId' => '',
            'sManufacturerIconId' => '',
            'sManufacturerName' => $oManufacturer->GetName(),
            'sManufacturerLink' => $oManufacturer->GetLinkProducts(),
            'sManufacturerSizeTable' => $sSizeTable,
        );

        $oLogo = $oManufacturer->GetLogo();
        $oIcon = $oManufacturer->GetIcon();
        if ($oIcon) {
            $aManufacturerData['sManufacturerIconId'] = $oIcon->id;
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger('cms_media', $oIcon->id);
            }
        } elseif ($oLogo) {
            $aManufacturerData['sManufacturerIconId'] = $oLogo->id;
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger('cms_media', $oLogo->id);
            }
        }

        $oVisitor->SetMappedValueFromArray($aManufacturerData);
    }
}
