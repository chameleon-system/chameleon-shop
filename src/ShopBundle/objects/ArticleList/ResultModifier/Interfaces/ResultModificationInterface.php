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

interface ResultModificationInterface
{
    public const CONFIG_MODULE_INSTANCE_ID = 'cms_tpl_module_instance_id';

    /**
     * @param int $filterDepth
     *
     * @return ResultInterface
     */
    public function apply(ResultInterface $result, array $configuration, $filterDepth);

    /**
     * @return ResultInterface
     */
    public function applyState(ResultInterface $result, array $configuration, StateInterface $state);
}
