<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Bridge\Module;

use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\RequestToListUrlConverterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;

class ShopArticleListDetailPagePagerExtensionModule extends \ChameleonSystemShopArticleDetailPagingBundleBridgeModuleShopArticleListDetailPagePagerExtensionModuleAutoParent
{
    /**
     * @var RequestToListUrlConverterInterface
     */
    private $requestToListUrlConverter;

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'getAsJson';
    }

    /**
     * {@inheritdoc}
     */
    public function AllowAccessWithoutAuthenticityToken($sMethodName)
    {
        $allowAccess = parent::AllowAccessWithoutAuthenticityToken($sMethodName);

        return $allowAccess || 'getAsJson' === $sMethodName;
    }

    public function setRequestToListUrlConverter(RequestToListUrlConverterInterface $requestToListUrlConverter)
    {
        $this->requestToListUrlConverter = $requestToListUrlConverter;
    }

    protected function getAsJson()
    {
        $enrichedState = $this->enrichStateWithDefaultsFromConfiguration();
        $results = $this->getResults($enrichedState);

        $items = $this->mapItemsAsJson($results->asArray());

        $listPageUrl = $this->getListPageUrl();

        $currentPage = $enrichedState->getState(StateInterface::PAGE, 0);

        $data = array(
            'nextPage' => null,
            'previousPage' => null,
            'items' => $items,
        );
        if ($currentPage > 0) {
            $data['previousPage'] = str_replace('_pageNumber_', $currentPage - 1, $listPageUrl);
        }

        if (($currentPage + 1) < $results->getNumberOfPages()) {
            $data['nextPage'] = str_replace('_pageNumber_', $currentPage + 1, $listPageUrl);
        }

        return $data;
    }

    /**
     * @param \TdbShopArticle[] $itemList
     *
     * @return array
     */
    private function mapItemsAsJson(array $itemList)
    {
        $response = array();
        foreach ($itemList as $item) {
            $response[$item->id] = array('id' => $item->id, 'name' => $item->GetNameSEO(), 'url' => $item->getLink());
        }

        return $response;
    }

    protected function getItemMapperBaseData()
    {
        $baseData = parent::getItemMapperBaseData();
        $baseData['pagerLinkDetails'] = http_build_query($this->requestToListUrlConverter->getPagerParameter($this->sModuleSpotName, $this->getListStateUrl()));

        return $baseData;
    }
}
