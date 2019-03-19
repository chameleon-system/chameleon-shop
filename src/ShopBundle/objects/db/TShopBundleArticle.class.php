<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopBundleArticle extends TAdbShopBundleArticle
{
    const VIEW_PATH = 'pkgShop/views/db/TShopBundleArticle';

    /**
     * used to display an article.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Customer', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        $oView->AddVar('oShopBundleArticle', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
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
     * Add view based clear cache triggers for the Render method here.
     *
     * @param array  $aClearTriggers - clear trigger array (with current contents)
     * @param string $sViewName      - view being requested
     * @param string $sViewType      - location of the view (Core, Custom-Core, Customer)
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function AddClearCacheTriggers(&$aClearTriggers, $sViewName, $sViewType)
    {
    }

    /**
     * used to set the id of a clear cache (ie. related table).
     *
     * @param string $sTableName - the table name
     *
     * @return int|string|null
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function GetClearCacheTriggerTableValue($sTableName)
    {
        $sValue = '';
        switch ($sTableName) {
            case $this->table:
                $sValue = $this->id;
                break;

            default:
                break;
        }

        return $sValue;
    }

    /**
     * returns an array with all table names that are relevant for the render function.
     *
     * @param string $sViewName - the view name being requested (if know by the caller)
     * @param string $sViewType - the view type (core, custom-core, customer) being requested (if know by the caller)
     *
     * @return array
     */
    public static function GetCacheRelevantTables($sViewName = null, $sViewType = null)
    {
        $aTables = array();
        $aTables[] = 'shop_article';
        $aTables[] = 'shop_bundle_article';

        return $aTables;
    }
}
