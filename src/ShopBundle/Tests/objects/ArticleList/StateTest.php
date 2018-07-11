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
    private $stateString = null;
    /**
     * @var \ChameleonSystem\ShopBundle\objects\ArticleList\State
     */
    private $state = null;
    /**
     * @var \ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\StateParameterException
     */
    private $stateException = null;
    private $varyingParameters = null;
    private $queryParameter;

    protected function tearDown()
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
     * @dataProvider dataProviderStateString
     */
    public function it_should_construct_from_state_string($stateString, $expectedStateString)
    {
        $this->givenAStateString($stateString);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenIShouldGetAStateWithThatStateString($expectedStateString);
    }

    public function dataProviderStateString()
    {
        return array(
            array(null, '', null),
            array('', '', null),
            array('p:1,s:sortid,ps:10', 'p:1,s:sortid,ps:10'),
            array('p:0,s:sortid,ps:10', 's:sortid,ps:10'),
            array('p:-1', ''),
            array('p:', ''),
            array('p:invalid', ''),
            array('ps:-1', 'ps:-1'),
            array('ps:', ''),
            array('ps:0', ''),
            array('ps:invalid', ''),
            array('sortid:', ''),
        );
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
     * @dataProvider dataProviderStateString
     */
    public function it_should_set_state_from_string($stateString, $expectedStateString)
    {
        $this->givenAStateString($stateString);
        $this->givenANewStateInstance();
        $this->whenISetTheStateFromString($stateString);
        $this->thenIShouldGetAStateWithThatStateString($expectedStateString);
    }

    private function givenANewStateInstance()
    {
        $this->state = new State();
        $this->state->registerStateElement(new \ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementCurrentPage());
        $this->state->registerStateElement(new \ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementSort());
        $this->state->registerStateElement(new \ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementPageSize(array(null, 5, 10, 15, 20)));
        $this->state->registerStateElement(new \ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementQuery());
    }

    private function whenISetTheStateFromString($stateString)
    {
        $this->state->setStateFromString($stateString);
    }

    /**
     * @test
     * @dataProvider dataProviderStateKeyAndValue
     */
    public function it_should_set_a_state($stateKey, $stateValue, $resultValue, $exception = null)
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

        return array(
            array(StateInterface::SORT, '1234', '1234', null),
            array(StateInterface::SORT, '', null, null),
            array(StateInterface::PAGE, '-1', null, $invalidValueException),
            array(StateInterface::PAGE, '0', null, null),
            array(StateInterface::PAGE, '5', '5', null),
            array(StateInterface::PAGE, 'invalid', null, $invalidValueException),
            array(StateInterface::PAGE_SIZE, '-1', '-1', null),
            array(StateInterface::PAGE_SIZE, '0', null, $invalidValueException),
            array(StateInterface::PAGE_SIZE, '5', '5', null),
            array(StateInterface::PAGE_SIZE, 'invalid', null, $invalidValueException),
            array(StateInterface::QUERY, array('fo' => 'bar'), array('fo' => 'bar'), null),
        );
    }

    private function whenISetTheStateKeyWithTheStateValue($stateKey, $stateValue)
    {
        try {
            $this->state->setState($stateKey, $stateValue);
        } catch (\ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\StateParameterException $e) {
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
     * @dataProvider dataProviderStateArray
     */
    public function it_should_return_a_state_as_array($stateString, $queryParameter, $expectedOutput)
    {
        $this->givenAStateString($stateString);
        $this->givenAQueryParameter($queryParameter);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenTheStateShouldReturnAStateArrayThatMatches($expectedOutput);
    }

    public function dataProviderStateArray()
    {
        return array(
            array('p:1,s:sortid,ps:10', null, array(StateInterface::PAGE => 1, StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10)),
            array('p:0,s:sortid,ps:10', null, array(StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10)),
            array('p:1,s:sortid,ps:10', array('some' => 'data', 'and' => 'somemore'), array(StateInterface::PAGE => 1, StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10, StateInterface::QUERY => array('some' => 'data', 'and' => 'somemore'))),
        );
    }

    /**
     * @test
     * @dataProvider dataProviderStateArrayWithoutQueryParameter
     */
    public function it_should_return_a_state_as_array_without_query_parameter($stateString, $queryParameter, $expectedOutput)
    {
        $this->givenAStateString($stateString);
        $this->givenAQueryParameter($queryParameter);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenTheStateShouldReturnAStateArrayWithoutQueryParameterThatMatches($expectedOutput);
    }

    public function dataProviderStateArrayWithoutQueryParameter()
    {
        return array(
            array('p:1,s:sortid,ps:10', null, array(StateInterface::PAGE => 1, StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10)),
            array('p:0,s:sortid,ps:10', null, array(StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10)),
            array('p:1,s:sortid,ps:10', array('some' => 'data', 'and' => 'somemore'), array(StateInterface::PAGE => 1, StateInterface::SORT => 'sortid', StateInterface::PAGE_SIZE => 10)),
        );
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
     * @dataProvider dataProviderInputOutputStateString
     *
     * @param $inputStateString
     * @param $queryParameter
     * @param $outputStateString
     * @param array $varyingParameters
     */
    public function it_should_return_the_state_as_string($inputStateString, $queryParameter, $outputStateString, array $varyingParameters = null)
    {
        $this->givenAStateString($inputStateString);
        $this->givenAQueryParameter($queryParameter);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->whenIAmVaryingTheParameter($varyingParameters);
        $this->thenIShouldGetAStateWithThatStateString($outputStateString);
    }

    public function dataProviderInputOutputStateString()
    {
        return array(
            array('p:1,s:sortid,ps:10', null, 'p:1,s:sortid,ps:10', null),
            array('p:1,s:sortid,ps:10', null, 's:sortid,ps:10', array(StateInterface::PAGE)),
            array('p:1,s:sortid,ps:10', null, 's:sortid', array(StateInterface::PAGE, StateInterface::PAGE_SIZE)),
            array('p:1,s:sortid,ps:10', array('some' => 'value'), 'p:1,s:sortid,ps:10', null),
        );
    }

    private function whenIAmVaryingTheParameter($varyingParameters)
    {
        $this->varyingParameters = $varyingParameters;
    }

    /**
     * @test
     * @dataProvider dataProviderQueryParameter
     */
    public function it_should_be_able_to_return_the_query_parameter($queryParamSet, $expectedQueryParam)
    {
        $this->givenAQueryParameter($queryParamSet);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenIShouldGetAStateWithThisQueryParameter($expectedQueryParam);
    }

    public function dataProviderQueryParameter()
    {
        return array(
            array(array('some' => 'value'), array('some' => 'value')),
            array(null, array()),
        );
    }

    private function thenIShouldGetAStateWithThisQueryParameter($expectedQueryParameter)
    {
        $this->assertEquals($expectedQueryParameter, $this->state->getQueryParameter());
    }

    /**
     * @test
     * @dataProvider dataProviderUrlQueryOutputTester
     *
     * @param $parameterIdentifier
     * @param $stateString
     * @param $queryData
     * @param $expectedOutput
     */
    public function it_should_generate_an_url_query_ready_parameter_array($parameterIdentifier, $stateString, $queryData, $varyingParameter, $expectedOutput)
    {
        $this->givenAQueryParameter($queryData);
        $this->givenAStateString($stateString);
        $this->whenIConstructTheStateWithTheStateStringAndQueryParameter();
        $this->thenIShouldGetAStateWithUrlQueryParametersMatching($parameterIdentifier, $varyingParameter, $expectedOutput);
    }

    public function dataProviderUrlQueryOutputTester()
    {
        return array(
            array('spota', 'p:1,ps:5,s:sortit', array('some' => 'data'), null, array('spota' => array('str' => 'p:1,ps:5,s:sortit'), 'some' => 'data')),
            array('spota', 'p:1,ps:5,s:sortit', array('some' => array('foo' => 'bar')), null, array('spota' => array('str' => 'p:1,ps:5,s:sortit'), 'some' => array('foo' => 'bar'))),
            array('spota', 'p:1,ps:5,s:sortit', null, null, array('spota' => array('str' => 'p:1,ps:5,s:sortit'))),
            array('spota', 'p:1,ps:5,s:sortit', null, null, array('spota' => array('str' => 'p:1,ps:5,s:sortit'))),
            array('spota', 'p:1,ps:5,s:sortit', array('some' => array('foo' => 'bar')), array('s'), array('spota' => array('str' => 'p:1,ps:5'), 'some' => array('foo' => 'bar'))),
        );
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
     * @dataProvider dataProviderSetUnsetStates
     *
     * @param $inputStateString
     * @param $valuesSet
     * @param $expectedStateValues
     */
    public function it_should_set_values_that_have_no_value($inputStateString, $valuesSet, $expectedStateValues)
    {
        $this->givenANewStateInstance();
        $this->whenISetTheStateFromString($inputStateString);
        $this->when_I_call_setUnsetStatesOnly_with($valuesSet);
        $this->thenTheStateShouldReturnAStateArrayThatMatches($expectedStateValues);
    }

    public function dataProviderSetUnsetStates()
    {
        return array(
            array(
                'p:1,ps:5,s:sortit', // state string
                array('p' => '5'), // default values to set
                array('p' => 1, 'ps' => 5, 's' => 'sortit'), // expected output
            ),
            array(
                'p:1,ps:5,s:sortit', // state string
                array('ps' => '5'), // default values to set
                array('p' => 1, 'ps' => 5, 's' => 'sortit'), // expected output
            ),
            array(
                'p:1,ps:5,s:sortit', // state string
                array('s' => 'newsort'), // default values to set
                array('p' => 1, 'ps' => 5, 's' => 'sortit'), // expected output
            ),

            array(
                'ps:5,s:sortit', // state string
                array('p' => '5'), // default values to set
                array('p' => 5, 'ps' => 5, 's' => 'sortit'), // expected output
            ),
            array(
                'p:1,s:sortit', // state string
                array('ps' => '10'), // default values to set
                array('p' => 1, 'ps' => 10, 's' => 'sortit'), // expected output
            ),
            array(
                'p:1,ps:5', // state string
                array('s' => 'newsort'), // default values to set
                array('p' => 1, 'ps' => 5, 's' => 'newsort'), // expected output
            ),
        );
    }

    private function when_I_call_setUnsetStatesOnly_with($valuesSet)
    {
        $this->state->setUnsetStatesOnly($valuesSet);
    }
}
