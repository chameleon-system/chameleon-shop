<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;

class TPkgShopMapper_ArticleListOrderBy extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oOrderByList', 'TdbShopModuleArticlelistOrderbyList');
        $oRequirements->NeedsSourceObject('sActiveOrderById', 'string', null);
        $oRequirements->NeedsSourceObject('sOrderByTitle', 'string');
        $oRequirements->NeedsSourceObject('sFieldName', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oOrderByList TdbShopModuleArticlelistOrderbyList */
        $oOrderByList = $oVisitor->GetSourceObject('oOrderByList');

        if (!is_object($oOrderByList) || 0 == $oOrderByList->Length()) {
            return;
        }
        $sActiveOrderById = $oVisitor->GetSourceObject('sActiveOrderById');
        $sOrderByTitle = $oVisitor->GetSourceObject('sOrderByTitle');
        $sFieldName = $oVisitor->GetSourceObject('sFieldName');

        $aExcludeParams = TCMSSmartURLData::GetActive()->getSeoURLParameters();
        $aExcludeParams[] = 'sortid';
        $aExcludeParams[] = 'module_fnc';
        $aExcludeParams[] = 'listrequest';
        $aExcludeParams[] = 'listkey';
        $aExcludeParams[] = 'listpage';
        $aOrderByParameter = TGlobal::instance()->GetUserData(null, $aExcludeParams);
        $aOrderByParameter['module_fnc'] = ['[{sModuleSpotName}]' => 'ChangeOrder'];

        $aData = [
            'sName' => $sOrderByTitle,
            'sFormActionUrl' => '?',
            'sFormId' => '',
            'sSelectName' => $sFieldName,
            'sFormHiddenFields' => TTools::GetArrayAsFormInput($aOrderByParameter),
            'aOptionList' => [
            ],
        ];
        $oActivePage = $this->getActivePageService()->getActivePage();
        if (null !== $oActivePage) {
            $aData['sFormActionUrl'] = $oActivePage->GetRealURLPlain();
        }

        $aOptionList = [];
        while ($oOption = $oOrderByList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oOption->table, $oOption->id);
            }
            $aOptionList[] = [
                'sValue' => $oOption->id,
                'sName' => $oOption->fieldNamePublic,
                'bSelected' => ($oOption->id == $sActiveOrderById),
            ];
        }
        $aData['aOptionList'] = $aOptionList;

        $oVisitor->SetMappedValueFromArray($aData);
    }

    private function getActivePageService(): ActivePageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
