<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopCurrency_CMSFieldPrice extends TPkgShopCurrency_CMSFieldPriceAutoParent
{
    public function RenderFieldPostLoadString()
    {
        $oViewParser = new TViewParser();
        /** @var $oViewParser TViewParser */
        $oViewParser->bShowTemplatePathAsHTMLHint = false;
        $aData = $this->GetFieldWriterData();
        $aData['numberOfDecimals'] = $this->_GetNumberOfDecimals();
        $aData['sValueTypeFieldName'] = $this->oDefinition->GetFieldtypeConfigKey('sValueTypeFieldName');
        $oViewParser->AddVarArray($aData);

        return $oViewParser->RenderObjectPackageView('postload', 'pkgShopCurrency/views/TCMSFields/TPkgShopCurrency_CMSFieldPrice');
    }

    /**
     * injected into the PostWakeupHook in the auto class.
     *
     * @return string
     */
    public function RenderFieldPostWakeupString()
    {
        $oViewParser = new TViewParser();
        /** @var $oViewParser TViewParser */
        $oViewParser->bShowTemplatePathAsHTMLHint = false;
        $aData = $this->GetFieldWriterData();
        $aData['numberOfDecimals'] = $this->_GetNumberOfDecimals();
        $oViewParser->AddVarArray($aData);

        return $oViewParser->RenderObjectPackageView('postwakeup', 'pkgShopCurrency/views/TCMSFields/TPkgShopCurrency_CMSFieldPrice');
    }

    public function RenderFieldPropertyString()
    {
        $sNormalfield = parent::RenderFieldPropertyString();

        $oViewParser = new TViewParser();
        /** @var $oViewParser TViewParser */
        $oViewParser->bShowTemplatePathAsHTMLHint = false;
        $aData = $this->GetFieldWriterData();
        $aData['sFieldName'] = $aData['sFieldName'].'Original';
        $aData['sFieldType'] = 'double';

        $oViewParser->AddVarArray($aData);

        $sNormalfield .= "\n".$oViewParser->RenderObjectView('property', 'TCMSFields/TCMSField');

        return $sNormalfield;
    }
}
