<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class TPkgShopMapper_ShopAddress extends AbstractViewMapper
{
    /**
     * @var PortalDomainServiceInterface
     */
    private $portalDomainService;
    /**
     * @var ShopServiceInterface
     */
    private $shopService;

    /**
     * @param PortalDomainServiceInterface|null $portalDomainService
     * @param ShopServiceInterface|null         $shopService
     */
    public function __construct(PortalDomainServiceInterface $portalDomainService = null, ShopServiceInterface $shopService = null)
    {
        if (null === $portalDomainService) {
            $this->portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
        } else {
            $this->portalDomainService = $portalDomainService;
        }
        if (null === $shopService) {
            $this->shopService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
        } else {
            $this->shopService = $shopService;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oShop', 'TdbShop', $this->shopService->getActiveShop());
        $oRequirements->NeedsSourceObject('oPortal', 'TdbCmsPortal', $this->portalDomainService->getActivePortal());
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oShop TdbShop */
        $oShop = $oVisitor->GetSourceObject('oShop');
        if (null === $oShop) {
            return;
        }

        $aData = $oShop->sqlData;
        foreach ($aData as $sKey => $sVal) {
            if ('adr_' == substr($sKey, 0, 4)) {
                $oVisitor->SetMappedValue($sKey, $sVal);
            }
        }
        $oVisitor->SetMappedValue('shopvatnumber', $oShop->fieldShopvatnumber);
        $oVisitor->SetMappedValue('name', $oShop->fieldName);
        $oCountry = $oShop->GetFieldDataCountry();
        if ($oCountry) {
            $oVisitor->SetMappedValue('adr_country', $oCountry->GetName());
        }

        $oVisitor->SetMappedValue('customer_service_telephone', $oShop->sqlData['customer_service_telephone']);
        $oVisitor->SetMappedValue('customer_service_email', $oShop->sqlData['customer_service_email']);

        /** @var $oPortal TdbCmsPortal */
        $oPortal = $oVisitor->GetSourceObject('oPortal');
        if ($oPortal) {
            $oLogo = $oPortal->GetImage(0, 'images');
            if ($oLogo) {
                $thumb = $oLogo->GetThumbnail($oLogo->aData['width']);
                if (null != $thumb) {
                    $oVisitor->SetMappedValue('sLogoURL', $thumb->GetFullURL());
                }
            }
            $oVisitor->SetMappedValue('sHomeURL', $oPortal->GetPortalHomeURL());
        }
        if ($bCachingEnabled) {
            $oCacheTriggerManager->addTrigger('shop', $oShop->id);
            if ($oCountry) {
                $oCacheTriggerManager->addTrigger('data_country', $oCountry->id);
            }

            if ($oPortal) {
                $oCacheTriggerManager->addTrigger('cms_portal', $oPortal->id);
            }
        }
    }
}
