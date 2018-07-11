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
    require __DIR__ . '/fixtures/TdbShopModuleArticleListFilterMock.php';
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
     * @dataProvider dataProviderRequestData
     *
     * @param $requestData
     * @param $expectedReturnData
     */
    public function it_should_extract_the_active_search_query($requestData, $expectedReturnData)
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
        $this->returnData = $this->extractor->extract(array(), $this->requestData, 'spotname');
    }

    private function then_we_expect_return_data_matching($expectedReturnData)
    {
        $this->assertEquals($expectedReturnData, $this->returnData);
    }

    public function dataProviderRequestData()
    {
        return array(
            array(
                array('foo' => 'bar'),
                array(),
            ),
            array(
                array(\TShopModuleArticlelistFilterSearch::PARAM_QUERY => 'somequery'),
                array(StateInterface::QUERY => array(\TShopModuleArticlelistFilterSearch::PARAM_QUERY => 'somequery')),
            ),
            array(
                array(\TShopModuleArticlelistFilterSearch::URL_FILTER => array('some' => 'filter')),
                array(StateInterface::QUERY => array(\TShopModuleArticlelistFilterSearch::URL_FILTER => array('some' => 'filter'))),
            ),
            array(
                array(\TShopModuleArticlelistFilterSearch::PARAM_QUERY => 'somequery', \TShopModuleArticlelistFilterSearch::URL_FILTER => array('some' => 'filter')),
                array(StateInterface::QUERY => array(\TShopModuleArticlelistFilterSearch::PARAM_QUERY => 'somequery', \TShopModuleArticlelistFilterSearch::URL_FILTER => array('some' => 'filter'))),
            ),
        );
    }
}
