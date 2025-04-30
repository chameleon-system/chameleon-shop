<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MTMyAccountMapper_Newsletter extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oNewsletterUser', 'TdbPkgNewsletterUser', null, true);
        $oRequirements->NeedsSourceObject('oMyAccountModuleConfig', 'TdbDataExtranetModuleMyAccount');
        $oRequirements->NeedsSourceObject('sActionLink', '');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oNewsletterUser TdbPkgNewsletterUser */
        $oNewsletterUser = $oVisitor->GetSourceObject('oNewsletterUser');
        $bNewsletterSubscribed = true;
        if (is_null($oNewsletterUser) || !$oNewsletterUser->fieldOptin) {
            $bNewsletterSubscribed = false;
        }
        if ($oNewsletterUser && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oNewsletterUser->table, $oNewsletterUser->id);
        }
        /** @var $oMyAccountModuleConfig TdbDataExtranetModuleMyAccount */
        $oMyAccountModuleConfig = $oVisitor->GetSourceObject('oMyAccountModuleConfig');
        if ($oMyAccountModuleConfig && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oMyAccountModuleConfig->table, $oMyAccountModuleConfig->id);
        }
        $aTextData = [];
        $aTextData['sTitle'] = $oMyAccountModuleConfig->fieldHeadline;
        $aTextData['sText'] = $oMyAccountModuleConfig->GetTextField('intro');
        $oVisitor->SetMappedValue('sActionLink', $oVisitor->GetSourceObject('sActionLink'));
        $oVisitor->SetMappedValue('bNewsletterSubscribed', $bNewsletterSubscribed);
        $oVisitor->SetMappedValue('aTextData', $aTextData);
    }
}
