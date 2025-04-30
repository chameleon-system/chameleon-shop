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

use ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\StateParameterException;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\State;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    private $stateString;
    /**
     * @var State
     */
    private $state;
    /**
     * @var StateParameterException
     */
    private $stateException;
    private $varyingParameters;
    private $queryParameter;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->stateString = null;
        $this->state = null;
        $this->stateException = null;
        $this->varyingParameters = null;
        $this->queryParameter = null;
    }

    /**
     * @test
     *
     * @dataProvider dataProviderStateString
     */
    public function itShouldConstructFromStateString($stateString, $expectedStateString)
    {
        $this->givenAStateString($stateString);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenIShouldGetAStateWithThatStateString($expectedStateString);
    }

    public function dataProviderStateString()
    {
        return [
            [null, '', null],
            ['', '', null],
            ['p:1,s:sortid,ps:10', 'p:1,s:sortid,ps:10'],
            ['p:0,s:sortid,ps:10', 's:sortid,ps:10'],
            ['p:-1', ''],
            ['p:', ''],
            ['p:invalid', ''],
            ['ps:-1', 'ps:-1'],
            ['ps:', ''],
            ['ps:0', ''],
            ['ps:invalid', ''],
            ['sortid:', ''],
        ];
    }

    private function givenAStateString($stateString)
    {
        $this->stateString = $stateString;
    }

    private function whenIConstructTheStateWithTheStateStringAndQueryParameter()
    {
        $this->givenANewStateInstance();
        try {
            $this->state->setStateFromString($this->stateString);
            if (null !== $this->queryParameter) {
                $this->state->setState(StateInterface::QUERY, $this->queryParameter);
            }
        } catch (\Exception $e) {
            $this->stateException = $e;
        }
    }

    private function thenIShouldGetAStateWithThatStateString($expectedStateString)
    {
        $this->assertEquals($expectedStateString, $this->state->getStateString($this->varyingParameters));
    }

    /**
     * @test
     *
     * @dataProvider dataProviderStateString
     */
    public function itShouldSetStateFromString($stateString, $expectedStateString)
    {
        $this->givenAStateString($stateString);
        $this->givenANewStateInstance();
        $this->whenISetTheStateFromString($stateString);
        $this->thenIShouldGetAStateWithThatStateString($expectedStateString);
    }

    private function givenANewStateInstance()
    {
        $this->state = new State();
        $this->state->registerStateElement(new State\StateElementCurrentPage());
        $this->state->registerStateElement(new State\StateElementSort());
        $this->state->registerStateElement(new State\StateElementPageSize([null, 5, 10, 15, 20]));
        $this->state->registerStateElement(new State\StateElementQuery());
    }

    private function whenISetTheStateFromString($stateString)
    {
        $this->state->setStateFromString($stateString);
    }

    /**
     * @test
     *
     * @dataProvider dataProviderStateKeyAndValue
     */
    public function itShouldSetAState($stateKey, $stateValue, $resultValue, $exception = null)
    {
        $this->givenANewStateInstance();
        $this->whenISetTheStateKeyWithTheStateValue($stateKey, $stateValue);
        if (null === $exception) {
            $this->thenTheStateShouldHaveTheStateValueForTheStateKey($stateKey, $resultValue);
        } else {
            $this->thenAnExceptionShouldHaveBeenThrown($exception);
            $this->thenTheStateValueForTheStateKeyShouldNotBeSet($stateKey);
        }
    }

    public function dataProviderStateKeyAndValue()
    {
        $invalidValueException = new StateParameterException('', StateInterface::ERROR_CODE_INVALID_STATE_VALUE);

        return [
            [StateInterface::SORT, '1234', '1234', null],
            [StateInterface::SORT, '', null, null],
            [StateInterface::PAGE, '-1', null, $invalidValueException],
            [StateInterface::PAGE, '0', null, null],
            [StateInterface::PAGE, '5', '5', null],
            [StateInterface::PAGE, 'invalid', null, $invalidValueException],
            [StateInterface::PAGE_SIZE, '-1', '-1', null],
            [StateInterface::PAGE_SIZE, '0', null, $invalidValueException],
            [StateInterface::PAGE_SIZE, '5', '5', null],
            [StateInterface::PAGE_SIZE, 'invalid', null, $invalidValueException],
            [StateInterface::QUERY, ['fo' => 'bar'], ['fo' => 'bar'], null],
        ];
    }

    private function whenISetTheStateKeyWithTheStateValue($stateKey, $stateValue)
    {
        try {
            $this->state->setState($stateKey, $stateValue);
        } catch (StateParameterException $e) {
            $this->stateException = $e;
        }
    }

    private function thenTheStateShouldHaveTheStateValueForTheStateKey($stateKey, $stateValue)
    {
        $this->assertEquals($stateValue, $this->state->getState($stateKey));
    }

    private function thenAnExceptionShouldHaveBeenThrown(StateParameterException $exception)
    {
        $this->assertInstanceOf('\ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\StateParameterException', $exception);
    }

    private function thenTheStateValueForTheStateKeyShouldNotBeSet($stateKey)
    {
        $this->assertNull($this->state->getState($stateKey, null), "the state key {$stateKey} should not be set");
    }

    /**
     * @test
     *
     * @dataProvider dataProviderStateArray
     */
    public function itShouldReturnAStateAsArray($stateString, $queryParameter, $expectedOutput)
    {
        $this->givenAStateString($stateString);
        $this->givenAQueryParameter($queryParameter);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenTheStateShouldReturnAStateArrayThatMatches($expectedOutput);
    }

    public function dataProviderStateArray()
    {
        return [
            ['p:1,s:sortid,ps:10', null, [StateInterface::PAGE => 1, StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10]],
            ['p:0,s:sortid,ps:10', null, [StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10]],
            ['p:1,s:sortid,ps:10', ['some' => 'data', 'and' => 'somemore'], [StateInterface::PAGE => 1, StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10, StateInterface::QUERY => ['some' => 'data', 'and' => 'somemore']]],
        ];
    }

    /**
     * @test
     *
     * @dataProvider dataProviderStateArrayWithoutQueryParameter
     */
    public function itShouldReturnAStateAsArrayWithoutQueryParameter($stateString, $queryParameter, $expectedOutput)
    {
        $this->givenAStateString($stateString);
        $this->givenAQueryParameter($queryParameter);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenTheStateShouldReturnAStateArrayWithoutQueryParameterThatMatches($expectedOutput);
    }

    public function dataProviderStateArrayWithoutQueryParameter()
    {
        return [
            ['p:1,s:sortid,ps:10', null, [StateInterface::PAGE => 1, StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10]],
            ['p:0,s:sortid,ps:10', null, [StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10]],
            ['p:1,s:sortid,ps:10', ['some' => 'data', 'and' => 'somemore'], [StateInterface::PAGE => 1, StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10]],
        ];
    }

    private function givenAQueryParameter($queryParameter)
    {
        $this->queryParameter = $queryParameter;
    }

    private function thenTheStateShouldReturnAStateArrayThatMatches($expectedOutput)
    {
        $this->assertEquals($expectedOutput, $this->state->getStateArray());
    }

    private function thenTheStateShouldReturnAStateArrayWithoutQueryParameterThatMatches($expectedOutput)
    {
        $this->assertEquals($expectedOutput, $this->state->getStateArrayWithoutQueryParameter());
    }

    /**
     * @test
     *
     * @dataProvider dataProviderInputOutputStateString
     */
    public function itShouldReturnTheStateAsString($inputStateString, $queryParameter, $outputStateString, ?array $varyingParameters = null)
    {
        $this->givenAStateString($inputStateString);
        $this->givenAQueryParameter($queryParameter);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->whenIAmVaryingTheParameter($varyingParameters);
        $this->thenIShouldGetAStateWithThatStateString($outputStateString);
    }

    public function dataProviderInputOutputStateString()
    {
        return [
            ['p:1,s:sortid,ps:10', null, 'p:1,s:sortid,ps:10', null],
            ['p:1,s:sortid,ps:10', null, 's:sortid,ps:10', [StateInterface::PAGE]],
            ['p:1,s:sortid,ps:10', null, 's:sortid', [StateInterface::PAGE, StateInterface::PAGE_SIZE]],
            ['p:1,s:sortid,ps:10', ['some' => 'value'], 'p:1,s:sortid,ps:10', null],
        ];
    }

    private function whenIAmVaryingTheParameter($varyingParameters)
    {
        $this->varyingParameters = $varyingParameters;
    }

    /**
     * @test
     *
     * @dataProvider dataProviderQueryParameter
     */
    public function itShouldBeAbleToReturnTheQueryParameter($queryParamSet, $expectedQueryParam)
    {
        $this->givenAQueryParameter($queryParamSet);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenIShouldGetAStateWithThisQueryParameter($expectedQueryParam);
    }

    public function dataProviderQueryParameter()
    {
        return [
            [['some' => 'value'], ['some' => 'value']],
            [null, []],
        ];
    }

    private function thenIShouldGetAStateWithThisQueryParameter($expectedQueryParameter)
    {
        $this->assertEquals($expectedQueryParameter, $this->state->getQueryParameter());
    }

    /**
     * @test
     *
     * @dataProvider dataProviderUrlQueryOutputTester
     */
    public function itShouldGenerateAnUrlQueryReadyParameterArray($parameterIdentifier, $stateString, $queryData, $varyingParameter, $expectedOutput)
    {
        $this->givenAQueryParameter($queryData);
        $this->givenAStateString($stateString);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenIShouldGetAStateWithUrlQueryParametersMatching($parameterIdentifier, $varyingParameter, $expectedOutput);
    }

    public function dataProviderUrlQueryOutputTester()
    {
        return [
            ['spota', 'p:1,ps:5,s:sortit', ['some' => 'data'], null, ['spota' => ['str' => 'p:1,ps:5,s:sortit'], 'some' => 'data']],
            ['spota', 'p:1,ps:5,s:sortit', ['some' => ['foo' => 'bar']], null, ['spota' => ['str' => 'p:1,ps:5,s:sortit'], 'some' => ['foo' => 'bar']]],
            ['spota', 'p:1,ps:5,s:sortit', null, null, ['spota' => ['str' => 'p:1,ps:5,s:sortit']]],
            ['spota', 'p:1,ps:5,s:sortit', null, null, ['spota' => ['str' => 'p:1,ps:5,s:sortit']]],
            ['spota', 'p:1,ps:5,s:sortit', ['some' => ['foo' => 'bar']], ['s'], ['spota' => ['str' => 'p:1,ps:5'], 'some' => ['foo' => 'bar']]],
        ];
    }

    private function thenIShouldGetAStateWithUrlQueryParametersMatching($parameterIdentifier, $varyingParameter, $expectedOutput)
    {
        $this->assertEquals($expectedOutput, $this->state->getStateAsUrlQueryArray($parameterIdentifier, $varyingParameter));
    }

    private function thenIExpectExceptionOfType($exceptionType)
    {
        if (null === $exceptionType) {
            $this->assertNull($this->stateException, 'we did not expect an exception!');
        } else {
            $this->assertInstanceOf($exceptionType, $this->stateException);
        }
    }

    /**
     * @test
     *
     * @dataProvider dataProviderSetUnsetStates
     */
    public function itShouldSetValuesThatHaveNoValue($inputStateString, $valuesSet, $expectedStateValues)
    {
        $this->givenANewStateInstance();
        $this->whenISetTheStateFromString($inputStateString);
        $this->when_I_call_setUnsetStatesOnly_with($valuesSet);
        $this->thenTheStateShouldReturnAStateArrayThatMatches($expectedStateValues);
    }

    public function dataProviderSetUnsetStates()
    {
        return [
            [
                'p:1,ps:5,s:sortit', // state string
                ['p' => '5'], // default values to set
                ['p' => 1, 'ps' => 5, 's' => 'sortit'], // expected output
            ],
            [
                'p:1,ps:5,s:sortit', // state string
                ['ps' => '5'], // default values to set
                ['p' => 1, 'ps' => 5, 's' => 'sortit'], // expected output
            ],
            [
                'p:1,ps:5,s:sortit', // state string
                ['s' => 'newsort'], // default values to set
                ['p' => 1, 'ps' => 5, 's' => 'sortit'], // expected output
            ],

            [
                'ps:5,s:sortit', // state string
                ['p' => '5'], // default values to set
                ['p' => 5, 'ps' => 5, 's' => 'sortit'], // expected output
            ],
            [
                'p:1,s:sortit', // state string
                ['ps' => '10'], // default values to set
                ['p' => 1, 'ps' => 10, 's' => 'sortit'], // expected output
            ],
            [
                'p:1,ps:5', // state string
                ['s' => 'newsort'], // default values to set
                ['p' => 1, 'ps' => 5, 's' => 'newsort'], // expected output
            ],
        ];
    }

    private function when_I_call_setUnsetStatesOnly_with($valuesSet)
    {
        $this->state->setUnsetStatesOnly($valuesSet);
    }
}
