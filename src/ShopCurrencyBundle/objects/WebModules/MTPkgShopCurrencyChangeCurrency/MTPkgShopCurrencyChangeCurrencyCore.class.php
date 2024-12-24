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
 * used to show available currencies to the user and to provide a method to change the currency.
 * /**/
class MTPkgShopCurrencyChangeCurrencyCore extends TUserCustomModelBase
{
    public function Execute()
    {
        parent::Execute();

        $this->data['oActiveCurrency'] = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_currency.shop_currency')->getObject();
        $this->data['oCurrencyList'] = TdbPkgShopCurrencyList::GetList();

        return $this->data;
    }

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'ChangeCurrency';
    }

    /**
     * @return void
     */
    protected function ChangeCurrency()
    {
        $oAction = new TPkgShopCurrency_PkgCmsActionPlugin();
        $oAction->ChangeCurrency(TGlobal::instance()->GetUserData());
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
        $parameters['sActiveCurrency'] = TdbPkgShopCurrency::GetActiveCurrencyId();

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
        $aTrigger = parent::_GetCacheTableInfos();
        if (!is_array($aTrigger)) {
            $aTrigger = [];
        }
        $aTrigger[] = ['table' => 'pkg_shop_currency', 'id' => ''];

        return $aTrigger;
    }
}
