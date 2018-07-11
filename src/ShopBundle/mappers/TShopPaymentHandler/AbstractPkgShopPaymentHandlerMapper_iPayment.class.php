<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class AbstractPkgShopPaymentHandlerMapper_iPayment extends AbstractPkgShopPaymentHandlerMapper
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oPaymentHandler TShopPaymentHandlerIPaymentCreditCard */
        $oPaymentHandler = $oVisitor->GetSourceObject('oPaymentHandler');
        if ($oPaymentHandler && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oPaymentHandler->table, $oPaymentHandler->id);
        }
        /** @var $sPaymentMethodId string */
        $sPaymentMethodId = $oVisitor->GetSourceObject('sPaymentMethodId');

        $aControlParameter = $oPaymentHandler->getRequestParameters();
        $aControlParameter['plain'] = 'livedata';
        $aControlParameter['spot'] = '[{sModuleSpotName}]';
        $aControlParameter['shop_payment_method_id'] = $sPaymentMethodId;

        $oVisitor->SetMappedValue('sHiddenControlFields', TTools::GetArrayAsFormInput($aControlParameter));
        $oVisitor->SetMappedValue('aUserInput', array());
    }
}
