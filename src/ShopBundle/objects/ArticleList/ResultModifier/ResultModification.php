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

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\DbAdapterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ResultInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\ResultModifier\Interfaces\ResultModificationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\SortString;

class ResultModification implements ResultModificationInterface
{
    /**
     * @var DbAdapterInterface
     */
    private $dbAdapter;

    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    /**
     * @param int $filterDepth
     *
     * @return ResultInterface
     */
    public function apply(ResultInterface $result, array $configuration, $filterDepth)
    {
        return $result;
    }

    /**
     * @return ResultInterface
     */
    public function applyState(ResultInterface $result, array $configuration, StateInterface $state)
    {
        $result->setPageSize($state->getState(StateInterface::PAGE_SIZE));

        $result->setPage($state->getState(StateInterface::PAGE, 0));

        $result = $this->applySortStateToResult($result, $state->getState(StateInterface::SORT));

        return $result;
    }

    /**
     * @param string $activeSortId
     *
     * @return ResultInterface
     */
    private function applySortStateToResult(ResultInterface $results, $activeSortId)
    {
        $sort = $this->dbAdapter->getSortTypeFromId($activeSortId);
        $sortString = new SortString($sort->getSortString());
        $results->setSort($sortString->getAsArray());

        return $results;
    }
}
