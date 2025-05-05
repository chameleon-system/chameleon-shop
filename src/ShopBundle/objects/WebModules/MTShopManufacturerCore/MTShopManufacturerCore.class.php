<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MTShopManufacturerCore extends TShopUserCustomModelBase
{
    public const URL_ITEM_ID_NAME = 'manufacturerid';

    /**
     * id of manufacturer.
     *
     * @var string|null
     */
    protected $iActiveItemId;

    protected $bAllowHTMLDivWrapping = true;

    public function Init()
    {
        parent::Init();
        $oGlobal = TGlobal::instance();
        $this->iActiveItemId = $oGlobal->GetUserData(self::URL_ITEM_ID_NAME);
        if (empty($this->iActiveItemId)) {
            $this->iActiveItemId = null;
        }
    }

    public function Execute()
    {
        parent::Execute();

        $this->data['oManufacturerList'] = TdbShopManufacturerList::GetList();

        $oConfig = TdbShopManufacturerModuleConf::GetNewInstance();
        /** @var $oConfig TdbShopManufacturerModuleConf */
        if (!$oConfig->LoadFromField('cms_tpl_module_instance_id', $this->instanceID)) {
            $oConfig = null;
        }
        $this->data['oConfig'] = $oConfig;

        if (!is_null($this->iActiveItemId) && (!isset($this->aModuleConfig['bDisableDetailViewCheck']) || true !== $this->aModuleConfig['bDisableDetailViewCheck'])) {
            $this->ViewManufacturerHook();
        }

        return $this->data;
    }

    /**
     * @return void
     */
    protected function ViewManufacturerHook()
    {
        $oItem = TdbShopManufacturer::GetNewInstance();
        /** @var $oItem TdbShopManufacturer */
        if (!$oItem->Load($this->iActiveItemId)) {
            $oItem = null;
        }
        $this->data['oManufacturer'] = $oItem;
        $this->SetTemplate('MTShopManufacturer', 'system/manufacturer');
    }

    public function GenerateModuleNavigation()
    {
        $aItems = [];
        $oManufacturerList = TdbShopManufacturerList::GetList();
        $oManufacturerList->GoToStart();
        $iActiveItem = 0;
        $iNumItems = $oManufacturerList->Length();
        // firstNode, lastNode, active
        while ($oManufacturer = $oManufacturerList->Next()) {
            ++$iActiveItem;
            $aClass = [];
            if (1 == $iActiveItem) {
                $aClass[] = 'firstNode';
            }
            if ($iActiveItem == $iNumItems) {
                $aClass[] = 'lastNode';
            }
            if ($oManufacturer->id == $this->iActiveItemId) {
                $aClass[] = 'active';
            }
            $sClass = '';
            if (count($aClass) > 0) {
                $sClass = 'class="'.implode(' ', $aClass).'"';
            }
            $aItems[] = '<li '.$sClass.'><a href="'.$oManufacturer->GetLinkProducts().'" title="'.TGlobal::OutHTML($oManufacturer->fieldName).'">'.TGlobal::OutHTML($oManufacturer->fieldName).'</a></li>';
        }
        $sNavi = implode('', $aItems);
        if (!empty($sNavi)) {
            $sNavi = '<ul>'.$sNavi.'</ul>';
        }
        if (!is_array($this->data)) {
            $this->data = [];
        }
        $this->data['sModuleNavigation'] = $sNavi;

        return $sNavi;
    }

    /**
     * if this function returns true, then the result of the execute function will be cached.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return true;
    }

    /**
     * return an assoc array of parameters that describe the state of the module.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();
        $parameters['iActiveItemId'] = $this->iActiveItemId;

        return $parameters;
    }

    /**
     * if the content that is to be cached comes from the database (as ist most often the case)
     * then this function should return an array of assoc arrays that point to the
     * tables and records that are associated with the content. one table entry has
     * two fields:
     *   - table - the name of the table
     *   - id    - the record in question. if this is empty, then any record change in that
     *             table will result in a cache clear.
     *
     * @return array
     */
    public function _GetCacheTableInfos()
    {
        $aCacheParams = parent::_GetCacheTableInfos();
        if (!is_array($aCacheParams)) {
            $aCacheParams = [];
        }
        if (!is_null($this->iActiveItemId)) {
            $aCacheParams[] = ['table' => 'shop_manufacturer', 'id' => $this->iActiveItemId];
        } else {
            $aCacheParams[] = ['table' => 'shop_manufacturer', 'id' => ''];
        }
        $aCacheParams[] = ['table' => 'shop_manufacturer_module_conf', 'cms_tpl_module_instance_id' => $this->instanceID];

        return $aCacheParams;
    }
}
