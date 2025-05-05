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
    <table width="270" class="usertable" cellpadding="0" cellspacing="0">
        <?php if ($oMsgManager->ConsumerHasMessages(TShopPaymentHandlerIPaymentDebit::MSG_MANAGER_NAME)) {
            ?>
        <tr>
            <td colspan="2"><?php echo $oMsgManager->RenderMessages(TShopPaymentHandlerIPaymentDebit::MSG_MANAGER_NAME); ?></td>
        </tr>
        <?php
        } ?>
        <tr>
            <th>Bankname:<span class="require">*</span></th>
            <td onclick="document.getElementById('labelHookId<?php echo $aCallTimeVars['iPaymentMethodId']; ?>').checked='checked'">
                <?php if (!array_key_exists('bank_name', $aUserPaymentData)) {
                    $aUserPaymentData['bank_name'] = '';
                } ?>
                <?php echo TTemplateTools::InputField('bank_name', $aUserPaymentData['bank_name'], 180); ?>
            </td>
        </tr>
        <tr>
            <th>Kontonummer:<span class="require">*</span></th>
            <td onclick="document.getElementById('labelHookId<?php echo $aCallTimeVars['iPaymentMethodId']; ?>').checked='checked'">
                <?php if (!array_key_exists('bank_accountnumber', $aUserPaymentData)) {
                    $aUserPaymentData['bank_accountnumber'] = '';
                } ?>
                <?php echo TTemplateTools::InputField('bank_accountnumber', $aUserPaymentData['bank_accountnumber'], 180); ?>
            </td>
        </tr>
        <tr>
            <th>Bankleitzahl:<span class="require">*</span></th>
            <td onclick="document.getElementById('labelHookId<?php echo $aCallTimeVars['iPaymentMethodId']; ?>').checked='checked'">
                <?php if (!array_key_exists('bank_code', $aUserPaymentData)) {
                    $aUserPaymentData['bank_code'] = '';
                } ?>
                <?php echo TTemplateTools::InputField('bank_code', $aUserPaymentData['bank_code'], 180); ?>
            </td>
        </tr>
        <tr>
            <th valign="middle">Land: *</th>
            <td>
                <?php
                            $oCountries = TdbDataCountryList::GetList();
$oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
$oUser = TdbDataExtranetUser::GetInstance();
$iCountryId = $oUser->fieldDataCountryId;
if (is_null($iCountryId) || $iCountryId < 1) {
    $iCountryId = $oShop->fieldDataCountryId;
}
echo TTemplateTools::DrawDBSelectField('bank_country', $oCountries, $iCountryId, 306);
?>
            </td>
        </tr>
    </table>
    <input type="submit" class="basketinputbutton nextbuttoninput" alt="Weiter" value="Weiter"/>
</form>