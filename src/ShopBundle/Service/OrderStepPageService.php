<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Service;

use ChameleonSystem\CoreBundle\Routing\PortalAndLanguageAwareRouterInterface;
use ChameleonSystem\CoreBundle\Util\RoutingUtilInterface;
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderStepPageService implements OrderStepPageServiceInterface
{
    public const SHOP_CHECKOUT_ROUTE_PREFIX = 'shop_checkout_';

    /**
     * @var PortalAndLanguageAwareRouterInterface
     */
    private $router;
    /**
     * @var UrlUtil
     */
    private $urlUtil;
    /**
     * @var RoutingUtilInterface
     */
    private $routingUtil;

    public function __construct(PortalAndLanguageAwareRouterInterface $router, UrlUtil $urlUtil, RoutingUtilInterface $routingUtil)
    {
        $this->router = $router;
        $this->urlUtil = $urlUtil;
        $this->routingUtil = $routingUtil;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkToOrderStepPageRelative(\TShopOrderStep $orderStep, array $parameters = [], ?\TdbCmsPortal $portal = null, ?\TdbCmsLanguage $language = null)
    {
        $orderStep = $this->getOrderStepInCorrectLanguage($orderStep, $language);
        $parameters = $this->addBasketStepParameter($parameters, $orderStep);

        return $this->router->generateWithPrefixes($this->getBasketStepRouteName($orderStep), $parameters, $portal, $language, UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * @return \TShopOrderStep
     */
    private function getOrderStepInCorrectLanguage(\TShopOrderStep $orderStep, ?\TdbCmsLanguage $language = null)
    {
        /*
         * fieldUrlName needs to be given in the correct language. This requires a reload if another language is requested.
         */
        if ((null !== $language) && $language->id !== $orderStep->GetLanguage()) {
            $orderStep = clone $orderStep;
            $orderStep->SetLanguage($language->id);
            $orderStep->Load($orderStep->id);
        }

        return $orderStep;
    }

    private function addBasketStepParameter(array $parameters, \TShopOrderStep $orderStep): array
    {
        if ('1' === $orderStep->fieldPosition) {
            $parameters['basketStep'] = '/';
        } else {
            $parameters['basketStep'] = '/'.$orderStep->fieldUrlName;
        }

        return $parameters;
    }

    /**
     * @return string
     */
    private function getBasketStepRouteName(\TShopOrderStep $orderStep)
    {
        return self::SHOP_CHECKOUT_ROUTE_PREFIX.$orderStep->fieldSystemname;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkToOrderStepPageAbsolute(\TShopOrderStep $orderStep, array $parameters = [], ?\TdbCmsPortal $portal = null, ?\TdbCmsLanguage $language = null, $forceSecure = false)
    {
        $orderStep = $this->getOrderStepInCorrectLanguage($orderStep, $language);
        $parameters = $this->addBasketStepParameter($parameters, $orderStep);

        $url = $this->router->generateWithPrefixes($this->getBasketStepRouteName($orderStep), $parameters, $portal, $language, UrlGeneratorInterface::ABSOLUTE_URL);

        if (true === $forceSecure) {
            $url = $this->getSecureUrlIfNeeded($url, $portal, $language);
        }

        return $url;
    }

    /**
     * Symfony currently does not allow to enforce generation of secure URLs (a secure URL will only be generated if the
     * route requires HTTPS or if the current request is secure), therefore we turn the URL secure manually.
     *
     * @param string $url
     *
     * @return string
     */
    private function getSecureUrlIfNeeded($url, ?\TdbCmsPortal $portal = null, ?\TdbCmsLanguage $language = null)
    {
        if (false === $this->urlUtil->isUrlSecure($url)) {
            $url = $this->urlUtil->getAbsoluteUrl($url, true, null, $portal, $language);
        }

        return $url;
    }
}
