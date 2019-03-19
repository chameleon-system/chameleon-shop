<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList;

use ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\StateParameterException;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateElementInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;

class State implements StateInterface
{
    private $stateData = array();
    /**
     * @var \ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateElementInterface[]
     */
    private $stateElement = array();

    public function setStateFromString($stateString)
    {
        $parts = explode(',', $stateString);
        foreach ($parts as $statePartial) {
            $statePartialParts = explode(':', $statePartial);
            if (2 !== count($statePartialParts)) {
                continue;
            }
            try {
                $this->setState($statePartialParts[0], $statePartialParts[1]);
            } catch (StateParameterException $e) {
                // ignore -
            }
        }
    }

    public function setState($name, $value)
    {
        if ('' === $value) {
            if (true === isset($this->stateData[$name])) {
                unset($this->stateData[$name]);
            }

            return;
        }

        if (false === $this->stateElementExists($name)) {
            throw new StateParameterException("invalid state element {$name}. The following elements are registered: ".implode(
                ', ',
                array_keys($this->stateElement)
            ), StateInterface::ERROR_CODE_INVALID_STATE_VALUE);
        }

        if (false === $this->validStateValue($name, $value)) {
            throw new StateParameterException("invalid value {$value} for state parameter {$name}", StateInterface::ERROR_CODE_INVALID_STATE_VALUE);
        }

        $normalizedStateValue = $this->getNormalizedStateValue($name, $value);

        if (null !== $normalizedStateValue) {
            $this->stateData[$name] = $normalizedStateValue;
        } elseif (isset($this->stateData[$name])) {
            unset($this->stateData[$name]);
        }
    }

    /**
     * returns a string representation of the state, excluding the parameter specified by varyingStateParameter.
     *
     * @param array|null $varyingStateParameter
     *
     * @return string
     */
    public function getStateString(array $varyingStateParameter = null)
    {
        $stateInput = $this->getStateArrayWithoutQueryParameter();
        $parts = array();
        foreach ($stateInput as $key => $value) {
            if (null !== $varyingStateParameter && in_array($key, $varyingStateParameter)) {
                continue;
            }
            $parts[] = $key.':'.$value;
        }

        if (0 === count($parts)) {
            return '';
        }

        return implode(',', $parts);
    }

    public function getState($name, $default = null)
    {
        if (false === isset($this->stateData[$name])) {
            return $default;
        }

        return $this->stateData[$name];
    }

    /**
     * does not include query parameter.
     *
     * @return array
     */
    public function getStateArray()
    {
        return $this->stateData;
    }

    public function getStateArrayWithoutQueryParameter()
    {
        $stateData = $this->getStateArray();
        if (isset($stateData[StateInterface::QUERY])) {
            unset($stateData[StateInterface::QUERY]);
        }

        return $stateData;
    }

    public function getStateAsUrlQueryArray($parameterIdentifier, array $varyingStateParameter = null)
    {
        $urlQueryData = array();

        $state = $this->getStateString($varyingStateParameter);
        if ('' !== $state) {
            $urlQueryData[$parameterIdentifier] = array(StateInterface::STATE_STRING => $state);
        }

        return array_merge($urlQueryData, $this->getQueryParameter());
    }

    private function validStateValue($name, $value)
    {
        return $this->stateElement[$name]->validate($value);
    }

    /**
     * @return array
     */
    public function getQueryParameter()
    {
        return $this->getState(StateInterface::QUERY, array());
    }

    public function registerStateElement(StateElementInterface $element)
    {
        $this->stateElement[$element->getKey()] = $element;
    }

    private function stateElementExists($name)
    {
        return isset($this->stateElement[$name]);
    }

    private function getNormalizedStateValue($name, $value)
    {
        return $this->stateElement[$name]->normalize($value);
    }

    /**
     * sets values in stateValues that have no value in the current state. ignores all others.
     *
     * @param array $stateValues
     */
    public function setUnsetStatesOnly(array $stateValues)
    {
        foreach ($stateValues as $key => $value) {
            if (null === $this->getState($key, null)) {
                $this->setState($key, $value);
            }
        }
    }
}
