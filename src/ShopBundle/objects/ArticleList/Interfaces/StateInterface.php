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
    public const ERROR_CODE_INVALID_STATE_VALUE = 1;
    public const PAGE = 'p';
    public const SORT = 's';
    public const PAGE_SIZE = 'ps';
    public const STATE_STRING = 'str';
    public const QUERY = 'q';

    /**
     * @return void
     */
    public function registerStateElement(StateElementInterface $element);

    /**
     * @param string $stateString
     *
     * @return void
     */
    public function setStateFromString($stateString);

    /**
     * @param string $name
     *
     * @return void
     */
    public function setState($name, $value);

    /**
     * returns a string representation of the state, excluding the parameter specified by varyingStateParameter.
     *
     * @param array<string, mixed>|null $varyingStateParameter
     *
     * @return string
     */
    public function getStateString(?array $varyingStateParameter = null);

    /**
     * @param string $name
     */
    public function getState($name, $default = null);

    /**
     * @return array<string, mixed>
     */
    public function getStateArray();

    /**
     * @return array<string, mixed>
     */
    public function getStateArrayWithoutQueryParameter();

    /**
     * returns an array that can be used to generate the query part of an URL identifying the state including the additional query parameters held within the state.
     *
     * @param string $parameterIdentifier
     *
     * @return array<string, mixed>
     */
    public function getStateAsUrlQueryArray($parameterIdentifier, ?array $varyingStateParameter = null);

    /**
     * @return array<string, mixed>
     */
    public function getQueryParameter();

    /**
     * sets values in stateValues that have no value in the current state. ignores all others.
     *
     * @param array<string, mixed> $stateValues
     *
     * @return void
     */
    public function setUnsetStatesOnly(array $stateValues);
}
