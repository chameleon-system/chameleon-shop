<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps;

use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentConfigFactory;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use TGlobal;

class AmazonConfirmOrderStep extends \TShopStepConfirm
{
    /**
     * {@inheritdoc}
     */
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        $user = $this->getExtranetUserProvider()->getActiveUser();

        return true === $user->isAmazonPaymentUser() &&
            false === $this->getShopService()->getActiveBasket()->hasAmazonPaymentError();
    }

    protected function isApplicableForBasket(\TShopBasket $basket)
    {
        return null !== $basket->getAmazonOrderReferenceId();
    }

    public function GetHtmlFooterIncludes()
    {
        $includes = parent::GetHtmlFooterIncludes();

        $includes[] = "<script type='text/javascript' src='".TGlobal::GetStaticURL('/bundles/chameleonsystemamazonpayment/widgets/addressRead.js')."'></script>";
        $includes[] = "<script type='text/javascript' src='".TGlobal::GetStaticURL('/bundles/chameleonsystemamazonpayment/widgets/walletRead.js')."'></script>";

        return $includes;
    }

    protected function &GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $data = parent::GetAdditionalViewVariables($sViewName, $sViewType);

        try {
            $data['amazonConfig'] = AmazonPaymentConfigFactory::createConfig($this->getActivePortalId());
        } catch (\InvalidArgumentException $e) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order_amazon')->error(
                'unable to load amazon config: '.(string) $e,
                array('e.message' => $e->getMessage(), 'e.file' => $e->getFile(), 'e.line' => $e->getLine())
            );
            $data['amazonConfig'] = null;
        }

        return $data;
    }

    private function getActivePortalId()
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal()->id;
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
