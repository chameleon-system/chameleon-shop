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
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    public function &Execute()
    {
        parent::Execute();

        try {
            $this->data['sSearchURL'] = $this->getSystemPageService()->getLinkToSystemPageRelative('search');
            $this->data['q'] = $this->getSearchQuery();
            $this->data['quicksearchUrl'] = $this->getRouter()->generateWithPrefixes('chameleon_system_shop.search_suggest');
        } catch (RouteNotFoundException $e) {
            // nothing to do
        }

        return $this->data;
    }
    
    protected function getSearchQuery(): string
    {
        $inputFilterService = $this->getInputFilterService();
        $queryString = $inputFilterService->getFilteredInput('q', '');
        
        if (false === \is_string($queryString)) {
            return '';
        }

        return \trim($queryString);
    }

    /**
     * prevent caching if there are messages.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        $inputFilterService = $this->getInputFilterService();
        $queryString = $inputFilterService->getFilteredInput('q');
        
        return null === $queryString;
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

    private function getRouter(): PortalAndLanguageAwareRouterInterface
    {
        return ServiceLocator::get('chameleon_system_core.router.chameleon_frontend');
    }

    private function getSystemPageService(): SystemPageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.system_page_service');
    }
    
    private function getInputFilterService(): InputFilterUtilInterface
    {
        return ServiceLocator::get('chameleon_system_core.util.input_filter');
    }
}
