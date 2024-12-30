<?php
/** @var $oStep TdbShopOrderStep */
/** @var $sSpotName string */
/** @var $aCallTimeVars array */

/** @var $oStepNext TdbShopOrderStep */
/** @var $oStepPrevious TdbShopOrderStep */
/** @var $sBackLink string */
$sDescription = $oStep->GetDescription();

$oMessageManager = TCMSMessageManager::GetInstance();

?>
<div class="TShopOrderStep TShopStepLogin">
    <div class="vLogin">
        <div class="column716">
            <div class="stepContent">
                <?php
                if ($oMessageManager->ConsumerHasMessages(TShopStepShipping::MSG_PAYMENT_METHOD)) {
                    echo $oMessageManager->RenderMessages(TShopStepShipping::MSG_PAYMENT_METHOD);
                }
                ?>
                <?php
                echo $sWizardNavi = $aCallTimeVars['oSteps']->Render('naviBefore', 'Customer', $sSpotName);
                ?>
                <div class="box">
                    <div
                        class="block"><?php include dirname(__FILE__).'/parts/vRegisterAsNewCustomer.view.php'; ?></div>
                    <div class="block"><?php include dirname(__FILE__).'/parts/vLoginUser.view.php'; ?></div>
                    <div class="block"><?php include dirname(__FILE__).'/parts/vGuest.view.php'; ?></div>
                    <br/>

                    <div class="steps">
                        <?php if ($sBackLink) {
                    ?><a href="<?=$sBackLink; ?>"
                                                     class="buttonBack buttonLeft"><?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_checkout.login_action_previous_step')); ?></a><?php
                } ?>
                        <div class="cleardiv">&nbsp;</div>
                    </div>

                </div>
                <?php
                echo $sWizardNavi = $aCallTimeVars['oSteps']->Render('naviAfter', 'Customer', $sSpotName);
                ?>
            </div>
        </div>

        <div class="column226">
            <div class="stepInfo">
                <?php if (!empty($aCallTimeVars['sWizardNavi'])) {
                    ?>
                <div class="box226 box226_white">
                    <div class="box226_header box_header"><img class="box_header_icon"
                                                               src="/static/images/icons/ordersteps.png"
                                                               alt="<?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_checkout.navi_header')); ?>"/><span><?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_checkout.navi_header')); ?></span>
                    </div>
                    <div class="box226_main box_main">
                        <div class="box_padding">
                            <?=$aCallTimeVars['sWizardNavi']; ?>
                        </div>
                    </div>
                    <div class="box226_footer box_footer"></div>
                </div>
                <?php
                } ?>

                <div class="cleardiv"></div>

                <div class="box226 box226_white">
                    <div class="box226_header box_header"><img class="box_header_icon"
                                                               src="/static/images/icons/notice.gif"
                                                               alt="<?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_checkout.help_header')); ?>"/><span><?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_checkout.help_header')); ?></span>
                    </div>
                    <div class="box226_main box_main">
                        <div class="box_padding">
                            <?php if (!empty($sDescription)) {
                    echo "<div class=\"stepdesc\">{$sDescription}</div>";
                } ?>
                        </div>
                    </div>
                    <div class="box226_footer box_footer"></div>
                </div>

            </div>
        </div>

    </div>
</div>

