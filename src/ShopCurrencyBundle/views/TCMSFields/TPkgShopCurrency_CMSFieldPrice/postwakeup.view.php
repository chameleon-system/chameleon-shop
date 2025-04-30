// start postakeup.view.php from TPkgShopCurrency
if (isset($this->sqlData['<?php echo $sFieldDatabaseName; ?>'])) {
    if (!is_null($this-><?php echo $sFieldName; ?>Original)) {
        $this->sqlData['<?php echo $sFieldDatabaseName; ?>'] = $this-><?php echo $sFieldName; ?>Original;
    }
    $this-><?php echo $sFieldName; ?> = $this->sqlData['<?php echo $sFieldDatabaseName; ?>'];
    $oActiveCurrency = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_currency.shop_currency')->getObject();
    if ($oActiveCurrency) {
        $this-><?php echo $sFieldName; ?>Formated = $oActiveCurrency->GetFormattedCurrency($this->sqlData['<?php echo $sFieldDatabaseName; ?>']);
    }
    unset($this->sqlData['<?php echo $sFieldDatabaseName; ?>__currencyType']);

    $this->PostLoadHook(); // call post load hook to recalculate the values based on the original values
}
/* end post wakeup */
