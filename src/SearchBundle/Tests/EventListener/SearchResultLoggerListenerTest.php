<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SearchBundle\Tests\EventListener;

use ChameleonSystem\SearchBundle\EventListener\SearchResultLoggerListener;
use ChameleonSystem\SearchBundle\Interfaces\ShopSearchLoggerInterface;
use ChameleonSystem\SearchBundle\Interfaces\ShopSearchSessionInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Event\ArticleListFilterExecutedEvent;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

if (false === class_exists('TShopModuleArticleListFilterAutoParent')) {
    require __DIR__ . '/fixtures/TShopModuleArticleListFilterAutoParentMock.php';
}

class SearchResultLoggerListenerTest extends TestCase
{
    private $userHasSearchedBefore;
    private $numberOfResults;
    private $searchParameter;
    /**
     * @var SearchResultLoggerListener
     */
    private $listener;
    private $filterType;

    /**
     * @test
     * @dataProvider dataProviderLogSearch
     *
     * @param $searchParameter
     * @param $numberOfResults
     * @param $userHasSearchedBefore
     */
    public function it_should_log_a_search($filterType, $searchParameter, $numberOfResults, $userHasSearchedBefore)
    {
        $this->given_a_filter_type($filterType);
        $this->given_a_search_for($searchParameter);
        $this->given_search_found($numberOfResults);
        $this->given_the_user_ran_search_before($userHasSearchedBefore);
        $this->given_an_instance_of_the_listener();
        $this->when_we_call_onArticleListResultGenerated();
    }

    private function given_a_search_for($searchParameter)
    {
        $this->searchParameter = $searchParameter;
    }

    private function given_search_found($numberOfResults)
    {
        $this->numberOfResults = $numberOfResults;
    }

    private function given_the_user_ran_search_before($userHasSearchedBefore)
    {
        $this->userHasSearchedBefore = $userHasSearchedBefore;
    }

    private function given_an_instance_of_the_listener()
    {
        $isSearch = ('\TShopModuleArticlelistFilterSearch' === $this->filterType);

        /** @var $mockSession ShopSearchSessionInterface|ObjectProphecy */
        $mockSession = $this->prophesize(ShopSearchSessionInterface::class);

        /** @var $searchLogger ShopSearchLoggerInterface|ObjectProphecy */
        $searchLogger = $this->prophesize(ShopSearchLoggerInterface::class);

        /** @var $shopService ShopServiceInterface|ObjectProphecy */
        $shopService = $this->prophesize(ShopServiceInterface::class);

        if (true === $isSearch) {
            if ($this->userHasSearchedBefore) {
                $mockSession->hasSearchedFor(Argument::any())->willReturn(true);
                $mockSession->addSearch($this->searchParameter)->shouldNotBeCalled();
                $searchLogger->logSearch(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();
            } else {
                $mockSession->hasSearchedFor(Argument::any())->willReturn(false);
                $mockSession->addSearch($this->searchParameter)->shouldBeCalled();
                $mockActiveShop = new \stdClass();
                $mockActiveShop->fieldUseShopSearchLog = true;
                $shopService->getActiveShop()->willReturn($mockActiveShop);
                $searchLogger->logSearch(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
            }
        } else {
            $searchLogger->logSearch(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();
        }

        $this->listener = new SearchResultLoggerListener($mockSession->reveal(), $searchLogger->reveal(), $shopService->reveal());
    }

    private function when_we_call_onArticleListResultGenerated()
    {
        /** @var $event ArticleListFilterExecutedEvent|ObjectProphecy */
        $event = $this->prophesize(ArticleListFilterExecutedEvent::class);

        /** @var $state StateInterface|ObjectProphecy */
        $state = $this->prophesize(StateInterface::class);
        $state->getState(StateInterface::QUERY, array())->willReturn($this->searchParameter);
        $event->getState()->willReturn($state->reveal());

        $filter = $this->prophesize($this->filterType);
        $event->getFilter()->willReturn($filter->reveal());

        /** @var $resultData ResultDataInterface|ObjectProphecy */
        $resultData = $this->prophesize(ResultDataInterface::class);
        $resultData->getTotalNumberOfResults()->willReturn($this->numberOfResults);
        $event->getResultData()->willReturn($resultData->reveal());

        $this->listener->onArticleListResultGenerated($event->reveal());
    }

    public function dataProviderLogSearch()
    {
        return array(
            array(
                '\TShopModuleArticlelistFilterSearch', // is search filter
                array('q' => 'something', 'lf' => array()), // $searchParameter
                10, // $numberOfResults
                false, // $userHasSearchedBefore
            ),
            array(
                '\TShopModuleArticlelistFilterSearch', // is search filter
                array('q' => 'something', 'lf' => array()), // $searchParameter
                10, // $numberOfResults
                true, // $userHasSearchedBefore
            ),
            array(
                '\TShopModuleArticleListFilter', // is search filter
                array('q' => 'something', 'lf' => array()), // $searchParameter
                10, // $numberOfResults
                false, // $userHasSearchedBefore
            ),
        );
    }

    private function given_a_filter_type($filterType)
    {
        $this->filterType = $filterType;
    }
}
