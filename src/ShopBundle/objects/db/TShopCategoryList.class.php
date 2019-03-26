<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;
use ChameleonSystem\CoreBundle\Service\RequestInfoServiceInterface;
use Doctrine\DBAL\Connection;

class TShopCategoryList extends TShopCategoryListAutoParent
{
    const VIEW_PATH = 'pkgShop/views/db/TShopCategoryList';

    /**
     * return all categories connected to the article
     * Add main category from article to list on first position.
     *
     * @param int    $iArticleId
     * @param string $sLanguageID
     *
     * @return TdbShopCategoryList
     */
    public static function &GetArticleCategories($iArticleId, $sLanguageID = null)
    {
        $oList = null;
        $sCategoryRestriction = TdbShopCategoryList::GetActiveCategoryQueryRestriction();
        if (!empty($sCategoryRestriction)) {
            $sCategoryRestriction = ' AND '.$sCategoryRestriction;
        }
        $db = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $query = 'SELECT `shop_category`.*, 1 AS isprimary
                    FROM `shop_article`
              INNER JOIN `shop_category` ON `shop_article`.`shop_category_id` = `shop_category`.`id`
                   WHERE `shop_article`.`id` = '.$db->quote($iArticleId)."
                     {$sCategoryRestriction}

                     UNION DISTINCT

                  SELECT `shop_category`.*, if (`shop_category`.`id` = `shop_article`.`shop_category_id`, 1, 0) AS isprimary
                    FROM `shop_category`
              INNER JOIN `shop_article_shop_category_mlt` ON `shop_category`.`id` = `shop_article_shop_category_mlt`.`target_id`
              INNER JOIN `shop_article` on `shop_article_shop_category_mlt`.`source_id` = `shop_article`.`id`
                   WHERE `shop_article_shop_category_mlt`.`source_id` = ".$db->quote($iArticleId)."
                     {$sCategoryRestriction}

                ORDER BY isprimary DESC, position ASC
        ";

        return TdbShopCategoryList::GetList($query, $sLanguageID);
    }

    /**
     * return root category list.
     *
     * @param string|null $sLanguageID
     *
     * @return TdbShopCategoryList
     */
    public static function &GetRootCategoryList($sLanguageID = null)
    {
        if (null === $sLanguageID) {
            $sLanguageID = self::getMyLanguageService()->getActiveLanguageId();
        }
        $sRestriction = "(`shop_category`.`shop_category_id` = '0' OR `shop_category`.`shop_category_id` = '')";
        $sCategoryRestriction = TdbShopCategoryList::GetActiveCategoryQueryRestriction();
        if (!empty($sCategoryRestriction)) {
            $sRestriction = $sRestriction.' AND '.$sCategoryRestriction;
        }
        $sQuery = self::GetDefaultQuery($sLanguageID, $sRestriction);

        return TdbShopCategoryList::GetList($sQuery, $sLanguageID);
    }

    /**
     * returns the max vat from the category list.
     *
     * @return TdbShopVat
     */
    public function &GetMaxVat()
    {
        $iPointer = $this->getItemPointer();
        $oMaxVatItem = null;
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            $oCurrentVat = $oItem->GetVat();
            if (!is_null($oCurrentVat)) {
                if (is_null($oMaxVatItem)) {
                    $oMaxVatItem = $oCurrentVat;
                } elseif ($oMaxVatItem->fieldVatPercent < $oCurrentVat->fieldVatPercent) {
                    $oMaxVatItem = $oCurrentVat;
                }
            }
        }
        $this->setItemPointer($iPointer);

