<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_OrderPayment extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopOrder', null, true);
        $oRequirements->NeedsSourceObject('sPaymentHtml', 'string', '', true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oOrder TdbShopOrder */
        $oOrder = $oVisitor->GetSourceObject('oObject');

        if (null !== $oOrder) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oOrder->table, $oOrder->id);
            }
            $oVisitor->SetMappedValue('sPaymentName', $oOrder->fieldShopPaymentMethodName);
            $oVisitor->SetMappedValue('aPaymentInformation', $this->getPaymentInformation($oOrder, $oCacheTriggerManager, $bCachingEnabled));
            $oVisitor->SetMappedValue('sPaymentHtml', $oVisitor->GetSourceObject('sPaymentHtml'));
        }
    }

    /**
     * @param TdbShopOrder $oOrder
     * @param bool|false $bCachingEnabled
     *
     * @return array
     */
    protected function getPaymentInformation(TdbShopOrder $oOrder, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aPaymentInformation = array();

        $oPaymentMethodParameterList = &$oOrder->GetFieldShopOrderPaymentMethodParameterList();
        $oPaymentMethodParameterList->GoToStart();
        while ($oPaymentParameter = &$oPaymentMethodParameterList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oPaymentParameter->table, $oPaymentParameter->id);
            }
            $aPaymentInformation[] = array(
                'sName' => $oPaymentParameter->fieldName,
                'sValue' => $oPaymentParameter->fieldValue,
            );
        }

        return $aPaymentInformation;
    }
}
