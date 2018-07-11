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
use TdbCmsLanguage;
use TdbCmsPortal;
use TShopOrderStep;

class OrderStepPageService implements OrderStepPageServiceInterface
{
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

    /**
     * @param PortalAndLanguageAwareRouterInterface $router
     * @param UrlUtil                               $urlUtil
     * @param RoutingUtilInterface                  $routingUtil
     */
    public function __construct(PortalAndLanguageAwareRouterInterface $router, UrlUtil $urlUtil, RoutingUtilInterface $routingUtil)
    {
        $this->router = $router;
        $this->urlUtil = $urlUtil;
        $this->routingUtil = $routingUtil;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkToOrderStepPageRelative(TShopOrderStep $orderStep, array $parameters = array(), TdbCmsPortal $portal = null, TdbCmsLanguage $language = null)
    {
        $orderStep = $this->getOrderStepInCorrectLanguage($orderStep, $language);
        $this->addBasketStepParameter($parameters, $orderStep);

        return $this->router->generateWithPrefixes($this->getBasketStepRouteName($orderStep), $parameters, $portal, $language, UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * @param TShopOrderStep      $orderStep
     * @param TdbCmsLanguage|null $language
     *
     * @return TShopOrderStep
     */
    private function getOrderStepInCorrectLanguage(TShopOrderStep $orderStep, TdbCmsLanguage $language = null)
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

    /**
     * @param array          $parameters
     * @param TShopOrderStep $orderStep
     */
    private function addBasketStepParameter(array &$parameters, TShopOrderStep $orderStep)
    {
        $parameters['basketStep'] = '/'.$orderStep->fieldUrlName;
    }

    /**
     * @param TShopOrderStep $orderStep
     *
     * @return string
     */
    private function getBasketStepRouteName(TShopOrderStep $orderStep)
    {
        return 'shop_checkout_'.$orderStep->fieldSystemname;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkToOrderStepPageAbsolute(TShopOrderStep $orderStep, array $parameters = array(), TdbCmsPortal $portal = null, TdbCmsLanguage $language = null, $forceSecure = false)
    {
        $orderStep = $this->getOrderStepInCorrectLanguage($orderStep, $language);
        $this->addBasketStepParameter($parameters, $orderStep);

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
     * @param string              $url
     * @param TdbCmsPortal|null   $portal
     * @param TdbCmsLanguage|null $language
     *
     * @return string
     */
    private function getSecureUrlIfNeeded($url, TdbCmsPortal $portal = null, TdbCmsLanguage $language = null)
    {
        if (false === $this->urlUtil->isUrlSecure($url)) {
            $url = $this->urlUtil->getAbsoluteUrl($url, true, null, $portal, $language);
        }

        return $url;
    }
}
