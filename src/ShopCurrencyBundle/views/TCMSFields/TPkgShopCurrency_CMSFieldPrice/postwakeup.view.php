// start postakeup.view.php from TPkgShopCurrency
if (isset($this->sqlData['<?= $sFieldDatabaseName; ?>'])) {
    if (!is_null($this-><?= $sFieldName; ?>Original)) {
        $this->sqlData['<?= $sFieldDatabaseName; ?>'] = $this-><?= $sFieldName; ?>Original;
    }
    $this-><?= $sFieldName; ?> = $this->sqlData['<?= $sFieldDatabaseName; ?>'];
    $oActiveCurrency = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_currency.shop_currency')->getObject();
    if ($oActiveCurrency) {
        $this-><?= $sFieldName; ?>Formated = $oActiveCurrency->GetFormattedCurrency($this->sqlData['<?= $sFieldDatabaseName; ?>']);
    }
    unset($this->sqlData['<?= $sFieldDatabaseName; ?>__currencyType']);

    $this->PostLoadHook(); // call post load hook to recalculate the values based on the original values
}
/* end post wakeup */
