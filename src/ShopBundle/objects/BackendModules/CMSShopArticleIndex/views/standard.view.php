<?php
use ChameleonSystem\CoreBundle\ServiceLocator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @var TranslatorInterface $translator
 */
$translator = ServiceLocator::get('translator');
?>

<div class="card">
    <div class="card-header">
        <?= TGlobal::OutHTML($translator->trans('chameleon_system_shop.product_search_index.headline')); ?>
    </div>
    <div class="card-body">
        <form name="indexer" method="post" action="<?=PATH_CMS_CONTROLLER; ?>" accept-charset="UTF-8">
            <input type="hidden" name="pagedef" value="<?=TGlobal::OutHTML($data['pagedef']); ?>"/>
            <input type="hidden" name="_pagedefType" value="<?= $data['_pagedefType']; ?>"/>
            <input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($data['sModuleSpotName']); ?>]"
                   value="TickerIndexGeneration"/>

            <?php if (!$data['bIndexIsRunning'] || $data['bIndexCompleted']) {
    ?>
                <input class="btn btn-primary" type="submit" value="<?= TGlobal::OutHTML($translator->trans('chameleon_system_shop.product_search_index.generate')); ?>"/>
                <?php
} ?>
        </form>
        <?php
        $oLocal = &TCMSLocal::GetActive();
        ?>
        <div class="mt-4"><?= TGlobal::OutHTML($translator->trans('chameleon_system_shop.product_search_index.completed')); ?>: <?=$oLocal->FormatNumber($data['bPercentDone'], 2); ?>%</div>

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
