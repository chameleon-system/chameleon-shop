<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

class TShopCategoryTree
{
    /**
     * Contains all child categories as TShopCategoryTree.
     *
     * @var TIterator
     */
    protected $oChildCategoryTreeList = null;

    /**
     * Contains the real category for the tree.
     *
     * @var TdbShopCategory
     */
    protected $oRealCategory = null;

    public $bIsActive = false;

    /**
     * Contains the item count for this category tree.
     *
     * @var int
     */
    public $iItemCount = 0;

    /**
     * Contains item count for this category tree and for all child category trees.
     *
     * @var int
     */
    public $iAllItemCount = 0;

    /**
     * Contains the id of the real category if exists.
     * If no real category exits for the category tree then it contains random id.
     *
     * @var string
     */
    public $id = false;

    /**
     * Contains a list with all child category ids.
     *
     * @var array
     */
    public $aContainingChildCategories = array();

    /**
     * Returns Category tree from given master category id.
     *
     * @param string $sChildCategoryId
     *
     * @return TShopCategoryTree
     */
    public static function GetCategoryTree($sChildCategoryId = '')
    {
        $oTree = new self();
        $oTree->LoadChildTreeExecute($sChildCategoryId);

        return $oTree;
    }

    /**
     * Loads Category tree from given master category id.
     *
     * @param  $sChildCategoryId
     */
    protected function LoadChildTreeExecute($sChildCategoryId)
    {
        $sSelect = $this->GetQuery($sChildCategoryId);
        $oChildCategoryList = TdbShopCategoryList::GetList($sSelect);
        if ($oChildCategoryList->Length() > 1) {
            $this->oChildCategoryTreeList = new TIterator();
        }
        while ($oChildCategory = $oChildCategoryList->Next()) {
            if ($oChildCategory->id == $sChildCategoryId) {
                $this->oRealCategory = $oChildCategory;
                $this->iItemCount = 0;
                $this->iAllItemCount = 0;
                $this->id = $oChildCategory->id;
            } else {
                $oMainTree = new self();
                $oMainTree->LoadChildTreeExecute($oChildCategory->id);
                $this->aContainingChildCategories = array_merge($this->aContainingChildCategories, $oMainTree->aContainingChildCategories);
                $this->iAllItemCount = $this->iAllItemCount + $oMainTree->iAllItemCount;
                $this->oChildCategoryTreeList->AddItem($oMainTree);
                $this->aContainingChildCategories[$oChildCategory->id] = $oChildCategory->GetName();
            }
        }
        if (empty($this->id)) {
            $this->id = TTools::GetUUID();
        }
    }

