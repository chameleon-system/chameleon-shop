<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPrimaryNavi extends TPkgShopPrimaryNaviAutoParent
{
    /**
     * @return AbstractPkgCmsNavigationNode|null
     */
    public function getPkgCmsNavigationNodeObject()
    {
        $oNaviNode = null;
        if (true === empty($this->fieldTarget)) {
            return $oNaviNode;
        }
        switch ($this->GetFieldTargetObjectType()) {
            case 'TdbCmsTree':
                if ($this->fieldShowRootCategoryTree) {
                    $oNaviNode = new TPkgShopPrimaryNavigation_TPkgCmsNavigationNodeWithRootShopCategoriesAsChildren();
                } else {
                    $oNaviNode = new TPkgCmsNavigationNode();
                }
                break;
            case 'TdbShopCategory':
                $oNaviNode = new TPkgShopPrimaryNavigation_TPkgCmsNavigationNode_Category();
                break;
            default:
                break;
        }
        if (null === $oNaviNode) {
            return $oNaviNode;
        }
        if (false === $oNaviNode->load($this->fieldTarget)) {
            return null;
        }

        $oNaviNode->sTitle = $this->fieldName;

        $oNaviNode->sCssClass = $this->fieldCssClass;

        return $oNaviNode;
    }
}
