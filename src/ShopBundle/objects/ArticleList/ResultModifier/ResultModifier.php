<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\ResultModifier;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ResultInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\ResultModifier\Interfaces\ResultModificationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\ResultModifier\Interfaces\ResultModifierInterface;

class ResultModifier implements ResultModifierInterface
{
    /**
     * @var ResultModificationInterface[]
     */
    private $modifications = array();

    public function addModification(ResultModificationInterface $resultModification)
    {
        $this->modifications[] = $resultModification;
    }

    /**
     * @param ResultInterface $result
     * @param array           $configuration
     * @param int             $filterDepth
     *
     * @return ResultInterface
     */
    public function apply(ResultInterface $result, array $configuration, $filterDepth)
    {
        foreach ($this->modifications as $modification) {
            $result = $modification->apply($result, $configuration, $filterDepth);
        }

        reset($this->modifications);

        return $result;
    }

    public function applyState(ResultInterface $result, array $configuration, StateInterface $state)
    {
        foreach ($this->modifications as $modification) {
            $result = $modification->applyState($result, $configuration, $state);
        }

        reset($this->modifications);

        return $result;
    }
}
