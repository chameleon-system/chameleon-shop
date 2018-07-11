<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Tests\ArticleList;

use ChameleonSystem\ShopArticleDetailPagingBundle\ArticleList\ArticleListApi;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ContentFromUrlLoaderServiceInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ListResultInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Service\AddParametersToUrlService;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class ArticleListApiTest extends TestCase
{
    private $listSpotName;
    private $listUrl;
    private $responsePayload;
    /**
     * @var ArticleListApi
     */
    private $api;
    /**
     * @var ListResultInterface
     */
    private $apiResult;

    /**
     * @test
     * @dataProvider dataProviderListResponse
     *
     * @param $responsePayload
     * @param $expectedNextPageUrl
     * @param $expectedPreviousPageUrl
     * @param $expectedItems
     */
    public function it_fetches_a_response($responsePayload, $expectedPreviousPageUrl, $expectedNextPageUrl, $expectedItems)
    {
        $listSpotName = 'listSpotName';
        $listUrl = 'listUrl';

        $this->given_that_the_list_is_in_spot($listSpotName);
        $this->given_the_list_url_is($listUrl);
        $this->given_that_the_list_response_payload_is($responsePayload);
        $this->given_an_instance_of_the_api();
        $this->when_we_call_get();
        $this->we_expect_a_result_with($expectedNextPageUrl, $expectedPreviousPageUrl, $expectedItems);
    }

    public function dataProviderListResponse()
    {
        return array(
            array(
                json_encode(
                    array(
                        'previousPage' => 'previousPageUrl',
                        'nextPage' => 'nextPageUrl',
                        'items' => array(
                            'FIRST-ITEM' => array('id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'),
                            'SECOND-ITEM' => array('id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'),
                            'LAST-ITEM' => array('id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'),
                        ),
                    )
                ), //$responsePayload
                'previousPageUrl', //$expectedPreviousPageUrl
                'nextPageUrl', //$expectedNextPageUrl
                array(
                    'FIRST-ITEM' => array('id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'),
                    'SECOND-ITEM' => array('id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'),
                    'LAST-ITEM' => array('id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'),
                ), //$expectedItems
            ),

            array(
                json_encode(
                    array(
                        'previousPage' => null,
                        'nextPage' => 'nextPageUrl',
                        'items' => array(
                            'FIRST-ITEM' => array('id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'),
                            'SECOND-ITEM' => array('id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'),
                            'LAST-ITEM' => array('id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'),
                        ),
                    )
                ), //$responsePayload
                null, //$expectedPreviousPageUrl
                'nextPageUrl', //$expectedNextPageUrl
                array(
                    'FIRST-ITEM' => array('id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'),
                    'SECOND-ITEM' => array('id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'),
                    'LAST-ITEM' => array('id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'),
                ), //$expectedItems
            ),

            array(
                json_encode(
                    array(
                        'previousPage' => 'previousPageUrl',
                        'nextPage' => null,
                        'items' => array(
                            'FIRST-ITEM' => array('id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'),
                            'SECOND-ITEM' => array('id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'),
                            'LAST-ITEM' => array('id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'),
                        ),
                    )
                ), //$responsePayload
                'previousPageUrl', //$expectedPreviousPageUrl
                null, //$expectedNextPageUrl
                array(
                    'FIRST-ITEM' => array('id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'),
                    'SECOND-ITEM' => array('id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'),
                    'LAST-ITEM' => array('id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'),
                ), //$expectedItems
            ),

            array(
                json_encode(
                    array(
                        'previousPage' => null,
                        'nextPage' => null,
                        'items' => array(
                            'FIRST-ITEM' => array('id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'),
                            'SECOND-ITEM' => array('id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'),
                            'LAST-ITEM' => array('id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'),
                        ),
                    )
                ), //$responsePayload
                null, //$expectedPreviousPageUrl
                null, //$expectedNextPageUrl
                array(
                    'FIRST-ITEM' => array('id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'),
                    'SECOND-ITEM' => array('id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'),
                    'LAST-ITEM' => array('id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'),
                ), //$expectedItems
            ),

            array(
                json_encode(
                    array(
                        'previousPage' => null,
                        'nextPage' => null,
                        'items' => array(),
                    )
                ), //$responsePayload
                null, //$expectedPreviousPageUrl
                null, //$expectedNextPageUrl
                array(), //$expectedItems
            ),
        );
    }

    private function given_that_the_list_is_in_spot($listSpotName)
    {
        $this->listSpotName = $listSpotName;
    }

    private function given_the_list_url_is($listUrl)
    {
        $this->listUrl = $listUrl;
    }

    private function given_that_the_list_response_payload_is($responsePayload)
    {
        $this->responsePayload = $responsePayload;
    }

    private function when_we_call_get()
    {
        $this->apiResult = $this->api->get($this->listUrl, $this->listSpotName);
    }

    private function given_an_instance_of_the_api()
    {
        /** @var $addParametersToUrlService AddParametersToUrlService|ObjectProphecy */
        $addParametersToUrlService = $this->prophesize('ChameleonSystem\ShopArticleDetailPagingBundle\Service\AddParametersToUrlService');
        $addParametersToUrlService->addParameterToUrl(Argument::any(), Argument::any())->will(function ($args, $addParametersToUrlService) {
            return $args[0];
        }
        );

        /** @var $contentLoader ContentFromUrlLoaderServiceInterface|ObjectProphecy */
        $contentLoader = $this->prophesize('ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ContentFromUrlLoaderServiceInterface');
        $contentLoader->load(Argument::any())->willReturn($this->responsePayload);

        $this->api = new ArticleListApi($contentLoader->reveal(), $addParametersToUrlService->reveal());
    }

    private function we_expect_a_result_with($expectedNextPageUrl, $expectedPreviousPageUrl, $expectedItems)
    {
        $msg = "for {$this->listUrl} in spot {$this->listSpotName} we got ".print_r($this->apiResult, true);
        $this->assertEquals($expectedNextPageUrl, $this->apiResult->getNextPageUrl(), $msg);
        $this->assertEquals($expectedPreviousPageUrl, $this->apiResult->getPreviousPageUrl(), $msg);

        $resultItems = $this->apiResult->getItemList();
        $resultItemsAsPlainArray = array();
        foreach ($resultItems as $resultItem) {
            $resultItemsAsPlainArray[$resultItem->getId()] = array('id' => $resultItem->getId(), 'name' => $resultItem->getName(), 'url' => $resultItem->getUrl());
        }

        $this->assertEquals($expectedItems, $resultItemsAsPlainArray, $msg);
    }
}
