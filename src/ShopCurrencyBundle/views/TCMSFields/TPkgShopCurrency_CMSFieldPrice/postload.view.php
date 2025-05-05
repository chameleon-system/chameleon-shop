// start currency postload (see postload.view.php in TPkgShopCurrency_CMSFieldPrice
if(!TGlobal::IsCMSMode()) {
    if (isset($this->sqlData['<?php echo $sFieldDatabaseName; ?>'])) {

        $currencyTypeFieldNameSql = '<?php echo $sFieldDatabaseName; ?>__currencyType';
        $originalCurrencyFieldNameSql = '<?php echo $sFieldDatabaseName; ?>__original';

        $oActiveCurrency = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_currency.shop_currency')->getObject();
        if(false !== $oActiveCurrency) {
            $currencyTypeOfFieldChanged = false === isset($this->sqlData[$currencyTypeFieldNameSql]);
            $currencyTypeOfFieldChanged = $currencyTypeOfFieldChanged || $this->sqlData[$currencyTypeFieldNameSql] !== $oActiveCurrency->id;

            if (true === $currencyTypeOfFieldChanged && false === $oActiveCurrency->fieldIsBaseCurrency) {
                $this-><?php echo $sFieldName; ?>Original = $this->sqlData['<?php echo $sFieldDatabaseName; ?>'];
                $this->sqlData[$originalCurrencyFieldNameSql] = $this-><?php echo $sFieldName; ?>Original;

                <?php if (null !== $sValueTypeFieldName) {
                    ?>
                if (isset($this->sqlData['<?php echo $sValueTypeFieldName; ?>']) && $this->sqlData['<?php echo $sValueTypeFieldName; ?>'] === 'absolut') {
                    $this->sqlData['<?php echo $sFieldDatabaseName; ?>'] = TdbPkgShopCurrency::ConvertToActiveCurrency($this->sqlData['<?php echo $sFieldDatabaseName; ?>']);
                }
                <?php
                } else {
                    ?>
                $this->sqlData['<?php echo $sFieldDatabaseName; ?>'] = TdbPkgShopCurrency::ConvertToActiveCurrency($this->sqlData['<?php echo $sFieldDatabaseName; ?>']);
                <?php
                } ?>

                $this->sqlData[$currencyTypeFieldNameSql] = $oActiveCurrency->id;
            }
            $this-><?php echo $sFieldName; ?> = $this->sqlData['<?php echo $sFieldDatabaseName; ?>'];
            $this-><?php echo $sFieldName; ?>Formated = $oActiveCurrency->GetFormattedCurrency($this->sqlData['<?php echo $sFieldDatabaseName; ?>']);
        }
    }

}else{

    if ($oLocal === null && class_exists('TCMSLocal',false)) {
        $oLocal = TCMSLocal::GetActive();
    }

    if (isset($this->sqlData['<?php echo $sFieldDatabaseName; ?>'])) {
        $this-><?php echo $sFieldName; ?> = $this->sqlData['<?php echo $sFieldDatabaseName; ?>'];
    } else {
        $this-><?php echo $sFieldName; ?> = 0;
    }

    if (!is_null($oLocal)) {
        $this-><?php echo $sFieldName; ?>Formated = $oLocal->FormatNumber($this-><?php echo $sFieldName; ?>,<?php echo $numberOfDecimals; ?>);
    }
}
// end currency logic