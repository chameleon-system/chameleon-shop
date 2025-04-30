<?php
/** @var $oStep TdbShopOrderStep */
/** @var $sSpotName string */
/** @var $oUser TdbDataExtranetUser */
$oMessageManager = TCMSMessageManager::GetInstance();
$oUser = TdbDataExtranetUser::GetInstance();
?>
<div class="vLoginUser">
    <?php
    $oExtranetConfig = TdbDataExtranet::GetInstance();
$aLoginParams = ['sSpotName' => $oExtranetConfig->fieldExtranetSpotName, 'sConsumer' => $sSpotName.'-form', 'sFailureURL' => $oStep->GetStepURL(), 'sSuccessURL' => $oStep->GetNextStep()->GetStepURL()];
echo $oUser->Render('vLoginBasket', 'Core', $aLoginParams);
$oMessageManager->RenderMessages($sSpotName.'-form');

?>
</div>