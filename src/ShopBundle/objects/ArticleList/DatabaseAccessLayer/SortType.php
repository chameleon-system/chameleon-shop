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
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Service\PortalDomainService;
use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;
use ChameleonSystem\CoreBundle\Util\FieldTranslationUtil;

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

        $parts = array();
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
        if(str_contains($sortString, self::NAME_SORT_STRING)) {
            return self::getFieldTranslationUtil()->getTranslatedFieldName('shop_article', 'name');
        }

        return $sortString;
    }

    private function getPortalDomainService(): PortalDomainService
    {
        return ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    protected static function getLanguageService(): LanguageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.language_service');
    }

    protected static function getFieldTranslationUtil(): FieldTranslationUtil
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.field_translation');
    }
}
