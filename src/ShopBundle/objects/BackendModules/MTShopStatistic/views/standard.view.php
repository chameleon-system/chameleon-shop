<br/>
<div class="module-shop-statistic">
    <div class="card standard">
        <div class="card-header">
            <h3 class="mb-0"><i class="fas fa-chart-pie mr-2"></i><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.headline')); ?></h3>
        </div>
        <div class="card-body">
            <div class="filters">
                <form method="post" action="">
                    <div class="d-flex align-items-center mb-4">
                        <label class="switch switch-label switch-pill switch-success mb-0 mr-2">
                            <input class="switch-input" type="checkbox" value="1" name="<?=TGlobal::GetModuleURLParameter('bShowChange'); ?>" <?php if ($bShowChange) { echo 'checked="checked"'; }?> />
                            <span class="switch-slider" data-checked="✓" data-unchecked="✕"></span>
                        </label>
                        <span><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_show_change')); ?></span>
                    </div>

                    <?php if (count($portalList) > 1) {
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_portal')); ?>:
                                    </label>
                                    <select class="form-control" name="<?=TGlobal::GetModuleURLParameter('portalId'); ?>">
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
                                </div>
                            </div>
                        </div>
                        <?php
                    } ?>

                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_output_type')); ?>:
                                </label>
                                <select class="form-control" name="<?=TGlobal::GetModuleURLParameter('sViewName'); ?>">
                                    <option
                                            value="html.table" <?php if ('html.table' == $sViewName) {
                                        echo 'selected="selected"';
                                    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_output_type_table')); ?></option>
                                    <option
                                            value="html.barchart" <?php if ('html.barchart' == $sViewName) {
                                        echo 'selected="selected"';
                                    } ?>><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_output_type_chart')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Gruppieren nach:
                                </label>
                                <select class="form-control" name="<?=TGlobal::GetModuleURLParameter('sDateGroupType'); ?>">
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
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_date_from')); ?>:
                                </label>
                                <div>
                                    <input class="form-control" type="text" name="<?=TGlobal::GetModuleURLParameter('sStartDate'); ?>" value="<?=TGlobal::OutHTML($sStartDate); ?>"/>
                                </div>

                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.form_date_to')); ?>:
                                </label>

                                <input class="form-control" type="text" name="<?=TGlobal::GetModuleURLParameter('sEndDate'); ?>" value="<?=TGlobal::OutHTML($sEndDate); ?>"/>
                            </div>
                        </div>
                    </div>
                    <input class="btn btn-primary" type="submit" name="submit" value="<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.action_show')); ?>"/>
                </form>
            </div>

            <?php
            $oGlobal = TGlobal::instance();
            $aParamter = $oGlobal->GetUserData(null, array('module_fnc'));
            $aParamter['module_fnc'] = array($sModuleSpotName => 'GetAsCSV');
            $sURL = TTools::GetArrayAsURL($aParamter);

            $aTopSellerParameter = $aParamter;
            $aTopSellerParameter['module_fnc'] = array($sModuleSpotName => 'DownloadTopsellers');
            $sTopSellerURL = TTools::GetArrayAsURL($aTopSellerParameter);

            ?>

            <div class="actionLinks d-flex mt-4 flex-column flex-sm-row">
                <a class="btn btn-secondary mt-1 mr-sm-1" href="javascript:void()" onclick="window.print()"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.action_print')); ?></a>
                <a class="btn btn-secondary mt-1 mr-sm-1" href="?<?=$sURL; ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.action_download')); ?></a>
                <a class="btn btn-secondary mt-1 mr-sm-1" href="?<?=$sTopSellerURL; ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.action_download_top_seller')); ?></a>
            </div>

            <div class="statistics mt-4">
                <?php
                echo $oStats->Render($sViewName);
                ?>
            </div>
        </div>
    </div>
</div>
