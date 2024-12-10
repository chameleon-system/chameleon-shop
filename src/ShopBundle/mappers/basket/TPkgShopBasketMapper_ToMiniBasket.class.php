<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopBasketMapper_ToMiniBasket extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oBasket', 'TShopBasket', TShopBasket::GetInstance());
        $oRequirements->NeedsSourceObject('oShop', 'TdbShop', TdbShop::GetInstance());
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oShop TdbShop */
        $oShop = $oVisitor->GetSourceObject('oShop');
        if ($oShop && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oShop->table, $oShop->id);
        }
        $oVisitor->SetMappedValue('sBasketURL', $oShop->GetBasketLink(false, true));
        $oVisitor->SetMappedValue('sCheckoutURL', $oShop->GetBasketLink(true, true));
        $oVisitor->SetMappedValue('sShippingInfoURL', $oShop->GetLinkToSystemPageAsPopUp(TGlobal::Translate('chameleon_system_shop.link.shipping_link'), 'shipping'));
    }
}
