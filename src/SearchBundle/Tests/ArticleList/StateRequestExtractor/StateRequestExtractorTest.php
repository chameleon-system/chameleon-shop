<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SearchBundle\Tests\ArticleList\StateRequestExtractor;

use ChameleonSystem\SearchBundle\ArticleList\StateRequestExtractor\StateRequestExtractor;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use PHPUnit\Framework\TestCase;

if (false === class_exists('TdbShopModuleArticleListFilter')) {
    require __DIR__.'/fixtures/TdbShopModuleArticleListFilterMock.php';
}

class StateRequestExtractorTest extends TestCase
{
    /**
     * @var StateRequestExtractor
     */
    private $extractor;
    private $requestData;
    private $returnData;

    /**
     * @test
     *
     * @dataProvider dataProviderRequestData
     */
    public function itShouldExtractTheActiveSearchQuery($requestData, $expectedReturnData)
    {
        $this->given_a_state_request_extractor();
        $this->given_request_data($requestData);
        $this->when_we_call_extract();
        $this->then_we_expect_return_data_matching($expectedReturnData);
    }

    private function given_a_state_request_extractor()
    {
        $this->extractor = new StateRequestExtractor();
    }

    private function given_request_data($requestData)
    {
        $this->requestData = $requestData;
    }

    private function when_we_call_extract()
    {
        $this->returnData = $this->extractor->extract([], $this->requestData, 'spotname');
    }

    private function then_we_expect_return_data_matching($expectedReturnData)
    {
        $this->assertEquals($expectedReturnData, $this->returnData);
    }

    public function dataProviderRequestData()
    {
        return [
            [
                ['foo' => 'bar'],
                [],
            ],
            [
                [\TShopModuleArticlelistFilterSearch::PARAM_QUERY => 'somequery'],
                [StateInterface::QUERY => [\TShopModuleArticlelistFilterSearch::PARAM_QUERY => 'somequery']],
            ],
            [
                [\TShopModuleArticlelistFilterSearch::URL_FILTER => ['some' => 'filter']],
                [StateInterface::QUERY => [\TShopModuleArticlelistFilterSearch::URL_FILTER => ['some' => 'filter']]],
            ],
            [
                [\TShopModuleArticlelistFilterSearch::PARAM_QUERY => 'somequery', \TShopModuleArticlelistFilterSearch::URL_FILTER => ['some' => 'filter']],
                [StateInterface::QUERY => [\TShopModuleArticlelistFilterSearch::PARAM_QUERY => 'somequery', \TShopModuleArticlelistFilterSearch::URL_FILTER => ['some' => 'filter']]],
            ],
        ];
    }
}
