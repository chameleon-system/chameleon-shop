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
use ChameleonSystem\CoreBundle\ServiceLocator;
use Psr\Log\LoggerInterface;
use TGlobal;

class AmazonShopBasket extends \ChameleonSystemAmazonPaymentBundlepkgShopWebModulesAmazonShopBasketAutoParent
{
    public function GetHtmlFooterIncludes(): array
    {
        $includes = parent::GetHtmlFooterIncludes();
        $activePortal = $this->getActivePortal();
        /*
         * Always load Amazon payment JS if it is active for the current portal. Otherwise the button links would not
         * work if the user just added a product via Ajax to the previously empty basket.
         */
        if (false === $this->getPaymentInfoService()->isPaymentMethodActive('amazon', $activePortal)) {
            return $includes;
        }
        try {
            $config = AmazonPaymentConfigFactory::createConfig($activePortal->id);
            $includes[] = "<script type='text/javascript' src='".$config->getWidgetURL()."'></script>";
            $includes[] = "<script type='text/javascript' src='".TGlobal::GetStaticURL('/bundles/chameleonsystemamazonpayment/common.js')."'></script>";
        } catch (\InvalidArgumentException $e) {
            $this->getLogger()->error(
                'unable to load amazon config: '.(string) $e,
                array('e.message' => $e->getMessage(), 'e.file' => $e->getFile(), 'e.line' => $e->getLine())
            );
            $includes[] = '<!-- ERROR: unable to load amazon payment config due to config error (invalid parameter). check log for details -->';
        }

        return $includes;
    }

    private function getActivePortal(): ?\TdbCmsPortal
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal();
    }

    /**
     * @return \ChameleonSystem\ShopBundle\Interfaces\PaymentInfoServiceInterface
     */
    private function getPaymentInfoService()
    {
        return ServiceLocator::get('chameleon_system_shop.payment_info_service');
    }

    private function getLogger(): LoggerInterface
    {
        return ServiceLocator::get('monolog.logger.order_payment_amazon');
    }
}
