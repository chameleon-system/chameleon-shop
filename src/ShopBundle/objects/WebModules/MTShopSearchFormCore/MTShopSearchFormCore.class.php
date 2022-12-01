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
use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * module used to render search forms.
/**/
class MTShopSearchFormCore extends TShopUserCustomModelBase
{
    protected $bAllowHTMLDivWrapping = true;

    /**
     * {@inheritdoc}
     */
    public function Execute()
    {
        parent::Execute();

        try {
            $this->data['sSearchURL'] = $this->getSystemPageService()->getLinkToSystemPageRelative('search');
            $this->data['q'] = $this->global->GetUserData('q');
            $this->data['quicksearchUrl'] = $this->getRouter()->generateWithPrefixes('chameleon_system_shop.search_suggest');
        } catch (RouteNotFoundException $e) {
            // nothing to do
        }

        return $this->data;
    }

    /**
     * prevent caching if there are messages.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return false === $this->global->UserDataExists('q');
    }

    /**
     * {@inheritdoc}
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgShop/MTShopSearchForm'));

        return $aIncludes;
    }

    /**
     * @return PortalAndLanguageAwareRouterInterface
     */
    private function getRouter()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.router.chameleon_frontend');
    }

    /**
     * @return SystemPageServiceInterface
     */
    private function getSystemPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.system_page_service');
    }
}
