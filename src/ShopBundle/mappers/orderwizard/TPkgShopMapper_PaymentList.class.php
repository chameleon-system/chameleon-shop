<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_PaymentList extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oPaymentMethodList', 'TdbShopPaymentMethodList', null, true);
        $oRequirements->NeedsSourceObject('oActivePaymentMethod', 'TdbShopPaymentMethod', null, true);
        $oRequirements->NeedsSourceObject('oLocal', 'TCMSLocal', TCMSLocal::GetActive());
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oPaymentMethodList TdbShopPaymentMethodList */
        $oPaymentMethodList = $oVisitor->GetSourceObject('oPaymentMethodList');
        /** @var $oActivePaymentMethod TdbShopPaymentMethod */
        $oActivePaymentMethod = $oVisitor->GetSourceObject('oActivePaymentMethod');
        if ($oActivePaymentMethod && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oActivePaymentMethod->table, $oActivePaymentMethod->id);
        }
        /** @var $oLocal TCMSLocal */
        $oLocal = $oVisitor->GetSourceObject('oLocal');

        $aPaymentList = array();
        if (null !== $oPaymentMethodList) {
            $hasActivePaymentMethod = false;
            $oPaymentMethodList->GoToStart();
            while ($oPaymentMethod = $oPaymentMethodList->Next()) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oPaymentMethod->table, $oPaymentMethod->id);
                }
                $bIsActive = ($oActivePaymentMethod && $oPaymentMethod->id == $oActivePaymentMethod->id);
                if ($bIsActive) {
                    $hasActivePaymentMethod = true;
                    $oPaymentMethod = $oActivePaymentMethod;
                }
                $oPaymentHandler = $oPaymentMethod->GetFieldShopPaymentHandler();
                if ($oPaymentHandler && $bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oPaymentHandler->table, $oPaymentHandler->id);
                }
                $dPrice = $oPaymentMethod->GetPrice();
                $aPayment = array(
                    'bIsActive' => $bIsActive,
                    'sCost' => (0 != $dPrice) ? ($oLocal->FormatNumber($dPrice, 2)) : (''),
                    'sTitle' => $oPaymentMethod->fieldName,
                    'id' => $oPaymentMethod->id,
                    'cmsMediaId' => $oPaymentMethod->fieldCmsMediaId,
                    'sDescription' => $oPaymentMethod->GetTextField('description'),
                    'sDetails' => trim($oPaymentHandler->Render('standard', 'Customer', array('iPaymentMethodId' => $oPaymentMethod->id, 'bInputIsActive' => $bIsActive))),
                    'sTargetURL' => $oPaymentHandler->GetUserInputTargetURL(),
                );
                $aPaymentList[] = $aPayment;
            }
            if (false === $hasActivePaymentMethod && count($aPaymentList) > 0) {
                $aPaymentList[0]['bIsActive'] = true;
            }
        }
        $oVisitor->SetMappedValue('aPaymentList', $aPaymentList);
    }
}
