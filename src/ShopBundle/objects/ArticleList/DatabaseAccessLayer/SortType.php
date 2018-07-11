<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\SortTypeInterface;

class SortType extends \ChameleonSystemShopBundleobjectsArticleListDatabaseAccessLayerSortTypeAutoParent implements SortTypeInterface
{
    public function getSortString()
    {
        $sortString = trim($this->fieldSqlOrderBy);
        if ('' !== $sortString) {
            $sortString .= ' '.trim($this->fieldOrderDirection);
        }
        $secondarySortString = trim($this->fieldSqlSecondaryOrderByString);
        $parts = array();
        if ('' !== $sortString) {
            $parts[] = $sortString;
        }
        if ('' !== $secondarySortString) {
            $parts[] = $secondarySortString;
        }

        return implode(', ', $parts);
    }
}
