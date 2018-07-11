<br/>
<div class="module-shop-statistic">
    <div class="standard">

        <div class="module-headline"><strong><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.headline')); ?></strong></div>
        <div class="filter">
            <form method="post" action="">
                <label><input type="checkbox" value="1"
                              name="<?=TGlobal::GetModuleURLParameter('bShowChange'); ?>" <?php if ($bShowChange) {
    echo 'checked="checked"';
}?> /> <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_show_change')); ?>
                </label><br/><br/>
                <?php if (count($portalList) > 1) {
    ?>
                    <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_portal')); ?>:
                    <select name="<?=TGlobal::GetModuleURLParameter('portalId'); ?>">
                        <option value="" <?php if ('' == $selectedPortalId) {
        echo 'selected';
    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.all_portals')); ?></option>
                        <?php foreach ($portalList as $portalId => $portalName) {
        ?>
                            <option value="<?=$portalId; ?>" <?php if ($selectedPortalId == $portalId) {
            echo 'selected';
        } ?> ><?=TGlobal::OutHTML($portalName); ?></option>
                        <?php
    } ?>
                    </select>
                <?php
} ?>
                <br />
                Gruppieren nach:
                <select name="<?=TGlobal::GetModuleURLParameter('sDateGroupType'); ?>">
                    <option
                        value="year" <?php if ('year' == $sDateGroupType) {
        echo 'selected="selected"';
    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.date_year')); ?></option>
                    <option
                        value="day" <?php if ('day' == $sDateGroupType) {
        echo 'selected="selected"';
    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.date_day')); ?></option>
                    <option
                        value="month" <?php if ('month' == $sDateGroupType) {
        echo 'selected="selected"';
    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.date_month')); ?></option>
                    <option
                        value="week" <?php if ('week' == $sDateGroupType) {
        echo 'selected="selected"';
    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.date_week')); ?></option>
                </select>



                <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_date_from')); ?>: <input type="text"
                                                                         name="<?=TGlobal::GetModuleURLParameter('sStartDate'); ?>"
                                                                         value="<?=TGlobal::OutHTML($sStartDate); ?>"/>
                <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_date_to')); ?>: <input type="text"
                                                                         name="<?=TGlobal::GetModuleURLParameter('sEndDate'); ?>"
                                                                         value="<?=TGlobal::OutHTML($sEndDate); ?>"/>

                <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_output_type')); ?>:
                <select name="<?=TGlobal::GetModuleURLParameter('sViewName'); ?>">
                    <option
                        value="html.table" <?php if ('html.table' == $sViewName) {
        echo 'selected="selected"';
    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_output_type_table')); ?></option>
                    <option
                        value="html.barchart" <?php if ('html.barchart' == $sViewName) {
        echo 'selected="selected"';
    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_output_type_chart')); ?></option>
                </select>

                <input type="submit" name="subit" value="<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.action_show')); ?>"/>

        </div>
        <br/>
        <?php
        $oGlobal = TGlobal::instance();
        $aParamter = $oGlobal->GetUserData(null, array('module_fnc'));
        $aParamter['module_fnc'] = array($sModuleSpotName => 'GetAsCSV');
        $sURL = TTools::GetArrayAsURL($aParamter);

        $aTopSellerParameter = $aParamter;
        $aTopSellerParameter['module_fnc'] = array($sModuleSpotName => 'DownloadTopsellers');
        $sTopSellerURL = TTools::GetArrayAsURL($aTopSellerParameter);

        ?>


        <ul class="actionLinks">
            <li><a href="javascript:void()"
                   onclick="window.print()"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.action_print')); ?></a></li>
            <li><a href="?<?=$sURL; ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.action_download')); ?></a></li>
            <li><a href="?<?=$sTopSellerURL; ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.action_download_top_seller'));?></a>
            </li>
        </ul>

        </form>

        <br/>
        <br/>
        <?php
        echo $oStats->Render($sViewName);
        ?>
    </div>
</div>
