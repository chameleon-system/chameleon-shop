// start postakeup.view.php from TPkgShopCurrency
if (isset($this->sqlData['<?= $sFieldDatabaseName; ?>'])) {
    if (!is_null($this-><?= $sFieldName; ?>Original)) {
        $this->sqlData['<?= $sFieldDatabaseName; ?>'] = $this-><?= $sFieldName; ?>Original;
    }
    $this-><?= $sFieldName; ?> = $this->sqlData['<?= $sFieldDatabaseName; ?>'];
    $oActiveCurrency = TdbPkgShopCurrency::GetActiveInstance();
    if ($oActiveCurrency) {
        $this-><?= $sFieldName; ?>Formated = $oActiveCurrency->GetFormattedCurrency($this->sqlData['<?= $sFieldDatabaseName; ?>']);
    }
    unset($this->sqlData['<?= $sFieldDatabaseName; ?>__currencyType']);

    $this->PostLoadHook(); // call post load hook to recalculate the values based on the original values
}
/* end post wakeup */
