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
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ArticleListApiTest extends TestCase
{
    use ProphecyTrait;

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
     *
     * @dataProvider dataProviderListResponse
     */
    public function itFetchesAResponse($responsePayload, $expectedPreviousPageUrl, $expectedNextPageUrl, $expectedItems)
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
        return [
            [
                json_encode(
                    [
                        'previousPage' => 'previousPageUrl',
                        'nextPage' => 'nextPageUrl',
                        'items' => [
                            'FIRST-ITEM' => ['id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'],
                            'SECOND-ITEM' => ['id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'],
                            'LAST-ITEM' => ['id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'],
                        ],
                    ]
                ), // $responsePayload
                'previousPageUrl', // $expectedPreviousPageUrl
                'nextPageUrl', // $expectedNextPageUrl
                [
                    'FIRST-ITEM' => ['id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'],
                    'SECOND-ITEM' => ['id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'],
                    'LAST-ITEM' => ['id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'],
                ], // $expectedItems
            ],

            [
                json_encode(
                    [
                        'previousPage' => null,
                        'nextPage' => 'nextPageUrl',
                        'items' => [
                            'FIRST-ITEM' => ['id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'],
                            'SECOND-ITEM' => ['id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'],
                            'LAST-ITEM' => ['id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'],
                        ],
                    ]
                ), // $responsePayload
                null, // $expectedPreviousPageUrl
                'nextPageUrl', // $expectedNextPageUrl
                [
                    'FIRST-ITEM' => ['id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'],
                    'SECOND-ITEM' => ['id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'],
                    'LAST-ITEM' => ['id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'],
                ], // $expectedItems
            ],

            [
                json_encode(
                    [
                        'previousPage' => 'previousPageUrl',
                        'nextPage' => null,
                        'items' => [
                            'FIRST-ITEM' => ['id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'],
                            'SECOND-ITEM' => ['id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'],
                            'LAST-ITEM' => ['id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'],
                        ],
                    ]
                ), // $responsePayload
                'previousPageUrl', // $expectedPreviousPageUrl
                null, // $expectedNextPageUrl
                [
                    'FIRST-ITEM' => ['id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'],
                    'SECOND-ITEM' => ['id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'],
                    'LAST-ITEM' => ['id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'],
                ], // $expectedItems
            ],

            [
                json_encode(
                    [
                        'previousPage' => null,
                        'nextPage' => null,
                        'items' => [
                            'FIRST-ITEM' => ['id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'],
                            'SECOND-ITEM' => ['id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'],
                            'LAST-ITEM' => ['id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'],
                        ],
                    ]
                ), // $responsePayload
                null, // $expectedPreviousPageUrl
                null, // $expectedNextPageUrl
                [
                    'FIRST-ITEM' => ['id' => 'FIRST-ITEM', 'name' => 'first item', 'url' => 'first item url'],
                    'SECOND-ITEM' => ['id' => 'SECOND-ITEM', 'name' => 'second item', 'url' => 'second item url'],
                    'LAST-ITEM' => ['id' => 'LAST-ITEM', 'name' => 'last item', 'url' => 'last item url'],
                ], // $expectedItems
            ],

            [
                json_encode(
                    [
                        'previousPage' => null,
                        'nextPage' => null,
                        'items' => [],
                    ]
                ), // $responsePayload
                null, // $expectedPreviousPageUrl
                null, // $expectedNextPageUrl
                [], // $expectedItems
            ],
        ];
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
        $resultItemsAsPlainArray = [];
        foreach ($resultItems as $resultItem) {
            $resultItemsAsPlainArray[$resultItem->getId()] = ['id' => $resultItem->getId(), 'name' => $resultItem->getName(), 'url' => $resultItem->getUrl()];
        }

        $this->assertEquals($expectedItems, $resultItemsAsPlainArray, $msg);
    }
}
