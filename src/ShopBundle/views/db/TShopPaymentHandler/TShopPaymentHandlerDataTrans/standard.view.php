<?php
/*@var $oShop TdbShop*/
/*@var $oPaymentHandler TdbShopPaymentHandler*/
/*@var $aCallTimeVars array*/
$oMsgManager = TCMSMessageManager::GetInstance();
$aInputFieldParameter = $data['IPaymentHiddenInput'];
$aInputFieldUserAddressParameter = $data['aUserAddressData'];
$aSpecificPaymentParameter = $data['aPaymenttypeSpecificParameter'];
$bInputIsActive = $aCallTimeVars['bInputIsActive'];
$sSpotName = '';
if (array_key_exists('sSpotName', $aCallTimeVars)) {
    $sSpotName = $aCallTimeVars['sSpotName'];
}
$oIPaymentHandler = $data['oPaymentHandler'];
$aInputControlParameter = array();
if (_DEVELOPMENT_MODE == false) {
    $aInputControlParameter[] = 'autocomplete="off"';
}
$sInputControlParameter = implode(' ', $aInputControlParameter);
?>
<input type="hidden" name="spot" value="<?=TGlobal::OutHTML($sSpotName); ?>"/>
<input type="hidden" name="shop_payment_method_id" value="<?=TGlobal::OutHTML($aCallTimeVars['iPaymentMethodId']); ?>"/>
<?php
foreach ($aInputFieldParameter as $sKey => $sValue) {
    ?><input type="hidden" name="<?=TGlobal::OutHTML($sKey); ?>" value="<?=TGlobal::OutHTML($sValue); ?>"/><?php
}
foreach ($aInputFieldUserAddressParameter as $sKey => $sValue) {
    ?><input type="hidden" name="<?=TGlobal::OutHTML($sKey); ?>" value="<?=TGlobal::OutHTML($sValue); ?>"/><?php
}
foreach ($aSpecificPaymentParameter as $sKey => $sValue) {
    ?><input type="hidden" name="<?=TGlobal::OutHTML($sKey); ?>" value="<?=TGlobal::OutHTML($sValue); ?>"/><?php
}
?>

