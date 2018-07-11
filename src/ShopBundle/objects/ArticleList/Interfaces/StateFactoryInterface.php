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

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;

interface StateFactoryInterface
{
    /**
     * @param StateElementInterface $stateElement
     */
    public function registerStateElement(StateElementInterface $stateElement);

    /**
     * @param array $userData
     *
     * @return StateInterface
     */
    public function createState(array $userData = null);

    /**
     * @param StateInterface         $state
     * @param ConfigurationInterface $configuration
     *
     * @return StateInterface
     */
    public function createStateEnrichedWithDefaults(StateInterface $state, ConfigurationInterface $configuration);
}
