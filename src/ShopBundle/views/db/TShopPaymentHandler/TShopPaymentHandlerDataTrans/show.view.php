<?php
/*@var $oShop TdbShop*/
/*@var $oPaymentHandler TdbShopPaymentHandler*/
/*@var $aCallTimeVars array*/
$bChooseMode = false;
if (array_key_exists('oPaymentData', $aCallTimeVars)) {
    $oUserPaymentData = $aCallTimeVars['oPaymentData'];
    $aUserPaymentData = $oUserPaymentData->fieldRawdata;
    $sPaymentDataId = $oUserPaymentData->id;
    $oUser = TdbDataExtranetUser::GetInstance();
    $bChooseMode = true;
}
?>
<table>
    <tr>
        <th>Kreditkarte</th>
        <td><?=TGlobal::OutHTML($aUserPaymentData['paydata_cc_typ']); ?></td>
    </tr>
    <tr>
        <th>Nummer</th>
        <td><?=TGlobal::OutHTML($aUserPaymentData['paydata_cc_number']); ?></td>
    </tr>
    <tr>
        <th>Vor- und Nachname</th>
        <td><?=TGlobal::OutHTML($aUserPaymentData['paydata_cc_cardowner']); ?></td>
    </tr>
    <tr>
        <th>Gültig bis</th>
        <td><?=TGlobal::OutHTML($aUserPaymentData['paydata_cc_expdate']); ?></td>
    </tr>
    <tr>
        <th>Prüfziffer</th>
        <td>***</td>
    </tr>
    <?php if ($bChooseMode) {
    ?>
    <tr>
        <td>
            <?php
            $sChecked = '';
    if ($oUserPaymentData->fieldLastUsed) {
        $sChecked = 'checked="checked"';
    } ?>
            <a href="<?=$oPaymentHandler->GetUseSavedPaymentURL($sPaymentDataId); ?>"><?=TGlobal::OutHtml(TGlobal::Translate('chameleon_system_shop.payment_data_trans.action_use_stored_card')); ?></a>
        </td>
        <td>
            <a href="<?=$oUser->GetDeletePaymentDataURL($sPaymentDataId, $aCallTimeVars['iPaymentMethodId']); ?>"><?= TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.action_delete_stored_card')); ?></a>
        </td>
    </tr>
    <?php
} ?>
</table>