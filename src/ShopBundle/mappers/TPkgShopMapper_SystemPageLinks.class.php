<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class TPkgShopMapper_SystemPageLinks extends AbstractViewMapper
{
    private ShopServiceInterface $shopService;

    public function __construct(?ShopServiceInterface $shopService = null)
    {
        if (null === $shopService) {
            $this->shopService = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
        } else {
            $this->shopService = $shopService;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oShop', 'TdbShop', $this->shopService->getActiveShop());
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
        $aSystemPages = $oShop->GetSystemPageNames();
        $aSystemPageLinks = [];
        foreach ($aSystemPages as $sSystemPage) {
            $aSystemPageLinks[$sSystemPage] = $oShop->GetLinkToSystemPage($sSystemPage);
        }
        $oVisitor->SetMappedValue('aShopSystemPageURL', $aSystemPageLinks);
    }
}
