<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentHandlerMapper_PayPalViaLinkOrderCompleted extends AbstractPkgShopPaymentHandlerMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('oOrder', 'TdbShopOrder');
        $oRequirements->NeedsSourceObject('oTextBlock');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oOrder TdbShopOrder */
        $oOrder = $oVisitor->GetSourceObject('oOrder');
        /** @var $oPaymentHandler TdbShopPaymentHandler */
        $oPaymentHandler = $oVisitor->GetSourceObject('oPaymentHandler');
        /** @var $oTextBlock TdbPkgCmsTextBlock */
        $oTextBlock = $oVisitor->GetSourceObject('oTextBlock');
        if ($oTextBlock && $oTextBlock instanceof TdbPkgCmsTextBlock) {
            $sPaymentText = $oTextBlock->GetTextField('content');
            $oVisitor->SetMappedValue('sPaymentText', $sPaymentText);
        }
        $oVisitor->SetMappedValue('sPaymentLink', $oPaymentHandler->GetPayPalPaymentLink($oOrder));
    }
}
