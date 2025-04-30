<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * module displays the contents of shop_system_info for one or more shop info items.
 * /**/
class MTShopInfoCore extends TShopUserCustomModelBase
{
    /**
     * the config data for the module instance.
     *
     * @var TdbShopSystemInfoModuleConfig
     */
    protected $oModuleConfig;

    protected $bAllowHTMLDivWrapping = true;

    public function Execute()
    {
        parent::Execute();

        $this->data['oConf'] = $this->GetModuleConfig();
        $this->data['oInfos'] = $this->data['oConf']->GetFieldShopSystemInfoList('name');

        return $this->data;
    }

    /**
     * return config record for modul instance.
     *
     * @return TdbShopSystemInfoModuleConfig|null
     */
    protected function GetModuleConfig()
    {
        if (is_null($this->oModuleConfig)) {
            $this->oModuleConfig = TdbShopSystemInfoModuleConfig::GetNewInstance();
            if (!$this->oModuleConfig->LoadFromField('cms_tpl_module_instance_id', $this->instanceID)) {
                $this->oModuleConfig = null;
            }
        }

        return $this->oModuleConfig;
    }

    /**
     * prevent caching if there are messages.
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
        $parameters['instanceID'] = $this->instanceID;

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
        $aClearCacheInfo = parent::_GetCacheTableInfos();
        $oConf = $this->GetModuleConfig();
        if (!is_null($oConf)) {
            $aClearCacheInfo[] = ['table' => $oConf->table, 'id' => $oConf->id];
            $aIDList = $oConf->GetMLTIdList('shop_system_info');
            foreach ($aIDList as $infoPageId) {
                $aClearCacheInfo[] = ['table' => 'shop_system_info', 'id' => $infoPageId];
            }
        }

        return $aClearCacheInfo;
    }
}
