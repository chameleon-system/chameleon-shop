<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces;

interface StateInterface
{
    const ERROR_CODE_INVALID_STATE_VALUE = 1;
    const PAGE = 'p';
    const SORT = 's';
    const PAGE_SIZE = 'ps';
    const STATE_STRING = 'str';
    const QUERY = 'q';

    public function registerStateElement(StateElementInterface $element);

    public function setStateFromString($stateString);

    public function setState($name, $value);

    /**
     * returns a string representation of the state, excluding the parameter specified by varyingStateParameter.
     *
     * @param null|array $varyingStateParameter
     *
     * @return string
     */
    public function getStateString(array $varyingStateParameter = null);

    public function getState($name, $default = null);

    public function getStateArray();

    public function getStateArrayWithoutQueryParameter();

    /**
     * returns an array that can be used to generate the query part of an URL identifying the state including the additional query parameters held within the state.
     *
     * @param $parameterIdentifier
     * @param array $varyingStateParameter
     *
     * @return array
     */
    public function getStateAsUrlQueryArray($parameterIdentifier, array $varyingStateParameter = null);

    /**
     * @return array
     */
    public function getQueryParameter();

    /**
     * sets values in stateValues that have no value in the current state. ignores all others.
     *
     * @param array $stateValues
     */
    public function setUnsetStatesOnly(array $stateValues);
}
