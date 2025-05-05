<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleListResultInfo extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oList', 'TdbShopArticleList');
        $oRequirements->NeedsSourceObject('oLocal', 'TCMSLocal', TCMSLocal::GetActive());
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oArticleList TdbShopArticleList */
        $oArticleList = $oVisitor->GetSourceObject('oList');

        /** @var $oLocal TCMSLocal */
        $oLocal = $oVisitor->GetSourceObject('oLocal');

        $iStartItem = $oArticleList->GetStartRecordNumber() + 1;
        $iMaxItems = $oArticleList->Length();
        $iEndItem = $oArticleList->GetStartRecordNumber() + $oArticleList->GetPageSize();
        if ($iEndItem > $iMaxItems) {
            $iEndItem = $iMaxItems;
        }

        $aListPaging = [
            'sStartItem' => $oLocal->FormatNumber($iStartItem, 0),
            'sEndItem' => $oLocal->FormatNumber($iEndItem, 0),
            'sMaxItems' => $oLocal->FormatNumber($iMaxItems, 0),
        ];
        $oVisitor->SetMappedValueFromArray($aListPaging);
    }
}
