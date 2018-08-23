<div class="row">
	<div class="amazonSelectAddress span6">
		<?php
        if (null === $amazonConfig) {
            echo 'error loading amazon widget';
        } else {
            $oViewRenderer = new ViewRenderer();
            $oViewRenderer->AddMapper(new \ChameleonSystem\AmazonPaymentBundle\mappers\AmazonWidgetMapper());
            $oViewRenderer->AddSourceObject('basket', $oBasket);
            $oViewRenderer->AddSourceObject('config', $amazonConfig);
            echo $oViewRenderer->Render('/pkgshoppaymentamazon/widgets/address.html.twig');
        }
        ?>
	</div>
	<div class="span6">
		<?php
        echo $oStep->fieldDescription;
        ?>
	</div>
	<div class="span12" style="margin-top: 20px;">
		<form name="address" method="post" accept-charset="UTF-8" action="<?=$oStep->GetStepURL(); ?>" class="form-horizontal">
			<input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($sSpotName); ?>]" value="ExecuteStep"/>

			<div class="control-group">
				<div>
					<div class="row">
						<div class="span2 pull-left">
							<a href="<?=$sBackLink; ?>" class="btn btn-large btn-block"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.user_previous_step')); ?></a>
						</div>
						<div class="span2 pull-right">
							<button id="primarypaymentbutton" disabled="disabled" type="submit" class="btn btn-large btn-success btn-block" ><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_theme_shop_standard.checkout.user_next_step')); ?></button>
						</div>
					</div>
				</div>
			</div>

		</form>
	</div>
</div>