<div class="creditCardContainer">

    <?php if ($oMsgManager->ConsumerHasMessages(TShopPaymentHandlerIPaymentCreditCard::MSG_MANAGER_NAME)) {
    ?>
    <div
        class="marginBottom10 marginTop10"><?php echo $oMsgManager->RenderMessages(TShopPaymentHandlerIPaymentCreditCard::MSG_MANAGER_NAME); ?></div>
    <?php
} ?>

    <div class="savely-info">
        <div class="isLft"><span class="i i-savely-creditcard"></span></div>
        <div class="isLft">
            <div
                class="font32 colorblack1"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.security_headline')); ?></div>
            <div>(<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.security_text')); ?>
                .)
            </div>
        </div>
        <div class="cleardiv"></div>
    </div>

    <div class="inputLabel font19 colorblack1"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_type')); ?>:</div>
    <div>
        <?php if (!array_key_exists('cc_typ', $aUserPaymentData)) {
        $aUserPaymentData['cc_typ'] = '';
    } ?>
        <?=TTemplateTools::SelectField('cc_typ', array('Mastercard' => 'Mastercard', 'VisaCard' => 'Visa'), 148, $aUserPaymentData['cc_typ']); ?>
    </div>

    <div class="inputLabel font19 colorblack1"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_number')); ?></div>
    <div class="inputLabelSub">
        (<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_number_help')); ?>
        .)
    </div>
    <div>
        <?php if (!array_key_exists('cc_number', $aUserPaymentData)) {
        $aUserPaymentData['cc_number'] = '';
    } ?>
        <?=TTemplateTools::TWInputField('cc_number', $aUserPaymentData['cc_number'], false, 148, $sInputControlParameter); ?>
    </div>

    <div class="inputLabel font19 colorblack1"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_owner')); ?></div>
    <div>
        <?php if (!array_key_exists('addr_name', $aUserPaymentData)) {
        $aUserPaymentData['addr_name'] = '';
    } ?>
        <?=TTemplateTools::TWInputField('addr_name', $aUserPaymentData['addr_name'], false, 296, $sInputControlParameter); ?>
    </div>

    <div class="inputLabel font19 colorblack1"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_valid_until')); ?></div>
    <div>(<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_valid_until_help')); ?>.)</div>
    <div>
        <?php
        $aMonth = array('1' => '01', '2' => '02', '3' => '03', '4' => '04', '5' => '05', '6' => '06', '7' => '07', '8' => '08', '9' => '09', '10' => '10', '11' => '11', '12' => '12');
        $iYear = date('Y');
        $aYear = array($iYear => $iYear, $iYear + 1 => $iYear + 1, $iYear + 2 => $iYear + 2, $iYear + 3 => $iYear + 3, $iYear + 4 => $iYear + 4, $iYear + 5 => $iYear + 5, $iYear + 6 => $iYear + 6);
        if (!array_key_exists('cc_expdate_month', $aUserPaymentData)) {
            $aUserPaymentData['cc_expdate_month'] = '';
        }
        echo TTemplateTools::SelectField('cc_expdate_month', $aMonth, 54, $aUserPaymentData['cc_expdate_month']);
        if (!array_key_exists('cc_expdate_year', $aUserPaymentData)) {
            $aUserPaymentData['cc_expdate_year'] = '';
        }
        echo TTemplateTools::SelectField('cc_expdate_year', $aYear, 69, $aUserPaymentData['cc_expdate_year']);
        ?>
    </div>

    <div class="inputLabel font19 colorblack1"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_checksum')); ?></div>
    <div>
        (<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_checksum_help')); ?>
        .)
    </div>
    <div class="checkNo isLft">
        <?php if (!array_key_exists('cc_checkcode', $aUserPaymentData)) {
            $aUserPaymentData['cc_checkcode'] = '';
        } ?>
        <?=TTemplateTools::TWInputField('cc_checkcode', $aUserPaymentData['cc_checkcode'], false, false, $sInputControlParameter);?>
    </div>
    <span class="i i-security-code isLft"></span>

    <div class="cleardiv">&nbsp;</div>
    <div
        class="colorblack1"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.form_card_payment_time'));?></div>
</div>




<?php /*
<table>
  <?php if ($oMsgManager->ConsumerHasMessages(TShopPaymentHandlerIPaymentCreditCard::MSG_MANAGER_NAME)) {?>
    <tr>
      <td colspan="2"><?php echo $oMsgManager->RenderMessages(TShopPaymentHandlerIPaymentCreditCard::MSG_MANAGER_NAME); ?></td>
    </tr>
  <?php } ?>
  <tr>
    <td>
      <input type="radio" class="plain" name="payment_data" value="livedata" <?php if($bInputIsActive) echo 'checked="checked"'; ?> /> <?=TGlobal::OutHTML(TGlobal::Translate('Neue Bankdaten erstellen'));?>
    </td>
  </tr>
  <tr>
    <th><?=TGlobal::OutHTML(TGlobal::Translate("Kreditkarte"))?>:<span class="require">*</span></th>
    <td>
      <?php if(!array_key_exists("cc_typ",$aUserPaymentData)) $aUserPaymentData['cc_typ'] = ""; ?>
      <?=TTemplateTools::SelectField('cc_typ', array('Mastercard'=>'Mastercard','VisaCard'=>'Visa'), 181, $aUserPaymentData['cc_typ'])?>
    </td>
  </tr>
  <tr>
    <th><?=TGlobal::OutHTML(TGlobal::Translate("Nummer"))?><span class="require">*</span></th>
    <td>
      <?php if(!array_key_exists("cc_number",$aUserPaymentData)) $aUserPaymentData['cc_number'] = ""; ?>
      <?=TTemplateTools::InputField('cc_number', $aUserPaymentData['cc_number'], 200,$sInputControlParameter)?>
    </td>
  </tr>
  <tr>
    <th><?=TGlobal::OutHTML(TGlobal::Translate("Name"))?><span class="require">*</span></th>
    <td>
      <?php if(!array_key_exists("addr_name",$aUserPaymentData)) $aUserPaymentData['addr_name'] = ""; ?>
      <?=TTemplateTools::InputField('addr_name', $aUserPaymentData['addr_name'], 200,$sInputControlParameter)?>
    </td>
  </tr>
  <tr>
    <th><?=TGlobal::OutHTML(TGlobal::Translate("Gültig bis"))?><span class="require">*</span></th>
    <td>
      <?php
        $aMonth = array('1'=>'01','2'=>'02','3'=>'03','4'=>'04','5'=>'05','6'=>'06','7'=>'07','8'=>'08','9'=>'09','10'=>'10','11'=>'11','12'=>'12');
        $iYear = date('Y');
        $aYear = array($iYear=>$iYear,$iYear+1=>$iYear+1,
                       $iYear+2=>$iYear+2,$iYear+3=>$iYear+3,
                       $iYear+4=>$iYear+4,$iYear+5=>$iYear+5,
                       $iYear+6=>$iYear+6);
        if(!array_key_exists("cc_expdate_month",$aUserPaymentData)) $aUserPaymentData['cc_expdate_month'] = "";
        echo TTemplateTools::SelectField('cc_expdate_month', $aMonth, 89, $aUserPaymentData['cc_expdate_month']);
        if(!array_key_exists("cc_expdate_year",$aUserPaymentData)) $aUserPaymentData['cc_expdate_year'] = "";
        echo TTemplateTools::SelectField('cc_expdate_year', $aYear, 89, $aUserPaymentData['cc_expdate_year']);
      ?>
    </td>
  </tr>
  <tr>
    <th><?=TGlobal::OutHTML(TGlobal::Translate("Prüfziffer"))?><span class="require">*</span></th>
    <td>
      <?php if(!array_key_exists("cc_checkcode",$aUserPaymentData)) $aUserPaymentData['cc_checkcode'] = ""; ?>
      <?=TTemplateTools::InputField('cc_checkcode', $aUserPaymentData['cc_checkcode'], 200,$sInputControlParameter)?>
    </td>
  </tr>
</table>
*/
?>