    /**
     * Returns query to get all child categories for given category id.
     * If given category id is empty then returns query to get master categories.
     *
     * @param  $sChildCategoryId
     *
     * @return string
     */
    protected function GetQuery($sChildCategoryId)
    {
        $sSelect = "SELECT `shop_category`.*
                    FROM `shop_category`
                   WHERE `shop_category`.`shop_category_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sChildCategoryId)."'
      OR `shop_category`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sChildCategoryId)."'
                ORDER BY `shop_category`.`position`";

        return $sSelect;
    }

    /**
     * Resets all item count in all category trees.
     */
    public function ResetCounter()
    {
        $this->iAllItemCount = 0;
        $this->iItemCount = 0;
        if (!is_null($this->oChildCategoryTreeList)) {
            $this->oChildCategoryTreeList->GoToStart();
            while ($oChildCategoryTree = $this->oChildCategoryTreeList->Next()) {
                $oChildCategoryTree->ResetCounter();
            }
        }
    }

    /**
     * Renders the category tree.
     * Function not uses external views. Html was rendered in class for better performance.
     * If you want to change something on the html overwrite one of the render functions.
     *
     * @param bool $sListFilterItemId
     * @param bool $bHideEmptyCategories
     * @param bool $bShowArticleCount
     * @param int  $iLevelCount
     *
     * @return string
     */
    public function Render($sListFilterItemId = false, $bHideEmptyCategories = false, $bShowArticleCount = false, $iLevelCount = 1)
    {
        $sHtml = '';
        $sCategoryNameHtml = '';
        if (!is_null($this->oRealCategory)) {
            $sCategoryNameHtml = $this->RenderCategoryName($sListFilterItemId, $bShowArticleCount, $iLevelCount);
        }
        $sChildCategoriesHtml = $this->RenderChildCategories($sListFilterItemId, $bHideEmptyCategories, $bShowArticleCount, $iLevelCount + 1);
        if (empty($sChildCategoriesHtml) && 0 == $this->iAllItemCount && $bHideEmptyCategories) {
            $sHtml = '';
        } else {
            if (!is_null($this->oRealCategory)) {
                $aClass = array('CategoryLevel_'.$iLevelCount);
                if (!empty($sChildCategoriesHtml)) {
                    $aClass[] = 'hasChildren';
                } else {
                    $aClass[] = 'isLeaflet';
                }
                if ($this->bIsActive) {
                    $aClass[] = 'active expanded';
                }
                $sHtml .= '<ul>';
                $sHtml .= '<li class="'.implode(' ', $aClass).'">'.$sCategoryNameHtml;
            }
            $sHtml .= $sChildCategoriesHtml;
            if (!is_null($this->oRealCategory)) {
                $sHtml .= '</li>';
                $sHtml .= '</ul>';
            }
        }

        return $sHtml;
    }

    /**
     * Renders list of child categories recursively.
     *
     * @param bool $sListFilterItemId
     * @param bool $bHideEmptyCategories
     * @param bool $bShowArticleCount
     * @param int  $iLevelCount
     *
     * @return string
     */
    public function RenderChildCategories($sListFilterItemId = false, $bHideEmptyCategories = false, $bShowArticleCount = false, $iLevelCount = 1)
    {
        $sHtml = '';
        if (!is_null($this->oChildCategoryTreeList)) {
            $this->oChildCategoryTreeList->GoToStart();
            while ($oChildCategoryTree = $this->oChildCategoryTreeList->Next()) {
                $sHtml .= $oChildCategoryTree->Render($sListFilterItemId, $bHideEmptyCategories, $bShowArticleCount, $iLevelCount);
            }
        }

        return $sHtml;
    }

    /**
     * Renders the category name and item count.
     *
     * @param bool $sListFilterItemId
     * @param bool $bShowArticleCount
     * @param int  $iLevelCount
     *
     * @return string
     */
    protected function RenderCategoryName($sListFilterItemId = false, $bShowArticleCount = false, $iLevelCount = 1)
    {
        $aClass = array('CategoryName');
        if ($this->bIsActive) {
            $aClass[] = 'active';
            $aClass[] = 'expanded';
        }
        $sHtml = '<a class="'.implode(' ', $aClass).'" href="'.$this->GetCategoryURL($sListFilterItemId).'">'.TGlobal::OutHtml($this->oRealCategory->GetName());

        if ($bShowArticleCount) {
            $sHtml .= $this->RenderCategoryCount();
        }
        $sHtml .= '</a>';

        return $sHtml;
    }

    /**
     * Renders the category count.
     *
     * @return string
     */
    protected function RenderCategoryCount()
    {
        return "<span class='CategoryCount'>(".TGlobal::OutHtml($this->iAllItemCount).')</span>';
    }

    /**
     * Returns the URL of the category.
     * If tree was used as a filter function returns url to filter for the category.
     * If tree was used as normal category tree then function returns link to category detail page.
     *
     * @param bool $sListFilterItemId
     *
     * @return string
     */
    protected function GetCategoryURL($sListFilterItemId = false)
    {
        $sURL = '';
        if (!is_null($this->oRealCategory)) {
            if ($sListFilterItemId) {
                $sURL = $this->GetAddFilterURL($sListFilterItemId);
            } else {
                $sURL = $this->oRealCategory->GetLink();
            }
        }

        return $sURL;
    }

    /**
     * return url that sets the filter to the current category.
     *
     * @param string $sValue
     */
    public function GetAddFilterURL($sListFilterItemId)
    {
        $oActiveListFilter = TdbPkgShopListfilter::GetActiveInstance();
        $aURLData = $oActiveListFilter->GetCurrentFilterAsArray();
        $aURLData = $this->ClearOtherCategoryTreeFilter($aURLData, $sListFilterItemId);
        $aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$sListFilterItemId][] = $this->oRealCategory->id;
        $aURLData[TdbPkgShopListfilter::URL_PARAMETER_IS_NEW_REQUEST] = '1';

        return $this->getActivePageService()->getLinkToActivePageRelative($aURLData);
    }

    /**
     * Clear active category filter for the filter url.
     * You can filter onyl for one category.
     *
     * @param  $aURLData
     * @param  $sListFilterItemId
     *
     * @return array
     */
    protected function ClearOtherCategoryTreeFilter($aURLData, $sListFilterItemId)
    {
        if (is_array($aURLData) && array_key_exists(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA, $aURLData) && is_array($aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA]) && array_key_exists($sListFilterItemId, $aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA])
        ) {
            unset($aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$sListFilterItemId]);
        }

        return $aURLData;
    }

    /**
     * Add item count to category in tree and add it to all parent categories.
     *
     * @param  $sCategoryId
     * @param  $iArticleCount
     *
     * @return bool
     */
    public function AddItemCount($sCategoryId, $iArticleCount)
    {
        $bSet = false;
        if (array_key_exists($sCategoryId, $this->aContainingChildCategories)) {
            $this->iAllItemCount = $this->iAllItemCount + $iArticleCount;
            if (!is_null($this->oChildCategoryTreeList)) {
                $this->oChildCategoryTreeList->GoToStart();
                while ($oChildCategoryTree = $this->oChildCategoryTreeList->Next()) {
                    if (!$bSet) {
                        $bSet = $oChildCategoryTree->AddItemCount($sCategoryId, $iArticleCount);
                    }
                }
            }
        } else {
            if (!is_null($this->oRealCategory) && $sCategoryId == $this->oRealCategory->id) {
                $this->iAllItemCount = $iArticleCount;
                $bSet = true;
            }
        }

        return $bSet;
    }

    /**
     * mark categories and their parents as active if they are in the array.
     *
     * @param $aActiveCategories
     *
     * @return bool
     */
    public function MarkActiveCategories($aActiveCategories)
    {
        $this->bIsActive = false;
        if (in_array($this->id, $aActiveCategories)) {
            $this->bIsActive = true;
        } else {
            if (!is_null($this->oChildCategoryTreeList)) {
                $this->oChildCategoryTreeList->GoToStart();
                while (!$this->bIsActive && ($oCatTree = &$this->oChildCategoryTreeList->Next())) {
                    $this->bIsActive = $this->bIsActive || $oCatTree->MarkActiveCategories($aActiveCategories);
                }
                $this->oChildCategoryTreeList->GoToStart();
            }
        }

        return $this->bIsActive;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
