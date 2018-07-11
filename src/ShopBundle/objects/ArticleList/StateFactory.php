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

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\StateParameterException;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateElementInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;

class StateFactory implements StateFactoryInterface
{
    /**
     * @var StateElementInterface[]
     */
    private $stateElements = array();

    /**
     * {@inheritdoc}
     */
    public function registerStateElement(StateElementInterface $stateElement)
    {
        $this->stateElements[$stateElement->getKey()] = $stateElement;
    }

    /**
     * {@inheritdoc}
     */
    public function createState(array $userData = null)
    {
        $state = $this->createStateObject();

        if (null === $userData) {
            return $state;
        }

        if (isset($userData[StateInterface::STATE_STRING])) {
            $state->setStateFromString($userData[StateInterface::STATE_STRING]);
            unset($userData[StateInterface::STATE_STRING]);
        }

        foreach ($userData as $key => $value) {
            try {
                $state->setState($key, $value);
            } catch (StateParameterException $e) {
                trigger_error('state Parameter Exception: '.(string) $e, E_USER_WARNING);
            }
        }

        return $state;
    }

    /**
     * {@inheritdoc}
     */
    public function createStateEnrichedWithDefaults(StateInterface $state, ConfigurationInterface $configuration)
    {
        $defaultValues = array(
            StateInterface::PAGE_SIZE => $configuration->getDefaultPageSize(),
            StateInterface::SORT => $configuration->getDefaultSortId(),
        );
        $enrichedState = clone $state;
        $enrichedState->setUnsetStatesOnly($defaultValues);

        return $enrichedState;
    }

    /**
     * @return \ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface
     */
    private function createStateObject()
    {
        $state = new State();
        foreach ($this->stateElements as $stateElement) {
            $state->registerStateElement($stateElement);
        }
        reset($this->stateElements);

        return $state;
    }
}
