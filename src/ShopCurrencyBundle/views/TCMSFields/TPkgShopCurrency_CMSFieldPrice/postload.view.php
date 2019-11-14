// start currency postload (see postload.view.php in TPkgShopCurrency_CMSFieldPrice
if(!TGlobal::IsCMSMode()) {
    if (isset($this->sqlData['<?= $sFieldDatabaseName; ?>'])) {

        $currencyTypeFieldNameSql = '<?= $sFieldDatabaseName; ?>__currencyType';
        $originalCurrencyFieldNameSql = '<?= $sFieldDatabaseName; ?>__original';

        $oActiveCurrency = TdbPkgShopCurrency::GetActiveInstance();
        if(false !== $oActiveCurrency) {
            $currencyTypeOfFieldChanged = false === isset($this->sqlData[$currencyTypeFieldNameSql]);
            $currencyTypeOfFieldChanged = $currencyTypeOfFieldChanged || $this->sqlData[$currencyTypeFieldNameSql] !== $oActiveCurrency->id;

            if (true === $currencyTypeOfFieldChanged && false === $oActiveCurrency->fieldIsBaseCurrency) {
                $this-><?= $sFieldName; ?>Original = $this->sqlData['<?= $sFieldDatabaseName; ?>'];
                $this->sqlData[$originalCurrencyFieldNameSql] = $this-><?= $sFieldName; ?>Original;

                <?php if (null !== $sValueTypeFieldName) {
    ?>
                if (isset($this->sqlData['<?= $sValueTypeFieldName; ?>']) && $this->sqlData['<?= $sValueTypeFieldName; ?>'] === 'absolut') {
                    $this->sqlData['<?= $sFieldDatabaseName; ?>'] = TdbPkgShopCurrency::ConvertToActiveCurrency($this->sqlData['<?= $sFieldDatabaseName; ?>']);
                }
                <?php
} else {
        ?>
                $this->sqlData['<?= $sFieldDatabaseName; ?>'] = TdbPkgShopCurrency::ConvertToActiveCurrency($this->sqlData['<?= $sFieldDatabaseName; ?>']);
                <?php
    } ?>
                $this-><?= $sFieldName; ?> = $this->sqlData['<?= $sFieldDatabaseName; ?>'];
                $this-><?= $sFieldName; ?>Formated = $oActiveCurrency->GetFormattedCurrency($this->sqlData['<?= $sFieldDatabaseName; ?>']);
                $this->sqlData[$currencyTypeFieldNameSql] = $oActiveCurrency->id;
            } else {
                $this-><?= $sFieldName; ?> = $this->sqlData['<?= $sFieldDatabaseName; ?>'];
                $this-><?= $sFieldName; ?>Formated = $oActiveCurrency->GetFormattedCurrency($this->sqlData['<?= $sFieldDatabaseName; ?>']);
            }
        }
    }

}else{

    if ($oLocal === null && class_exists('TCMSLocal',false)) {
        $oLocal =& TCMSLocal::GetActive();
    }

    if (isset($this->sqlData['<?= $sFieldDatabaseName; ?>'])) {
        $this-><?= $sFieldName; ?> = $this->sqlData['<?= $sFieldDatabaseName; ?>'];
    } else {
        $this-><?= $sFieldName; ?> = 0;
    }

    if (!is_null($oLocal)) {
        $this-><?= $sFieldName; ?>Formated = $oLocal->FormatNumber($this-><?= $sFieldName; ?>,<?= $numberOfDecimals; ?>);
    }
}
// end currency logic