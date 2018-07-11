<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleMarker extends AbstractPkgShopMapper_Article
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
        $oArticleMarkerList = $oArticle->GetFieldShopArticleMarkerList();
        $aArticleMarker = array(); //with (sImageUrl, sName, sDescription)
        while ($oArticleMarker = $oArticleMarkerList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oArticleMarker->table, $oArticleMarker->id);
            }
            if (!empty($oArticleMarker->fieldCmsMediaId) && $bCachingEnabled) {
                $oCacheTriggerManager->addTrigger('cms_media', $oArticleMarker->fieldCmsMediaId);
            }
            $aMarker = array(
                'sImageId' => $oArticleMarker->fieldCmsMediaId,
                'sName' => $oArticleMarker->fieldTitle,
                'sDescription' => $oArticleMarker->GetTextField('description'),
            );
            $aArticleMarker[] = $aMarker;
        }
        $oVisitor->SetMappedValue('aArticleMarker', $aArticleMarker);
    }
}
