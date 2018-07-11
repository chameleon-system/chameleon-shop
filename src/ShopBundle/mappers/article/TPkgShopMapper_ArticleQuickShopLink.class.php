<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Routing\PortalAndLanguageAwareRouterInterface;

class TPkgShopMapper_ArticleQuickShopLink extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        if ($oArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
        }
        /** @var $oShop TdbShop */
        $oShop = $oVisitor->GetSourceObject('oShop');
        if ($oShop && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oShop->table, $oShop->id);
        }

        // add all current get parameters to the url
        $request = $this->getRequest();
        $parameters = $request->query->all();
        $parameters = array_merge_recursive($parameters, $request->request->all());
        if (isset($parameters['pagedef'])) {
            unset($parameters['pagedef']);
        }
        $parameters['identifier'] = $oArticle->sqlData['cmsident'];
        $router = $this->getRouter();
        $quickShopLink = $router->generateWithPrefixes('shop_article_quickshop', $parameters);

        $oVisitor->SetMappedValue('sQuickShopLink', $quickShopLink);
        $oVisitor->SetMappedValue('sQuickShopDisable', $this->isQuickShopDisabled());
    }

    /**
     * @return bool
     */
    protected function isQuickShopDisabled()
    {
        $bDisableQuickShop = false;
        if (defined('SHOP_DISABLE_QUICK_SHOP')) {
            $bDisableQuickShop = SHOP_DISABLE_QUICK_SHOP;
        }

        return $bDisableQuickShop;
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    private function getRequest()
    {
        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack');

        return $requestStack->getCurrentRequest();
    }

    /**
     * @return PortalAndLanguageAwareRouterInterface
     */
    private function getRouter()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.router.chameleon_frontend');
    }
}
