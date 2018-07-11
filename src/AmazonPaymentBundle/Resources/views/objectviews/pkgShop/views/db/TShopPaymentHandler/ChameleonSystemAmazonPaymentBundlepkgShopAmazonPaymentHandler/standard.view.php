<div class="amazonSelectPayment">
    <?php
  /*@var $oShop TdbShop*/
  /*@var $oPaymentHandler TdbShopPaymentHandler*/
  /*@var $aCallTimeVars array*/

if (null === $amazonConfig) {
    echo 'error loading amazon widget';
} else {
    $oViewRenderer = new ViewRenderer();
    $oViewRenderer->AddMapper(new \ChameleonSystem\AmazonPaymentBundle\mappers\AmazonWidgetMapper());
    $oViewRenderer->AddSourceObject('basket', $oBasket);
    $oViewRenderer->AddSourceObject('config', $amazonConfig);
    echo $oViewRenderer->Render('/pkgshoppaymentamazon/widgets/wallet.html.twig');
}
?>
</div>
