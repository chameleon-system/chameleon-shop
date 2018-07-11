<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Util\UrlNormalization\UrlNormalizationUtil;

class TCMSTableEditorShopCategory extends TCMSTableEditor
{
    /**
     * gets called after save if all posted data was valid.
     *
     * @param TIterator  $oFields    holds an iterator of all field classes from DB table with the posted values or default if no post data is present
     * @param TCMSRecord $oPostTable holds the record object of all posted data
     */
    protected function PostSaveHook(&$oFields, &$oPostTable)
    {
        $this->UpdatePageNaviBreadCrumb();
        $this->UpdateInheritablePropertiesManager($this->oTable, $this->oTable);
        parent::PostSaveHook($oFields, $oPostTable);
    }

    /**
     * traverses through all sub categories calling UpdateInheritableProperties() for every category.
     * overwrite UpdateInheritableProperties to inherit properties to child categories.
     *
     * @param TdbShopCategory $oRootCategory
     * @param TdbShopCategory $oCategory
     */
    protected function UpdateInheritablePropertiesManager($oRootCategory, $oCategory)
    {
        $oChildren = $oCategory->GetChildren();
        while ($oChild = $oChildren->Next()) {
            $this->UpdateInheritableProperties($oRootCategory, $oChild);
            $this->UpdateInheritablePropertiesManager($oRootCategory, $oChild);
        }
    }

    /**
     * update recursive properties in oCategory based on oOwningCategory.
     *
     * @param TdbShopCategory $oOwningCategory
     * @param TdbShopCategory $oCategory
     */
    protected function UpdateInheritableProperties($oOwningCategory, $oCategory)
    {
        $activeValue = '1';
        if (false === $oOwningCategory->fieldActive || false === $oCategory->parentCategoriesAreActive()) {
            $activeValue = '0';
        }
        $oTableEditor = TTools::GetTableEditorManager($this->oTableConf->fieldName, $oCategory->id);
        $oTableEditor->AllowEditByAll($this->bAllowEditByAll);
        $oTableEditor->SaveField('tree_active', $activeValue, false);
    }

    /**
     * makes it possible to modify the contents written to database after the copy
     * is commited.
     */
    protected function OnAfterCopy()
    {
        parent::OnAfterCopy();
        $this->UpdatePageNaviBreadCrumb();
    }

    /**
     * update the page navi breadcrumb.
     */
    public function UpdatePageNaviBreadCrumb()
    {
        $sEditLanguage = $this->oTableConf->GetLanguage();
        $breadcrumbs = self::GetNavigationBreadCrumbs($this->sId, $sEditLanguage);
        $this->SaveField('url_path', $breadcrumbs);
        // now we also need to update all children of this category...
        $oCategory = TdbShopCategory::GetNewInstance();
        $oCategory->SetLanguage($sEditLanguage);
        /** @var $oCategory TdbShopCategory */
        if (!$oCategory->Load($this->sId)) {
            $oCategory = null;
        } else {
            $oChildren = &$oCategory->GetChildren();
            while ($oChild = $oChildren->Next()) {
                $oEditor = new TCMSTableEditorManager();
                /** @var $oEditor TCMSTableEditorManager */
                $oEditor->Init($this->oTableConf->id, $oChild->id, $sEditLanguage);
                $oEditor->AllowEditByAll($this->bAllowEditByAll);
                $oEditor->SaveField('name', $oChild->fieldName, true);
            }
        }
    }

    /**
     * returns the breadcrumb navigation as plaintext.
     *
     * @param string $id          - id of the page to update
     * @param string $sLanguageID
     *
     * @return string
     */
    public static function GetNavigationBreadCrumbs($id, $sLanguageID = null)
    {
        $sBreadCrumb = '';
        $oCategory = TdbShopCategory::GetNewInstance();
        if (!is_null($sLanguageID)) {
            $oCategory->SetLanguage($sLanguageID);
        }
        /** @var $oCategory TdbShopCategory */
        if (!$oCategory->Load($id)) {
            $oCategory = null;
        }
        if (!is_null($oCategory)) {
            $oCategoryBreadcrumb = &$oCategory->GetBreadcrumb();
            $oCategoryBreadcrumb->GoToStart();
            while ($oCategoryItem = $oCategoryBreadcrumb->Next()) {
                $sBreadCrumb .= '/'.self::getUrlNormalizationUtil()->normalizeUrl($oCategoryItem->GetName());
            }
        }

        return $sBreadCrumb;
    }

    /**
     * @return UrlNormalizationUtil
     */
    private static function getUrlNormalizationUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.url_normalization');
    }
}
