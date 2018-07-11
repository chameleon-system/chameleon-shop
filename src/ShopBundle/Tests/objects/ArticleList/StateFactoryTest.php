<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Tests\objects\ArticleList;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\State;
use ChameleonSystem\ShopBundle\objects\ArticleList\StateFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class StateFactoryTest extends TestCase
{
    /**
     * @var array
     */
    private $userData;
    /**
     * @var \ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface
     */
    private $state;
    /**
     * @var StateFactory
     */
    private $stateFactory;
    /**
     * @var ConfigurationInterface
     */
    private $configuration;
    /**
     * @var \ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface
     */
    private $enrichedState;

    /**
     * need to use static method since dataProviders are called before setUpBeforeClass - but they need the factory.
     *
     * @return StateFactory
     */
    public static function getStateFactory()
    {
        $stateFactory = new \ChameleonSystem\ShopBundle\objects\ArticleList\StateFactory();
        $stateFactory->registerStateElement(new \ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementCurrentPage());
        $stateFactory->registerStateElement(new \ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementSort());
        $stateFactory->registerStateElement(new \ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementPageSize(array(
                null,
                5,
                10,
                15,
                20,
                21,
            )));
        $stateFactory->registerStateElement(new \ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementQuery());

        return $stateFactory;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->stateFactory = clone self::getStateFactory();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->userData = null;
        $this->state = null;
        $this->stateFactory = null;
    }

    /**
     * @test
     * @dataProvider dataProviderUserInput
     *
     * @param $userData
     * @param $expectedState
     */
    public function it_creates_state_object_from_user_input($userData, $expectedState)
    {
        $this->givenUserData($userData);
        $this->whenWeCallTheFactoryMethod();
        $this->thenWeExpectTheState($expectedState);
    }

    public function dataProviderUserInput()
    {
        $data = array();

        // from state string plus param
        $expectedState = self::getStateFactory()->createState();
        $expectedState->setState(StateInterface::PAGE, 4);
        $expectedState->setState(StateInterface::PAGE_SIZE, 20);
        $stateString = $expectedState->getStateString(array(StateInterface::PAGE));
        $userData = array(StateInterface::STATE_STRING => $stateString, StateInterface::PAGE => 4);
        $data[] = array($userData, $expectedState);

        // from parameter array
        $param = array(
            StateInterface::PAGE => 5,
            StateInterface::PAGE_SIZE => 21,
            StateInterface::SORT => 'somesortid',
        );
        $expectedState = self::getStateFactory()->createState();
        $expectedState->setState(StateInterface::PAGE, 5);
        $expectedState->setState(StateInterface::PAGE_SIZE, 21);
        $expectedState->setState(StateInterface::SORT, 'somesortid');
        $data[] = array($param, $expectedState);

        // nothing
        $expectedState = self::getStateFactory()->createState();
        $data[] = array(array(), $expectedState);
        $data[] = array(null, $expectedState);

        return $data;
    }

    private function givenUserData($userData)
    {
        $this->userData = $userData;
    }

    private function whenWeCallTheFactoryMethod()
    {
        $this->state = $this->stateFactory->createState($this->userData);
    }

    private function thenWeExpectTheState(StateInterface $expectedState)
    {
        $this->assertEquals($expectedState->getStateArray(), $this->state->getStateArray());
    }

    /**
     * @test
     * @dataProvider dataProviderEnrichedStates
     *
     * @param \ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface $state
     * @param array                                                                     $defaultStateValues
     * @param \ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface $expectedState
     */
    public function it_should_create_a_state_based_on_another_state_enriched_by_default_values(StateInterface $state, array $defaultStateValues, array $expectedStateData)
    {
        $this->givenAState($state);
        $this->givenDefaultStateValues($defaultStateValues);
        $this->whenWeCallTheEnrichFactoryMethod();
        $this->thenWeExpectTheEnrichedStateToHave($expectedStateData);
    }

    public function dataProviderEnrichedStates()
    {
        $data = array();

        $state = self::getStateFactory()->createState();
        $defaultStateValues = array(
            StateInterface::PAGE_SIZE => 10,
            StateInterface::SORT => 'somesortid',
        );
        $data[] = array($state, $defaultStateValues, $defaultStateValues);

        $state = self::getStateFactory()->createState();
        $state->setState(StateInterface::SORT, 'newsortid');
        $state->setState(StateInterface::QUERY, 'somequery');
        $expectedResult = $defaultStateValues;
        $expectedResult[StateInterface::SORT] = 'newsortid';
        $expectedResult[StateInterface::QUERY] = 'somequery';
        $data[] = array($state, $defaultStateValues, $expectedResult);

        $state = self::getStateFactory()->createState();
        $state->setState(StateInterface::PAGE_SIZE, 20);
        $expectedResult = $defaultStateValues;
        $expectedResult[StateInterface::PAGE_SIZE] = 20;
        $data[] = array($state, $defaultStateValues, $expectedResult);

        $state = self::getStateFactory()->createState();
        $state->setState(StateInterface::PAGE, 2);
        $expectedResult = $defaultStateValues;
        $expectedResult[StateInterface::PAGE] = 2;
        $data[] = array($state, $defaultStateValues, $expectedResult);

        $state = self::getStateFactory()->createState();
        $state->setState(StateInterface::SORT, 'newsortid');
        $expectedResult = $defaultStateValues;
        $expectedResult[StateInterface::SORT] = 'newsortid';
        $data[] = array($state, $defaultStateValues, $expectedResult);

        return $data;
    }

    private function givenAState(StateInterface $state)
    {
        $this->state = $state;
    }

    private function givenDefaultStateValues(array $defaultStateValues)
    {
        /** @var $configuration ConfigurationInterface|ObjectProphecy */
        $configuration = $this->prophesize('ChameleonSystem\pkgshoplistfilter\DatabaseAccessLayer\Interfaces\ConfigurationInterface');
        if (isset($defaultStateValues[StateInterface::SORT])) {
            $configuration->getDefaultSortId()->willReturn($defaultStateValues[StateInterface::SORT]);
        }
        if (isset($defaultStateValues[StateInterface::PAGE_SIZE])) {
            $configuration->getDefaultPageSize()->willReturn($defaultStateValues[StateInterface::PAGE_SIZE]);
        }

        $this->configuration = $configuration->reveal();
    }

    private function whenWeCallTheEnrichFactoryMethod()
    {
        $this->enrichedState = $this->stateFactory->createStateEnrichedWithDefaults($this->state, $this->configuration);
    }

    private function thenWeExpectTheEnrichedStateToHave(array $expectedStateData)
    {
        $this->assertEquals($expectedStateData, $this->enrichedState->getStateArray());
    }
}
