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
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class StateFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var array
     */
    private $userData;
    /**
     * @var StateInterface
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
     * @var StateInterface
     */
    private $enrichedState;

    /**
     * need to use static method since dataProviders are called before setUpBeforeClass - but they need the factory.
     *
     * @return StateFactory
     */
    public static function getStateFactory()
    {
        $stateFactory = new StateFactory();
        $stateFactory->registerStateElement(new State\StateElementCurrentPage());
        $stateFactory->registerStateElement(new State\StateElementSort());
        $stateFactory->registerStateElement(new State\StateElementPageSize([
                null,
                5,
                10,
                15,
                20,
                21,
            ]));
        $stateFactory->registerStateElement(new State\StateElementQuery());

        return $stateFactory;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateFactory = clone self::getStateFactory();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->userData = null;
        $this->state = null;
        $this->stateFactory = null;
    }

    /**
     * @test
     *
     * @dataProvider dataProviderUserInput
     */
    public function itCreatesStateObjectFromUserInput($userData, $expectedState)
    {
        $this->givenUserData($userData);
        $this->whenWeCallTheFactoryMethod();
        $this->thenWeExpectTheState($expectedState);
    }

    public function dataProviderUserInput()
    {
        $data = [];

        // from state string plus param
        $expectedState = self::getStateFactory()->createState();
        $expectedState->setState(StateInterface::PAGE, 4);
        $expectedState->setState(StateInterface::PAGE_SIZE, 20);
        $stateString = $expectedState->getStateString([StateInterface::PAGE]);
        $userData = [StateInterface::STATE_STRING => $stateString, StateInterface::PAGE => 4];
        $data[] = [$userData, $expectedState];

        // from parameter array
        $param = [
            StateInterface::PAGE => 5,
            StateInterface::PAGE_SIZE => 21,
            StateInterface::SORT => 'somesortid',
        ];
        $expectedState = self::getStateFactory()->createState();
        $expectedState->setState(StateInterface::PAGE, 5);
        $expectedState->setState(StateInterface::PAGE_SIZE, 21);
        $expectedState->setState(StateInterface::SORT, 'somesortid');
        $data[] = [$param, $expectedState];

        // nothing
        $expectedState = self::getStateFactory()->createState();
        $data[] = [[], $expectedState];
        $data[] = [null, $expectedState];

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
     *
     * @dataProvider dataProviderEnrichedStates
     */
    public function itShouldCreateAStateBasedOnAnotherStateEnrichedByDefaultValues(StateInterface $state, array $defaultStateValues, array $expectedStateData)
    {
        $this->givenAState($state);
        $this->givenDefaultStateValues($defaultStateValues);
        $this->whenWeCallTheEnrichFactoryMethod();
        $this->thenWeExpectTheEnrichedStateToHave($expectedStateData);
    }

    public function dataProviderEnrichedStates()
    {
        $data = [];

        $state = self::getStateFactory()->createState();
        $defaultStateValues = [
            StateInterface::PAGE_SIZE => 10,
            StateInterface::SORT => 'somesortid',
        ];
        $data[] = [$state, $defaultStateValues, $defaultStateValues];

        $state = self::getStateFactory()->createState();
        $state->setState(StateInterface::SORT, 'newsortid');
        $state->setState(StateInterface::QUERY, 'somequery');
        $expectedResult = $defaultStateValues;
        $expectedResult[StateInterface::SORT] = 'newsortid';
        $expectedResult[StateInterface::QUERY] = 'somequery';
        $data[] = [$state, $defaultStateValues, $expectedResult];

        $state = self::getStateFactory()->createState();
        $state->setState(StateInterface::PAGE_SIZE, 20);
        $expectedResult = $defaultStateValues;
        $expectedResult[StateInterface::PAGE_SIZE] = 20;
        $data[] = [$state, $defaultStateValues, $expectedResult];

        $state = self::getStateFactory()->createState();
        $state->setState(StateInterface::PAGE, 2);
        $expectedResult = $defaultStateValues;
        $expectedResult[StateInterface::PAGE] = 2;
        $data[] = [$state, $defaultStateValues, $expectedResult];

        $state = self::getStateFactory()->createState();
        $state->setState(StateInterface::SORT, 'newsortid');
        $expectedResult = $defaultStateValues;
        $expectedResult[StateInterface::SORT] = 'newsortid';
        $data[] = [$state, $defaultStateValues, $expectedResult];

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
