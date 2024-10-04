<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use esono\pkgCmsRouting\CollectionGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class TPkgShopCategoryRouteCollectionGenerator
 * create the routing info for categories. we can not use a yml here, since the page identifier is language specific.
 */
class TPkgShopCategoryRouteCollectionGenerator implements CollectionGeneratorInterface
{
    /**
     * @var SystemPageServiceInterface
     */
    private $systemPageService;

    /**
     * @param SystemPageServiceInterface $systemPageService
     */
    public function __construct(SystemPageServiceInterface $systemPageService)
    {
        $this->systemPageService = $systemPageService;
    }

    /**
     * @param array          $config
     * @param TdbCmsPortal   $portal
     * @param TdbCmsLanguage $language
     *
     * @return RouteCollection
     *
     * @throws Exception
     * @throws TPkgCmsException_Log
     */
    public function getCollection($config, TdbCmsPortal $portal, TdbCmsLanguage $language)
    {
        $systemPage = $this->systemPageService->getSystemPage('products', $portal, $language);
        if (null === $systemPage) {
            throw new TPkgCmsException_Log('No category system page defined (portal system page with name "products")', array('portal' => $portal->id));
        }
        $productNodeId = $systemPage->fieldCmsTreeId;
        $productNode = TdbCmsTree::GetNewInstance($productNodeId, $language->id);
        if (false === $productNode->sqlData) {
            throw new TPkgCmsException_Log('No product list node is assigned to the category system page (portal system page with name "products")', array('portal' => $portal->id));
        }

        $pageDef = $productNode->GetLinkedPage();
        if (false === $pageDef) {
            throw new Exception("The product list node {$productNode->id} for portal {$portal->id} has no page assigned to it");
        }

        $aPath = array();
        $breadcrumb = $productNode->GetBreadcrumb(true);
        /** @var TdbCmsTree $node */
        while ($node = $breadcrumb->Next()) {
            $aPath[] = $node->fieldUrlname;
        }

        $path = implode('/', $aPath);

        // exclude any sub-pages
        $childNodes = $productNode->GetChildren(true, $language->id);
        $excludeList = array();
        $excludeString = '';
        while ($childNode = $childNodes->Next()) {
            $excludeList[] = str_replace('|', '\\|', '/'.$childNode->fieldUrlname);
        }
        if (count($excludeList) > 0) {
            $excludeString = '(?!'.implode('|', $excludeList).')';
        }

        $route = new Route('/{categoryPath}/{category}',
            array('_controller' => 'chameleon_system_shop.product_controller::shopCategory', 'pagedef' => $pageDef),
            array(
                'categoryPath' => "(?i:{$path}){$excludeString}",
                'category' => '(.+)',
            )
        );

        $collection = new RouteCollection();
        $collection->add('shop_category', $route);

        return $collection;
    }
}
