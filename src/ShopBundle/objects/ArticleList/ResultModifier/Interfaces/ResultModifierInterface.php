<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\ResultModifier\Interfaces;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ResultInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;

interface ResultModifierInterface
{
    /**
     * @param ResultModificationInterface $resultModification
     * @return void
     */
    public function addModification(ResultModificationInterface $resultModification);

    /**
     * @param ResultInterface $result
     * @param array           $configuration
     * @param int             $filterDepth
     *
     * @return ResultInterface
     */
    public function apply(ResultInterface $result, array $configuration, $filterDepth);

    /**
     * @param ResultInterface $result
     * @param array $configuration
     * @param StateInterface $state
     * @return ResultInterface
     */
    public function applyState(ResultInterface $result, array $configuration, StateInterface $state);
}
