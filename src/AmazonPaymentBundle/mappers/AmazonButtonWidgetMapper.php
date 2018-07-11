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
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use IMapperCacheTriggerRestricted;
use IMapperRequirementsRestricted;
use IMapperVisitorRestricted;

class AmazonButtonWidgetMapper extends \AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('amazonActionPluginSpotName', 'string', 'amazonActionPlugin');
        $oRequirements->NeedsSourceObject('amazonPaymentMethodInternalName', 'string', 'amazon');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ) {
        if (false === \TdbShopShippingGroupList::GetShippingGroupsThatAllowPaymentWith($oVisitor->GetSourceObject('amazonPaymentMethodInternalName'))) {
            $oVisitor->SetMappedValue('amazonPayment', array('amazonPaymentEnabled' => false));

            return;
        }

        $successURLData = array(
            'module_fnc' => array(
                $oVisitor->GetSourceObject(
                    'amazonActionPluginSpotName'
                ) => 'setAmazonOrderReferenceId',
            ),
        );
        $errorURLData = array(
            'module_fnc' => array($oVisitor->GetSourceObject('amazonActionPluginSpotName') => 'errorAmazonLogin'),
        );
        $data = array(
            'amazonPaymentEnabled' => true,
            'payWithAmazonButtonURL' => '',
            'sellerId' => '',
            'payWithAmazonURL' => \TTools::GetArrayAsURL($successURLData, '?'),
            'payWithAmazonURLError' => \TTools::GetArrayAsURL($errorURLData, '?'),
        );
        try {
            $config = AmazonPaymentConfigFactory::createConfig($this->getActivePortalId());
            $data['payWithAmazonButtonURL'] = $config->getPayWithAmazonButton();
            $data['sellerId'] = $config->getMerchantId();
            $data['sText'] = $config->getPayWithAmazonButtonText();
        } catch (\InvalidArgumentException $e) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('cmsPkgCore.logChannel.standard')->error(
                'unable to load amazon config: '.(string) $e,
                __FILE__,
                __LINE__,
                array('e.message' => $e->getMessage(), 'e.file' => $e->getFile(), 'e.line' => $e->getLine())
            );
            $data['payWithAmazonButtonURL'] = '<!-- ERROR: unable to load amazon payment config due to config error (invalid parameter). check log for details -->';
        }

        $oVisitor->SetMappedValue('amazonPayment', $data);
    }

    private function getActivePortalId()
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal()->id;
    }
}
