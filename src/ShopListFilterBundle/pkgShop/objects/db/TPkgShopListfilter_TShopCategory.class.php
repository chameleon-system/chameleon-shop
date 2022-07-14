<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilter_TShopCategory extends TPkgShopListfilter_TShopCategoryAutoParent
{
    /**
     * returns the ID of the Listfilter for that is set for this category or one of its ancestors.
     *
     * @return string
     */
    public function GetFieldPkgShopListfilterIdRecursive()
    {
        /** @var string|null $sFilterId */
        $sFilterId = $this->GetFromInternalCache('sActingFilterForCategory');
        if (null !== $sFilterId) {
            return $sFilterId;
        }

        if (isset($this->sqlData['lft']) && isset($this->sqlData['rgt'])) {
            $sFilterId = $this->findFilterFromNestedSet();
        } else {
            $sFilterId = '';
            if (empty($this->fieldPkgShopListfilterId) && !empty($this->fieldShopCategoryId)) {
                $oParent = $this->GetParent();
                if ($oParent) {
                    $sFilterId = $oParent->GetFieldPkgShopListfilterIdRecursive();
                }
            } else {
                $sFilterId = $this->fieldPkgShopListfilterId;
            }
        }

        $this->SetInternalCache('sActingFilterForCategory', $sFilterId);

        return $sFilterId;
    }

    /**
     * @return string
     */
    private function findFilterFromNestedSet()
    {
        $query = "select pkg_shop_listfilter_id
                        FROM shop_category
                       WHERE lft < :activeLeft
                         AND pkg_shop_listfilter_id != ''
                    ORDER BY lft DESC
                       LIMIT 1
                        ";
        /**
         * @var non-empty-list<string>|false
         * @psalm-suppress UndefinedThisPropertyFetch
         * @FIXME Property `fieldLft` does not exist - maybe `fieldPkgShopListfilterId` is meant?
         */
        $match = $this->getDatabaseConnection()->fetchArray($query, array('activeLeft' => $this->fieldLft));
        if (false === $match) {
            return '';
        }
        $sFilterId = $match[0];

        return $sFilterId;
    }
}
