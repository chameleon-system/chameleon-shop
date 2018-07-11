<?php
/** @var $oShop TdbShop */
/** @var $oUser TdbDataExtranetUser */
/** @var $oExtranetConfig TdbDataExtranet */
/** @var $umode string - the user mode (register, guest...) */

/** @var $oStep TdbShopOrderStep */
/** @var $sSpotName string */
/** @var $aCallTimeVars array */

/** @var $oStepNext TdbShopOrderStep */
/** @var $oStepPrevious TdbShopOrderStep */
/** @var $sBackLink string */
$sDescription = $oStep->GetDescription();

$oMessageManager = TCMSMessageManager::GetInstance();
?>
<div class="TShopOrderStep TShopStepUserData">
    <div class="user">
        <script type="text/javascript">
            function SetSelectedAddress(sAddressId, sAddressName) {
                document.user.<?=MTShopOrderWizardCore::URL_PARAM_STEP_METHOD; ?>.value = 'ChangeSelectedAddress';
                document.user.submit();
            }
        </script>
        <div class="column716">
            <div class="stepContent">
                <?php
                echo $sWizardNavi = $aCallTimeVars['oSteps']->Render('naviBefore', 'Customer', $sSpotName);
                ?>
                <div class="box">
                    <form name="user" method="post" accept-charset="UTF-8" action="<?=$oStep->GetStepURL(); ?>">
                        <input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($sSpotName); ?>]" value="ExecuteStep"/>
                        <input type="hidden" name="<?=MTShopOrderWizardCore::URL_PARAM_STEP_METHOD; ?>" value=""/>

                        <?php
                        if (!$oUser->IsLoggedIn()) {
                            if ('register' == $umode) {
                                // register new user
                                require dirname(__FILE__).'/parts/register-user.inc.php';
                            } else {
                                // order as guest
                                require dirname(__FILE__).'/parts/guest-user.inc.php';
                            }
                        } else {
                            // edit existing data
                            require dirname(__FILE__).'/parts/update-user.inc.php';
                        }
                        ?>
                        <input type="submit" value="subit"/>
                    </form>

                    <?php echo TShopBasket::GetInstance()->Render('vTelephoneOrderForm', 'Customer'); ?>
                    <?php
                    echo $sWizardNavi = $aCallTimeVars['oSteps']->Render('naviAfter', 'Customer', $sSpotName);
                    ?>
                </div>
            </div>
        </div>
        <div class="column226">
            <div class="stepInfo">

                <?php if (!empty($aCallTimeVars['sWizardNavi'])) {
                        ?>
                <div class="box226 box226_white">
                    <div class="box226_header box_header"><img class="box_header_icon"
                                                               src="/static/images/icons/ordersteps.png"
                                                               alt="<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.module_checkout.navi_header')); ?>"/><span><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.module_checkout.navi_header')); ?></span>
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
                                                               alt="<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.module_checkout.help_header')); ?>"/><span><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.module_checkout.help_header')); ?></span>
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