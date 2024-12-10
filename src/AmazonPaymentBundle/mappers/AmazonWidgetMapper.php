<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\mappers;

use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentConfigFactory;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use IMapperCacheTriggerRestricted;
use IMapperRequirementsRestricted;
use IMapperVisitorRestricted;
use TShopBasket;

class AmazonWidgetMapper extends \AbstractViewMapper
{
    /**
     * A mapper has to specify its requirements by providing th passed MapperRequirements instance with the
     * needed information and returning it.
     *
     * example:
     *
     * $oRequirements->NeedsSourceObject("foo",'stdClass','default-value');
     * $oRequirements->NeedsSourceObject("bar");
     * $oRequirements->NeedsMappedValue("baz");
     *
     * @param IMapperRequirementsRestricted $oRequirements
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('basket', 'TShopBasket', TShopBasket::GetInstance());
        $oRequirements->NeedsSourceObject(
            'config',
            '\\ChameleonSystem\\AmazonPaymentBundle\\AmazonPaymentGroupConfig',
            AmazonPaymentConfigFactory::createConfig($this->getActivePortalId())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        /** @var TShopBasket $basket */
        $basket = $oVisitor->GetSourceObject('basket');
        /** @var AmazonPaymentGroupConfig $config */
        $config = $oVisitor->GetSourceObject('config');

        $errorURL = array('module_fnc' => array('amazonActionPlugin' => 'widgetError'));
        $data = array(
            'sellerId' => $config->getMerchantId(),
            'amazonOrderReferenceId' => $basket->getAmazonOrderReferenceId(),
            'errorURL' => \TTools::GetArrayAsURL($errorURL, '?'),
        );

        $oVisitor->SetMappedValueFromArray($data);
    }

    private function getActivePortalId()
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal()->id;
    }
}
