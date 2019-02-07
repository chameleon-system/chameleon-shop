<div class="card CMSInterfacePopup">
    <div class="card-header">
        Article search index
    </div>
    <div class="card-body">
        <form name="indexer" method="post" action="<?=PATH_CMS_CONTROLLER; ?>" accept-charset="UTF-8">
            <input type="hidden" name="pagedef" value="<?=TGlobal::OutHTML($data['pagedef']); ?>"/>
            <input type="hidden" name="_pagedefType" value="<?= $data['_pagedefType']; ?>"/>
            <input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($data['sModuleSpotName']); ?>]"
                   value="TickerIndexGeneration"/>

            <?php if (!$data['bIndexIsRunning'] || $data['bIndexCompleted']) {
                ?>
                <input class="btn btn-primary" type="submit" value="generate"/>
                <?php
            } ?>
        </form>
        <?php
        $oLocal = &TCMSLocal::GetActive();
        ?>
        <div class="mt-4">Completed: <?=$oLocal->FormatNumber($data['bPercentDone'], 2); ?>%</div>

        <?php if (!$data['bIndexCompleted'] && $data['bIndexIsRunning']) {
            ?>
            <script>
                function SubmitCMSIndex() {
                    document.indexer.submit();
                }
                setTimeout("SubmitCMSIndex()", 1000);
            </script>
            <?php
        } ?>
    </div>
</div>
