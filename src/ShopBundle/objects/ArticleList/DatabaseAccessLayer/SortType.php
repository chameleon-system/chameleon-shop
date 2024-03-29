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

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\FieldTranslationUtil;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\SortTypeInterface;

class SortType extends \ChameleonSystemShopBundleobjectsArticleListDatabaseAccessLayerSortTypeAutoParent implements SortTypeInterface
{
    private const NAME_SORT_STRING = '`shop_article`.`name`';

    public function getSortString()
    {
        $sortString = trim($this->fieldSqlOrderBy);
        if ('' !== $sortString) {
            $sortString .= ' '.trim($this->fieldOrderDirection);
        }

        $sortString = $this->getMultilingualSortString($sortString);

        $secondarySortString = trim($this->fieldSqlSecondaryOrderByString);

        $parts = [];
        if ('' !== $sortString) {
            $parts[] = $sortString;
        }
        if ('' !== $secondarySortString) {
            $parts[] = $secondarySortString;
        }

        return implode(', ', $parts);
    }

    private function getMultilingualSortString(string $sortString): string
    {
        if (str_contains($sortString, self::NAME_SORT_STRING)) {
            return $this->getFieldTranslationUtil()->getTranslatedFieldName('shop_article', 'name');
        }

        return $sortString;
    }

    protected function getFieldTranslationUtil(): FieldTranslationUtil
    {
        return ServiceLocator::get('chameleon_system_core.util.field_translation');
    }
}
