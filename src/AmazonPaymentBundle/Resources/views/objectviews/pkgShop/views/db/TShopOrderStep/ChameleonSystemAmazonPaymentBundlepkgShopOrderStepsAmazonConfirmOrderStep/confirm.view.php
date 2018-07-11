<?php
/** @var $oShop TdbShop */
/** @var $oBasket TShopBasket */
/** @var $oUser TdbDataExtranetUser */
/** @var $oExtranetConfig TdbDataExtranet */

/** @var $oStep TdbShopOrderStep */
/** @var $sSpotName string */
/** @var $aCallTimeVars array */

/** @var $oStepNext TdbShopOrderStep */
/** @var $oStepPrevious TdbShopOrderStep */
/** @var $sBackLink string */
/** @var $sLinkUserData string */
/** @var $sLinkShippingAddress string */
/** @var $sLinkShipping string */
/** @var $newsletter boolean - set to true if the user checked the "signup to newsletter" option */
$oMessageManager = TCMSMessageManager::GetInstance();
$sDescription = $oStep->GetDescription();

?>

<h1><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_headline')); ?></h1>
<?php
if ($oMessageManager->ConsumerHasMessages(MTShopBasketCore::MSG_CONSUMER_NAME.'-agb')) {
    echo '<div class="cmsmessage messageerror mainmessage">'.TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_error')).'</div>';
}
?>
[{CMSMSG-<?= MTShopBasketCore::MSG_CONSUMER_NAME; ?>}]
<?php if (!empty($sDescription)) {
    ?>
<div class="description"><?=$sDescription; ?></div><?php
} ?>
<div class="row">
    <div class="span3">
        <div class="ConfirmStepInfoBlock">
            <div class="shippingConfirmStepInfoBlock ConfirmStepInfoBlockBox">
                <h3><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_shipment')); ?></h3>
                <div class="shippingtype innerBox">
                    <?php
                    $oShippingGroups = TdbShopShippingGroupList::GetAvailableShippingGroups();
                    ?>
                    <h4><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_shipment_type')); ?>
                        (<a href="<?=$sLinkShipping; ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_edit')); ?></a>)
                    </h4>
                    <?php
                    $oShipping = $oBasket->GetActiveShippingGroup();
                    echo TGlobal::OutHTML($oShipping->fieldName);
                    ?>
                </div>
            </div>


        </div>
    </div>
    <div class="span9">
		<div class="row ConfirmStepInfoBlock">
			<div class="span4 pull-left">
				<div class="shippingConfirmStepInfoBlock ConfirmStepInfoBlockBox" style="margin-bottom: 0px; float: none;">
					<div class="shippingadr innerBox">
						<h4><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.extranet.shipping_address')); ?>
							(<a href="<?=$sLinkShippingAddress; ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_edit')); ?></a>)
						</h4>
						<?php
                            if (null === $amazonConfig) {
                                echo 'error loading amazon widget';
                            } else {
                                $oViewRenderer = new ViewRenderer();
                                $oViewRenderer->AddMapper(new \ChameleonSystem\AmazonPaymentBundle\mappers\AmazonWidgetMapper());
                                $oViewRenderer->AddSourceObject('basket', $oBasket);
                                $oViewRenderer->AddSourceObject('config', $amazonConfig);
                                echo $oViewRenderer->Render('/pkgshoppaymentamazon/widgets/address-read-only.html.twig');
                            }
                        ?>
					</div>
					</div>
			</div>

			<div class="span4 pull-right">
				<div class="billingConfirmStepInfoBlock ConfirmStepInfoBlockBox" style="margin-bottom: 0px; float: none;">
					<div class="shippingtype innerBox">
						<h4><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_payment_type')); ?>
							(<a href="<?=$sLinkShipping; ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_edit')); ?></a>)
						</h4>
						<?php
                            $oPaymentMethod = $oBasket->GetActivePaymentMethod();
                            if (null === $amazonConfig) {
                                echo 'error loading amazon widget';
                            } else {
                                $oViewRenderer = new ViewRenderer();
                                $oViewRenderer->AddMapper(new \ChameleonSystem\AmazonPaymentBundle\mappers\AmazonWidgetMapper());
                                $oViewRenderer->AddSourceObject('basket', $oBasket);
                                $oViewRenderer->AddSourceObject('config', $amazonConfig);
                                echo $oViewRenderer->Render('/pkgshoppaymentamazon/widgets/wallet-read-only.html.twig');
                            }

                        ?>
					</div>
				</div>
			</div>
		</div>
        <?php
        $oViewRenderer = new ViewRenderer();
        $oViewRenderer->addMapperFromIdentifier('chameleon_system_shop_currency.mapper.shop_currency_mapper');
        $oViewRenderer->AddMapper(new TPkgShopBasketMapper_BasketSummary());
        $oViewRenderer->AddMapper(new TPkgShopBasketMapper_BasketItems());
        $oViewRenderer->addMapperFromIdentifier('chameleon_system_shop.mapper.system_page_links');
        $oViewRenderer->AddSourceObject('oBasket', $oBasket);
        echo $oViewRenderer->Render('/pkgShop/shopBasket/shopBasketCheckoutConfirmStep.html.twig');
        ?>
        <div class="row">
            <form name="user" method="post" accept-charset="UTF-8" action="<?=$oStep->GetStepURL(); ?>">
                <input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($sSpotName); ?>]" value="ExecuteStep"/>
                <input type="hidden" class="cmsaction" name="<?=MTShopOrderWizardCore::URL_PARAM_STEP_METHOD; ?>" value=""/>

                <div class="span6">
                    <?php if ($bShowNewsletterSignup) {
            ?>
                    <div class="newsletter">
                        <div class="control-group">
                            <div class="controls">
                                <label class="checkbox"><input type="checkbox" value="1" <?php if ($newsletter) {
                echo 'checked="checked"';
            } ?> name="aInput[newsletter]"/> <?=TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_newsletter'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php
        } ?>
                    <div class="agb confirmStepAgb">
                        <?php
                        $sAGBLink = $oShop->GetLinkToSystemPageAsPopUp(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_agb'), 'agb-popup', null, false, 750);
                        $sText = 'chameleon_system_theme_shop_standard.checkout.confirm_agb_block';
                        ?>
                        <div class="control-group">
                            <div class="controls">
                                <label class="checkbox"><input type="checkbox" value="true" name="aInput[agb]"/> <?=TGlobal::Translate($sText, array('%sAGB%' => $sAGBLink)); ?>
                                </label>
                            </div>
                        </div>
                        [{CMSMSG-<?=MTShopBasketCore::MSG_CONSUMER_NAME; ?>-agb}]
                        <?php
                            echo $oPaymentMethod->renderConfirmOrderBlock($oUser);
                        ?>
                    </div>
                </div>
                <div class="span3">
                    <button id="primarypaymentbutton" class="btn btn-large btn-success btn-block" type="submit"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_order'));?></button>
                    <div class="cssFont5">
                        (<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.confirm_info_mail'));?>)
                    </div>
                </div>
            </form>
        </div>

    </div>

</div>