        return $oMaxVatItem;
    }

    /**
     * returns the shops default article category.
     *
     * @return TdbShopCategory
     */
    public static function &GetDefaultCategory()
    {
        static $oCategory;
        if (!$oCategory) {
            $oRootCategories = &TdbShopCategoryList::GetChildCategories();
            if ($oRootCategories->Length() > 0) {
                $oCategory = &$oRootCategories->Current();
            }
        }

        return $oCategory;
    }

    /**
     * return the category id that matches the path defined by $aPath.
     *
     * @param array $aPath - the category names
     *
     * @return TdbShopCategory
     */
    public static function &GetCategoryForCategoryPath($aPath)
    {
        $oCategory = null;
        $sPath = implode('/', $aPath);
        if (!empty($sPath)) {
            $sPath = '/'.$sPath;
        }
        /** @var $oCategory TdbShopCategory */
        $oCategory = TdbShopCategory::GetNewInstance();
        $activeLanguageId = self::getLanguageService()->getActiveLanguageId();
        $oCategory->SetLanguage($activeLanguageId);
        if (!$oCategory->LoadFromField('url_path', $sPath) || false == $oCategory->AllowDisplayInShop()) {
            if (CMS_TRANSLATION_FIELD_BASED_EMPTY_TRANSLATION_FALLBACK_TO_BASE_LANGUAGE && !TGlobal::IsCMSMode() && TdbCmsConfig::GetInstance()->fieldTranslationBaseLanguageId != $activeLanguageId) {
                // try fallback
                $oCategory->SetLanguage(TdbCmsConfig::GetInstance()->fieldTranslationBaseLanguageId);
                if (!$oCategory->LoadFromField('url_path', $sPath) || false == $oCategory->AllowDisplayInShop()) {
                    $oCategory = null;
                }
            } else {
                $oCategory = null;
            }
        }

        return $oCategory;
    }

    /**
     * returns the child categories of the category identified by iParentId
     * if no parent id is given, then the root categories will be returned.
     *
     * @param string|null $iParentId
     * @param array       $aFilter     - an optional filter list for the category
     * @param string|null $sLanguageID
     *
     * @return TdbShopCategoryList
     */
    public static function &GetChildCategories($iParentId = null, $aFilter = null, $sLanguageID = null)
    {
        $sActiveSnippetRestriction = TdbShopCategoryList::GetActiveCategoryQueryRestriction();
        if (is_null($iParentId)) {
            $iParentId = '';
        }
        $parameters = array(
            'parentId' => $iParentId,
        );
        $parameterType = array();

        $query = 'SELECT `shop_category`.*
                  FROM `shop_category`
                 WHERE `shop_category`.`shop_category_id` = :parentId
               ';

        if (!is_null($aFilter) && count($aFilter) > 0) {
            $parameters['filter'] = $aFilter;
            $parameterType['filter'] = Connection::PARAM_STR_ARRAY;
            $query .= ' AND `shop_category`.`id` IN (:filter) ';
        }
        if (!empty($sActiveSnippetRestriction)) {
            $query .= ' AND '.$sActiveSnippetRestriction;
        }
        $query .= ' ORDER BY `shop_category`.`position` ASC, `shop_category`.`name` ASC';

        $oCategories = new TdbShopCategoryList();
        $oCategories->SetLanguage($sLanguageID);
        $oCategories->Load($query, $parameters, $parameterType);

        return $oCategories;
    }

    /**
     * Returns a query snippet to restrict categories to active categories.
     * NOTE: The method should only ever return a restriction if we are in the front end mode.
     *
     * @return string
     */
    public static function GetActiveCategoryQueryRestriction()
    {
        if (true === TGlobal::IsCMSMode()) {
            return '';
        }

        $databaseConnection = self::getConnection();
        $fieldTranslationUtil = self::getFieldTranslationUtil();
        $activeFieldName = $fieldTranslationUtil->getTranslatedFieldName('shop_category', 'active');
        $treeActiveFieldName = $fieldTranslationUtil->getTranslatedFieldName('shop_category', 'tree_active');

        return sprintf("(`shop_category`.%s = '1' AND `shop_category`.%s = '1')",
            $databaseConnection->quoteIdentifier($activeFieldName),
            $databaseConnection->quoteIdentifier($treeActiveFieldName)
        );
    }

    /**
     * return category path from iEndNodeId to iStartNodeId. if not start node is given,
     * the path will start at the root category.
     *
     * @param int    $iStartNodeId
     * @param int    $iEndNodeId
     * @param string $sLanguageID
     *
     * @return TIterator
     */
    public static function &GetCategoryPath($iEndNodeId, $iStartNodeId = null, $sLanguageID = null)
    {
        if (null === $sLanguageID) {
            $sLanguageID = self::getLanguageService()->getActiveLanguageId();
        }

        $bDone = false;
        $iCurrentCatId = $iEndNodeId;
        $aTmpList = array(); // we store the list in a tmp array so we can reverse the order
        do {
            $oCategory = TdbShopCategory::GetNewInstance();
            $oCategory->SetLanguage($sLanguageID);
            if (!$oCategory->Load($iCurrentCatId)) {
                $oCategory = null;
                $bDone = true;
            } else {
                $aTmpList[] = $oCategory;
                if (null !== $iStartNodeId && $iStartNodeId == $iCurrentCatId) {
                    $bDone = true;
                } else {
                    $oCategory = $oCategory->GetParent();
                    if (null === $oCategory) {
                        $bDone = true;
                    } else {
                        $iCurrentCatId = $oCategory->id;
                    }
                }
            }
        } while (!$bDone);

        $aTmpList = array_reverse($aTmpList);
        $oCategoryPath = new TIterator();
        foreach (array_keys($aTmpList) as $iTmpIndex) {
            $oCategoryPath->AddItem($aTmpList[$iTmpIndex]);
        }

        return $oCategoryPath;
    }

    /**
     * used to display a category list.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Core', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        $oView->AddVar('oCategoryList', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbShopCategoryList::VIEW_PATH, $sViewType);
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        return array();
    }

    /**
     * return all parent categories from the tree.
     *
     * @param string $categoryId
     *
     * @return TIterator
     */
    public static function getParentCategoryList($categoryId)
    {
        $categoryList = new TIterator();
        $aTmpList = array(); // we store the list in a tmp array so we can reverse the order
        $currentCategoryId = $categoryId;
        $bDone = false;
        $requestInfoService = self::getRequestInfoService();
        $languageService = self::getLanguageService();
        if ($requestInfoService->isBackendMode()) {
            $languageId = $languageService->getActiveEditLanguage()->id;
        } else {
            $languageId = $languageService->getActiveLanguageId();
        }
        do {
            $category = TdbShopCategory::GetNewInstance();
            $category->SetLanguage($languageId);
            if (!$category->Load($currentCategoryId)) {
                $category = null;
                $bDone = true;
            } else {
                $category = $category->GetParent();
                if (!is_null($category)) {
                    $aTmpList[] = $category;
                    $currentCategoryId = $category->id;
                } else {
                    $bDone = true;
                }
            }
        } while (!$bDone);

        $aTmpList = array_reverse($aTmpList);
        foreach ($aTmpList as $tempCategory) {
            $categoryList->AddItem($tempCategory);
        }

        return $categoryList;
    }

    /**
     * @return Connection
     */
    private static function getConnection()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
    }

    /**
     * @return RequestInfoServiceInterface
     */
    private static function getRequestInfoService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.request_info_service');
    }

    /**
     * @return LanguageServiceInterface
     */
    private static function getLanguageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.language_service');
    }
}
