<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPrimaryNavigation_TPkgCmsNavigationNodeWithRootShopCategoriesAsChildren extends TPkgCmsNavigationNode
{
    /**
     * @return AbstractPkgCmsNavigationNode[]|null
     */
    public function getAChildren()
    {
        if (true === $this->bDisableSubmenu) {
            return null;
        }

        if (null === $this->aChildren) {
            $this->aChildren = array();
            $oRootCategories = TdbShopCategoryList::GetRootCategoryList();
            $oRootCategories->GoToStart();
            while ($oRootCategory = $oRootCategories->Next()) {
                if (false === $oRootCategory->AllowDisplayInShop()) {
                    continue;
                }
                /** @var AbstractPkgCmsNavigationNode $oNaviNode */
                $oNaviNode = new TPkgShopPrimaryNavigation_TPkgCmsNavigationNode_Category();
                $oNaviNode->iLevel = $this->iLevel + 1;
                if (true === $oNaviNode->loadFromNode($oRootCategory)) {
                    $this->aChildren[] = $oNaviNode;
                }
            }
        }

        return $this->aChildren;
    }
}
