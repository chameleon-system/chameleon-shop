<?php

// Amazonpayment

$oViewRenderer5 = new ViewRenderer();
$oViewRenderer5->AddMapper(new \ChameleonSystem\AmazonPaymentBundle\mappers\AmazonButtonWidgetMapper());
$spot5 = $oViewRenderer5->Render('/pkgshoppaymentamazon/amazonbutton_login.html.twig');

$oViewRenderer = new ViewRenderer();
$oViewRenderer->AddSourceObject('spot1', $spot1);
$oViewRenderer->AddSourceObject('spot2', $spot2);
$oViewRenderer->AddSourceObject('spot3', $spot3);
$oViewRenderer->AddSourceObject('spot4', $spot4);
$oViewRenderer->AddSourceObject('spot5', $spot5);
