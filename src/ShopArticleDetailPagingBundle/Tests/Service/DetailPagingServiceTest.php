<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Tests\Service;

use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ArticleListApiInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ListItemInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ListResultInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\RequestToListUrlConverterInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Service\AddParametersToUrlService;
use ChameleonSystem\ShopArticleDetailPagingBundle\Service\DetailPagingService;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class DetailPagingServiceTest extends TestCase
{
    private $listSpotName;
    private $currentListPageUrl;
    /**
     * @var ArticleListApiInterface
     */
    private $ListApi;
    /**
     * @var DetailPagingService
     */
    private $pagerService;
    /**
     * @var ListItemInterface
     */
    private $itemFound;
    /**
     * @var ListResultInterface[]
     */
    private $listResults;
    private $pagerSpotName;
    private $currentItemId;
    private $backToListUrl;

    protected function setUp()
    {
        parent::setUp();

        $this->listResults = array(
            'firstPageUrl' => $this->dataProviderListResult(0, 'secondPageUrl', null),
            'secondPageUrl' => $this->dataProviderListResult(1, 'thirdPageUrl', 'firstPageUrl'),
            'thirdPageUrl' => $this->dataProviderListResult(2, null, 'secondPageUrl'),
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->listSpotName = null;
        $this->pagerSpotName = null;
        $this->currentListPageUrl = null;
        $this->ListApi = null;
        $this->pagerService = null;
        $this->itemFound = null;
        $this->listResults = null;
        $this->currentItemId = null;
        $this->backToListUrl = null;
    }

    /**
     * @test
     * @dataProvider dataProviderListResultForNextItem
     *
     * @param string            $currentItemId
     * @param ListItemInterface $expectedItem
     */
    public function it_should_return_the_next_item($currentListPageURL, $currentItemId, $expectedListPageUrl, $expectedItemId)
    {
        $this->given_that_the_pager_is_in_spot('pagerSpotName');
        $this->given_that_the_list_is_in_spot('spotname');
        $this->given_an_article_list_with($this->listResults);
        $this->given_a_request_url_for_the_current_list_page($currentListPageURL);
        $this->given_a_paging_service();
        $this->when_we_call_getNextItem_with($currentItemId);
        $this->then_we_should_get_an_item_matching($expectedItemId);
        if (null !== $expectedItemId) {
            $this->the_returned_items_url_should_contain_the_paging_module_relevant_parameters($expectedListPageUrl);
        }
    }

    /**
     * @test
     * @dataProvider dataProviderListResultForPreviousItem
     *
     * @param string            $currentItemId
     * @param ListItemInterface $expectedItem
     */
    public function it_should_return_the_previous_item($currentListPageURL, $currentItemId, $expectedListPageUrl, $expectedItemId)
    {
        $this->given_that_the_pager_is_in_spot('pagerSpotName');
        $this->given_that_the_list_is_in_spot('spotname');
        $this->given_an_article_list_with($this->listResults);
        $this->given_a_request_url_for_the_current_list_page($currentListPageURL);
        $this->given_a_paging_service();
        $this->when_we_call_getPreviousItem_with($currentItemId);
        $this->then_we_should_get_an_item_matching($expectedItemId);
        if (null !== $expectedItemId) {
            $this->the_returned_items_url_should_contain_the_paging_module_relevant_parameters($expectedListPageUrl);
        }
    }

    /**
     * @test
     */
    public function it_should_return_the_back_to_list_link()
    {
        $this->given_that_the_pager_is_in_spot('pagerSpotName');
        $this->given_that_the_list_is_in_spot('spotname');
        $this->given_an_article_list_with($this->listResults);
        $this->given_a_request_url_for_the_current_list_page('current-list-request-url');
        $this->given_a_paging_service();
        $this->when_we_call_getBackToListUrl();
        $this->then_we_should_get_a_return_url_matching('current-list-request-url');
    }

    /**
     * @param ListResultInterface[] $listResults
     */
    private function given_an_article_list_with(array $listResults)
    {
        /** @var $listApi ArticleListApiInterface|ObjectProphecy */
        $listApi = $this->prophesize('ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ArticleListApiInterface');
        foreach ($listResults as $listUrl => $result) {
            $listApi->get($listUrl, $this->listSpotName)->willReturn($result);
        }

        $this->ListApi = $listApi->reveal();
    }

    private function given_that_the_list_is_in_spot($spotName)
    {
        $this->listSpotName = $spotName;
    }

    private function given_that_the_pager_is_in_spot($pagerSpotName)
    {
        $this->pagerSpotName = $pagerSpotName;
    }

    private function given_a_request_url_for_the_current_list_page($currentListPageUrl)
    {
        $this->currentListPageUrl = $currentListPageUrl;
    }

    private function given_a_paging_service()
    {
        /** @var $requestToListUrlConverter RequestToListUrlConverterInterface|ObjectProphecy */
        $requestToListUrlConverter = $this->prophesize('ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\RequestToListUrlConverterInterface');
        $requestToListUrlConverter->getListSpotName()->willReturn($this->listSpotName);
        $requestToListUrlConverter->getListUrl()->willReturn($this->currentListPageUrl);
        $listSpotName = $this->listSpotName;
        $requestToListUrlConverter->getPagerParameter($this->listSpotName, Argument::any())->will(function ($args, $requestToListUrlConverter) use ($listSpotName) {
            return array(
                    RequestToListUrlConverterInterface::URL_PARAMETER_SPOT_NAME => $listSpotName,
                    RequestToListUrlConverterInterface::URL_PARAMETER_LIST_URL => $args[1],
                );
        }
        );

        /** @var $addParametersToUrlService AddParametersToUrlService|ObjectProphecy */
        $addParametersToUrlService = $this->prophesize('ChameleonSystem\ShopArticleDetailPagingBundle\Service\AddParametersToUrlService');
        $addParametersToUrlService->addParameterToUrl(Argument::any(), Argument::any())->will(function ($args, $addParametersToUrlService) {
            return $args[0].'?'.http_build_query($args[1]);
        }
        );
        $this->pagerService = new DetailPagingService(
            $this->ListApi, $requestToListUrlConverter->reveal(), $addParametersToUrlService->reveal()
        );
    }

    private function when_we_call_getNextItem_with($currentItemId)
    {
        $this->currentItemId = $currentItemId;
        $this->itemFound = $this->pagerService->getNextItem($currentItemId, $this->pagerSpotName);
    }

    private function when_we_call_getPreviousItem_with($currentItemId)
    {
        $this->currentItemId = $currentItemId;
        $this->itemFound = $this->pagerService->getPreviousItem($currentItemId, $this->pagerSpotName);
    }

    private function then_we_should_get_an_item_matching($expectedItemId)
    {
        $stateString = 'current list url: '.$this->currentListPageUrl.', current item id: '.$this->currentItemId;
        if (null === $expectedItemId) {
            $this->assertNull($this->itemFound, $stateString);
        } else {
            $this->assertEquals($expectedItemId, $this->itemFound->getId(), $stateString);
        }
    }

    public function dataProviderListResultForNextItem()
    {
        return array(
            array(
                'firstPageUrl',
                'page0-FIRST-ITEM',
                'firstPageUrl',
                'page0-SECOND-ITEM',
            ),
            array(
                'firstPageUrl',
                'page0-INNER-ITEM',
                'firstPageUrl',
                'page0-INNER-ITEM-RIGHT',
            ),
            array(
                'firstPageUrl',
                'page0-LAST-ITEM',
                'secondPageUrl',
                'page1-FIRST-ITEM',
            ),

            array(
                'secondPageUrl',
                'page1-FIRST-ITEM',
                'secondPageUrl',
                'page1-SECOND-ITEM',
            ),
            array(
                'secondPageUrl',
                'page1-INNER-ITEM',
                'secondPageUrl',
                'page1-INNER-ITEM-RIGHT',
            ),
            array(
                'secondPageUrl',
                'page1-LAST-ITEM',
                'thirdPageUrl',
                'page2-FIRST-ITEM',
            ),

            array(
                'thirdPageUrl',
                'page2-FIRST-ITEM',
                'thirdPageUrl',
                'page2-SECOND-ITEM',
            ),
            array(
                'thirdPageUrl',
                'page2-INNER-ITEM',
                'thirdPageUrl',
                'page2-INNER-ITEM-RIGHT',
            ),
            array(
                'thirdPageUrl',
                'page2-LAST-ITEM',
                null,
                null,
            ),
        );
    }

    public function dataProviderListResultForPreviousItem()
    {
        return array(
            array(
                'firstPageUrl',
                'page0-FIRST-ITEM',
                null,
                null,
            ),
            array(
                'firstPageUrl',
                'page0-INNER-ITEM',
                'firstPageUrl',
                'page0-INNER-ITEM-LEFT',
            ),
            array(
                'firstPageUrl',
                'page0-LAST-ITEM',
                'firstPageUrl',
                'page0-SECOND-LAST-ITEM',
            ),

            array(
                'secondPageUrl',
                'page1-FIRST-ITEM',
                'firstPageUrl',
                'page0-LAST-ITEM',
            ),
            array(
                'secondPageUrl',
                'page1-INNER-ITEM',
                'secondPageUrl',
                'page1-INNER-ITEM-LEFT',
            ),
            array(
                'secondPageUrl',
                'page1-LAST-ITEM',
                'secondPageUrl',
                'page1-SECOND-LAST-ITEM',
            ),

            array(
                'thirdPageUrl',
                'page2-FIRST-ITEM',
                'secondPageUrl',
                'page1-LAST-ITEM',
            ),
            array(
                'thirdPageUrl',
                'page2-INNER-ITEM',
                'thirdPageUrl',
                'page2-INNER-ITEM-LEFT',
            ),
            array(
                'thirdPageUrl',
                'page2-LAST-ITEM',
                'thirdPageUrl',
                'page2-SECOND-LAST-ITEM',
            ),
        );
    }

    private function dataProviderListResult($page, $nextPageUrl, $previousPageUrl)
    {
        /** @var $listResult ListResultInterface */
        $listResult = $this->prophesize('ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ListResultInterface');
        $listResult->getNextPageUrl()->willReturn($nextPageUrl);
        $listResult->getPreviousPageUrl()->willReturn($previousPageUrl);

        $input = array('FIRST-ITEM', 'SECOND-ITEM', 'INNER-ITEM-LEFT', 'INNER-ITEM', 'INNER-ITEM-RIGHT', 'SECOND-LAST-ITEM', 'LAST-ITEM');
        $items = array();
        foreach ($input as $itemId) {
            $id = 'page'.$page.'-'.$itemId;
            $items[$id] = $this->dataProviderHelperCreateItem($id);
        }

        $listResult->getItemList()->willReturn($items);

        return $listResult;
    }

    private function dataProviderHelperCreateItem($id)
    {
        /** @var $item ListItemInterface|ObjectProphecy */
        $item = $this->prophesize('ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ListItemInterface');
        $item->getId()->willReturn($id);
        $item->getName()->willReturn('Name: '.$id);
        $item->getUrl()->willReturn('Url: '.$id);
        $item->setUrl(Argument::any())->will(function ($args, $item) {
            $item->getUrl()->willReturn($args[0]);
        });

        return $item->reveal();
    }

    private function when_we_call_getBackToListUrl()
    {
        $this->backToListUrl = $this->pagerService->getBackToListUrl($this->pagerSpotName);
    }

    private function then_we_should_get_a_return_url_matching($expectedReturnToListUrl)
    {
        $this->assertEquals($expectedReturnToListUrl, $this->backToListUrl);
    }

    private function the_returned_items_url_should_contain_the_paging_module_relevant_parameters($expectedListUrl)
    {
        $parsedUrl = parse_url($this->itemFound->getUrl(), PHP_URL_QUERY);
        $urlParameters = array();
        parse_str($parsedUrl, $urlParameters);

        $this->assertTrue(isset($urlParameters[RequestToListUrlConverterInterface::URL_PARAMETER_LIST_URL]), 'item links missing list url');
        $this->assertEquals($expectedListUrl, $urlParameters[RequestToListUrlConverterInterface::URL_PARAMETER_LIST_URL]);

        $this->assertTrue(isset($urlParameters[RequestToListUrlConverterInterface::URL_PARAMETER_SPOT_NAME]), 'item links missing list spot name');
        $this->assertEquals($this->listSpotName, $urlParameters[RequestToListUrlConverterInterface::URL_PARAMETER_SPOT_NAME], 'invalid list spot name');
    }
}
