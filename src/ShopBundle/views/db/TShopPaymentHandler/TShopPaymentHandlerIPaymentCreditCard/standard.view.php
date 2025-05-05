<?php
/** @var $oShop TdbShop */
/** @var $oPaymentHandler TdbShopPaymentHandler */
/** @var $aCallTimeVars array */
$oMsgManager = TCMSMessageManager::GetInstance();
$aInputFieldParameter = $data['IPaymentHiddenInput'];
$aInputFieldUserAddressParameter = $data['aUserAddressData'];
$aSpecificPaymentParameter = $data['aPaymenttypeSpecificParameter'];
$sSpotName = '';
if (array_key_exists('sSpotName', $aCallTimeVars)) {
    $sSpotName = $aCallTimeVars['sSpotName'];
}
$oIPaymentHandler = $data['oPaymentHandler'];
?>
<form name="checkout<?php echo $oIPaymentHandler->id; ?>" method="post" action="<?php echo TGlobal::OutHTML($data['sRequestUrl']); ?>">
    <input type="hidden" name="spot" value="<?php echo TGlobal::OutHTML($sSpotName); ?>"/>
    <input type="hidden" name="shop_payment_method_id"
           value="<?php echo TGlobal::OutHTML($aCallTimeVars['iPaymentMethodId']); ?>"/>
    <?php
    foreach ($aInputFieldParameter as $sKey => $sValue) {
        ?>
        <input type="hidden" name="<?php echo TGlobal::OutHTML($sKey); ?>" value="<?php echo TGlobal::OutHTML($sValue); ?>"/>
        <?php
    } ?>
    <?php
    foreach ($aInputFieldUserAddressParameter as $sKey => $sValue) {
        ?>
        <input type="hidden" name="<?php echo TGlobal::OutHTML($sKey); ?>" value="<?php echo TGlobal::OutHTML($sValue); ?>"/>
        <?php
    } ?>
    <?php
    foreach ($aSpecificPaymentParameter as $sKey => $sValue) {
        ?>
        <input type="hidden" name="<?php echo TGlobal::OutHTML($sKey); ?>" value="<?php echo TGlobal::OutHTML($sValue); ?>"/>
        <?php
    } ?>
    <table>
        <?php if ($oMsgManager->ConsumerHasMessages(TShopPaymentHandlerIPaymentCreditCard::MSG_MANAGER_NAME)) {
            ?>
        <tr>
            <td colspan="2"><?php echo $oMsgManager->RenderMessages(TShopPaymentHandlerIPaymentCreditCard::MSG_MANAGER_NAME); ?></td>
        </tr>
        <?php
        } ?>
        <tr>
            <th>Kreditkarte:<span class="require">*</span></th>
            <td onclick="document.getElementById('labelHookId<?php echo $aCallTimeVars['iPaymentMethodId']; ?>').checked='checked'">
                <?php if (!array_key_exists('cc_typ', $aUserPaymentData)) {
                    $aUserPaymentData['cc_typ'] = '';
                } ?>
                <?php echo TTemplateTools::SelectField('cc_typ', ['Mastercard' => 'Mastercard', 'VisaCard' => 'Visa'], 130, $aUserPaymentData['cc_typ']); ?>
            </td>
        </tr>
        <tr>
            <td align="right" colspan="2">
            </td>
        </tr>
        <tr>
            <th>Nummer</th>
            <td onclick="document.getElementById('labelHookId<?php echo $aCallTimeVars['iPaymentMethodId']; ?>').checked='checked'">
                <?php if (!array_key_exists('cc_number', $aUserPaymentData)) {
                    $aUserPaymentData['cc_number'] = '';
                } ?>
                <?php echo TTemplateTools::InputField('cc_number', $aUserPaymentData['cc_number'], 130); ?>
            </td>
        </tr>
        <tr>
            <th>Vor- und Nachname</th>
            <td onclick="document.getElementById('labelHookId<?php echo $aCallTimeVars['iPaymentMethodId']; ?>').checked='checked'">
                <?php if (!array_key_exists('addr_name', $aUserPaymentData)) {
                    $aUserPaymentData['addr_name'] = '';
                } ?>
                <?php echo TTemplateTools::InputField('addr_name', $aUserPaymentData['addr_name'], 130); ?>
            </td>
        </tr>
        <tr>
            <th>Gültig bis</th>
            <td onclick="document.getElementById('labelHookId<?php echo $aCallTimeVars['iPaymentMethodId']; ?>').checked='checked'">
                <?php
                            $aMonth = ['1' => '01', '2' => '02', '3' => '03', '4' => '04', '5' => '05', '6' => '06', '7' => '07', '8' => '08', '9' => '09', '10' => '10', '11' => '11', '12' => '12'];
$iYear = date('Y');
$aYear = [$iYear => $iYear, $iYear + 1 => $iYear + 1, $iYear + 2 => $iYear + 2, $iYear + 3 => $iYear + 3, $iYear + 4 => $iYear + 4, $iYear + 5 => $iYear + 5, $iYear + 6 => $iYear + 6];
if (!array_key_exists('cc_expdate_month', $aUserPaymentData)) {
    $aUserPaymentData['cc_expdate_month'] = '';
}
echo TTemplateTools::SelectField('cc_expdate_month', $aMonth, 63, $aUserPaymentData['cc_expdate_month']);
if (!array_key_exists('cc_expdate_year', $aUserPaymentData)) {
    $aUserPaymentData['cc_expdate_year'] = '';
}
echo TTemplateTools::SelectField('cc_expdate_year', $aYear, 63, $aUserPaymentData['cc_expdate_year']);
?>
            </td>
        </tr>
        <tr>
            <th>Prüfziffer</th>
            <td onclick="document.getElementById('labelHookId<?php echo $aCallTimeVars['iPaymentMethodId']; ?>').checked='checked'">
                <?php if (!array_key_exists('cc_checkcode', $aUserPaymentData)) {
                    $aUserPaymentData['cc_checkcode'] = '';
                } ?>
                <?php echo TTemplateTools::InputField('cc_checkcode', $aUserPaymentData['cc_checkcode'], 130); ?>
            </td>
        </tr>
    </table>
    <input type="submit" class="basketinputbutton nextbuttoninput" alt="Weiter" value="Weiter"/>
</form>