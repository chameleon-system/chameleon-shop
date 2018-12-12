<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop\WebModules;

use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentConfigFactory;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use TdbShopShippingGroupList;

class AmazonShopOrderWizard extends \ChameleonSystemAmazonPaymentBundlepkgShopWebModulesAmazonShopOrderWizardAutoParent
{
    public function GetHtmlFooterIncludes()
    {
        $footerIncludes = array();
        try {
            if (false === TdbShopShippingGroupList::GetShippingGroupsThatAllowPaymentWith('amazon')) {
                return parent::GetHtmlFooterIncludes();
            }
            $config = AmazonPaymentConfigFactory::createConfig($this->getActivePortalId());
            $footerIncludes[] = "<script type='text/javascript' src='".$config->getWidgetURL()."'></script>";
            $footerIncludes[] = "<script type='text/javascript' src='".\TGlobal::GetStaticURL('/bundles/chameleonsystemamazonpayment/common.js')."'></script>";
        } catch (\InvalidArgumentException $e) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order_amazon')->error(
                'unable to load amazon config: '.(string) $e,
                array('e.message' => $e->getMessage(), 'e.file' => $e->getFile(), 'e.line' => $e->getLine())
            );
            $footerIncludes[] = '<!-- ERROR: unable to load amazon payment config due to config error (invalid parameter). check log for details -->';
        }

        $footerIncludes = array_merge($footerIncludes, parent::GetHtmlFooterIncludes());

        $resources = $this->getResourcesForSnippetPackage('pkgshoppaymentamazon');
        $footerIncludes = array_merge($footerIncludes, $resources);

        return $footerIncludes;
    }

    private function getActivePortalId()
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal()->id;
    }
}
