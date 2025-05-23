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
        $oRequirements->NeedsSourceObject('oShop', 'TdbShop', ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop());
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
        $oVisitor->SetMappedValue('sShippingInfoURL', $oShop->GetLinkToSystemPageAsPopUp(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.link.shipping_link'), 'shipping'));
    }
}
