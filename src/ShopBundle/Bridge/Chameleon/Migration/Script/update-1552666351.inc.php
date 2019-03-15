<h1>Build #1552666351</h1>
<h2>Date: 2019-03-15</h2>
<div class="changelog">
    - fix name of image crop preset after field was made multilingual
</div>
<?php
TCMSLogChange::requireBundleUpdates('ChameleonSystemImageCropBundle', 1552663534);

$data = TCMSLogChange::createMigrationQueryData('cms_image_crop_preset', 'en')
    ->setFields(
        [
            'name' => 'Presenter with hotspots: Background image',
        ]
    )->setWhereEquals(['system_name' => 'pkgImageHotspotItemBackground']);
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_image_crop_preset', 'de')
    ->setFields(
        [
            'name' => 'Presenter mit Hotspots: Hintergrundbild',
        ]
    )->setWhereEquals(['system_name' => 'pkgImageHotspotItemBackground']);
TCMSLogChange::update(__LINE__, $data);