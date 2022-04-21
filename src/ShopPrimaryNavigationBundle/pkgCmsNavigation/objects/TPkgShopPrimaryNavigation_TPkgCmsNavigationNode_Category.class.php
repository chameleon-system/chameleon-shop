<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopCategoryDataAccessInterface;

/**
 * @extends AbstractPkgCmsNavigationNode<TdbShopCategory>
 */
class TPkgShopPrimaryNavigation_TPkgCmsNavigationNode_Category extends AbstractPkgCmsNavigationNode
{
    /**
     * @param string $sId - shop category id
     *
     * @return bool
     */
    public function load($sId)
    {
        $category = TdbShopCategory::GetNewInstance($sId);
        if (false === $category->AllowDisplayInShop() || false == $category->fieldActive) {
            return false;
        }

        return $this->loadFromNode($category);
    }

    /**
     * @param TdbShopCategory $oNode
     *
     * @return bool
     */
    public function loadFromNode($oNode)
    {
        if (false === $oNode->fieldActive) {
            return false;
        }
        $this->setNodeCopy($oNode);
        $this->setFromShopCategory($oNode);

        return true;
    }

    /**
     * @return AbstractPkgCmsNavigationNode[]|null
     */
    public function getAChildren()
    {
        if (true === $this->bDisableSubmenu) {
            return null;
        }
        if (null === $this->aChildren) {
            if (null !== $this->getNodeCopy()) {
                $this->aChildren = array();
                /** @var $oNode TdbShopCategory */
                $oNode = $this->getNodeCopy();
                $language = $oNode->GetLanguage();

                $categoryDataAccess = $this->getShopCategoryDataAccess();
                $childrenRows = $categoryDataAccess->getActiveChildren($oNode->id);

                /** @var TdbShopCategory[] $categoryList */
                $categoryList = array_map(
                    function ($child) use ($language) {
                        return TdbShopCategory::GetNewInstance($child, $language);
                    }, $childrenRows);

                foreach ($categoryList as $oChild) {
                    if (false === $oChild->AllowDisplayInShop()) {
                        continue;
                    }
                    $sClass = get_class($this);
                    /** @var AbstractPkgCmsNavigationNode $oNaviNode */
                    $oNaviNode = new $sClass();
                    $oNaviNode->iLevel = $this->iLevel + 1;
                    if (true === $oNaviNode->loadFromNode($oChild)) {
                        $this->aChildren[] = $oNaviNode;
                    }
                }
            }
        }

        return $this->aChildren;
    }

    private function setFromShopCategory(TdbShopCategory $oNode)
    {
        $this->sLink = $oNode->GetLink();
        $this->sTitle = $oNode->GetName();
        $this->sSeoTitle = $oNode->GetBreadcrumbName();
        $this->sNavigationIconURL = $this->getNodeIconURL();
    }

    public function getBIsActive()
    {
        if (null === $this->bIsActive) {
            $this->bIsActive = false;
            /** @var $oNode TdbShopCategory */
            $oNode = $this->getNodeCopy();
            $oActiveCategory = TdbShop::GetInstance()->GetActiveCategory();
            if ($oActiveCategory && $oNode && $oActiveCategory->id === $oNode->id) {
                $this->bIsActive = true;
            }
        }

        return $this->bIsActive;
    }

    public function getBIsExpanded()
    {
        if (null === $this->bIsExpanded) {
            $this->bIsExpanded = $this->getBIsActive();
            if (false === $this->bIsExpanded) {
                /** @var $oNode TdbShopCategory */
                $oNode = $this->getNodeCopy();
                $aCategoryPath = TdbShop::GetInstance()->GetActiveCategoryPath();
                if ($oNode && is_array($aCategoryPath) && isset($aCategoryPath[$oNode->id])) {
                    $this->bIsExpanded = true;
                }
            }
        }

        return $this->bIsExpanded;
    }

    /**
     * returns the url to an icon for the node - if set.
     *
     * @return string|null
     */
    public function getNodeIconURL()
    {
        $sURL = null;
        /** @var $oNode TdbShopCategory */
        $oNode = $this->getNodeCopy();
        $oImage = $oNode->GetImage(0, 'navi_icon_cms_media_id', $this->dummyImagesAllowed());
        if ($oImage) {
            $sURL = $oImage->GetRelativeURL();
            $this->sNavigationIconId = $oImage->id;
        }

        return $sURL;
    }

    /**
     * @return ShopCategoryDataAccessInterface
     */
    protected function getShopCategoryDataAccess()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_category_data_access');
    }
}
