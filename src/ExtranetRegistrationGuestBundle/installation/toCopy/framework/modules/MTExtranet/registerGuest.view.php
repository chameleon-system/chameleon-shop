<?php

$oViewRender = new ViewRenderer();
$oViewRender->AddMapper(new TPkgExtranetRegistrationGuestMapper_Form());
$oViewRender->AddSourceObject('oThankYouOrderStep', TdbShopOrderStep::GetStep('thankyou'));
echo $oViewRender->Render('/common/userInput/form/formCreateAccountFromGuest.html.twig');
