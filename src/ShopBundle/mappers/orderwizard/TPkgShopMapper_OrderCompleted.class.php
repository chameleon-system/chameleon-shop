<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_OrderCompleted extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oThankYouOrderStep', 'TdbShopOrderStep');
        $oRequirements->NeedsSourceObject('sPaymentHtml');
        $oRequirements->NeedsSourceObject('oOrder', 'TdbShopOrder');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oActiveOrderStep TdbShopOrderStep */
        $oActiveOrderStep = $oVisitor->GetSourceObject('oThankYouOrderStep');
        if ($oActiveOrderStep && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oActiveOrderStep->table, $oActiveOrderStep->id);
        }
        $aTextData = array();
        $aTextData['sTitle'] = $oActiveOrderStep->fieldName;
        $aTextData['sText'] = '';

        $aOrderPrintData = array();
        $aOrderPrintData['sText'] = $oActiveOrderStep->GetDescription();
        $oLastOrder = $oVisitor->GetSourceObject('oOrder');
        if (!$oLastOrder->fieldSystemOrderNotificationSend) {
            $aOrderPrintData['sText'] .= \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.error.unable_to_send_order_confirm_mail');
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oLastOrder->table, $oLastOrder->id);
            }
        }
        $oVisitor->SetMappedValue('aStepTextData', $aTextData);
        $oVisitor->SetMappedValue('aOrderPrintData', $aOrderPrintData);
        $oVisitor->SetMappedValue('sPaymentHtml', $oVisitor->GetSourceObject('sPaymentHtml'));
    }
}
