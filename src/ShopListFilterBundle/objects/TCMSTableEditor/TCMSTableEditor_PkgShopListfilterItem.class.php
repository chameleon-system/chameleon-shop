<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSTableEditor_PkgShopListfilterItem extends TCMSTableEditor
{
    /**
     * use this method to change field configurations before saving
     * e.g. overload field types or field modifier types (make it possible to
     * save hidden and readonly fields).
     *
     * @param TIterator $oFields
     */
    protected function PrepareFieldsForSave(&$oFields)
    {
        parent::PrepareFieldsForSave($oFields);
        $this->RemoveDisabledFields($oFields);
    }

    /**
     * method is called before the fields are shown in the editor - this allows the
     * TCMSTableEditor class or children to modify the fields before they are shown to the user.
     *
     * @param TIterator $oFields
     */
    public function ProcessFieldsBeforeDisplay(&$oFields)
    {
        parent::ProcessFieldsBeforeDisplay($oFields);
        $this->RemoveDisabledFields($oFields);
    }

    protected function RemoveDisabledFields(&$oFields)
    {
        // hide fields for type
        $oType = null;
        $oNewTypeField = $oFields->FindItemWithProperty('name', 'pkg_shop_listfilter_item_type');
        if (!is_null($this->oTable)) {
            if ($oNewTypeField && $oNewTypeField->data != $this->oTable->fieldPkgShopListfilterItemType) {
                $oType = TdbPkgShopListfilterItemType::GetNewInstance();
                $oType->Load($oNewTypeField->data);
            } else {
                $oType = $this->oTable->GetFieldPkgShopListfilterItemType();
            }
            if ($oType && is_array($oType->sqlData) && count($oType->sqlData) > 0) {
                $oHiddenFields = $oType->GetFieldCmsFieldConfList();
                $aFieldNames = $oHiddenFields->GetItemUniqueValueListForField('name');
                while ($oField = &$oFields->Next()) {
                    if (!array_key_exists($oField->name, $aFieldNames)) {
                        $oField->oDefinition->sqlData['modifier'] = 'hidden';
                    }
                }
                $oFields->GoToStart();
            }
        }
    }
}
