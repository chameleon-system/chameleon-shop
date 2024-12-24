<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;

class TPkgShopCurrencyMapper extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $currency = ServiceLocator::get('chameleon_system_shop_currency.shop_currency')->getObject();
        $oRequirements->NeedsSourceObject('oCurrency', 'TdbPkgShopCurrency', $currency, true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oCurrency TdbPkgShopCurrency */
        $oCurrency = $oVisitor->GetSourceObject('oCurrency');
        if ($oCurrency && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oCurrency->table, $oCurrency->id);
        }
        $oVisitor->SetMappedValue('sCurrencyName', $oCurrency->fieldName);
        $oVisitor->SetMappedValue('sCurrency', $oCurrency->fieldSymbol);
    }
}
