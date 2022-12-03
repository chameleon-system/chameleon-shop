<h1>Build #1546853355</h1>
<h2>Date: 2019-01-07</h2>
<div class="changelog">
    - add crop to image field of hotspot item
</div>
<?php
TCMSLogChange::requireBundleUpdates('ChameleonSystemImageCropBundle', 1534864767);

$databaseConnection = TCMSLogChange::getDatabaseConnection();

$fieldConfigImageField = $databaseConnection->fetchAssociative(
    'SELECT * FROM `cms_field_conf` WHERE `cms_tbl_conf_id` = ? AND `name` = ?',
    [TCMSLogChange::GetTableId('pkg_image_hotspot_item'), 'cms_media_id']
);
if (false === $fieldConfigImageField) {
    TCMSLogChange::addInfoMessage(
        'Field `cms_media_id` in table `pkg_image_hotspot_item` is missing.',
        TCMSLogChange::INFO_MESSAGE_LEVEL_ERROR
    );

    return;
}

if (TCMSLogChange::GetFieldType('CMSFIELD_EXTENDEDTABLELIST_MEDIA') !== $fieldConfigImageField['cms_field_type_id']) {
    TCMSLogChange::addInfoMessage(
        'Field type of `cms_media_id` in table `pkg_image_hotspot_item` was apparently changed, please change type to CMSFIELD_EXTENDEDTABLELIST_MEDIA_CROP manually and make sure to keep any custom code. Restrict field to crop preset `pkgImageHotspotItemBackground`.',
        TCMSLogChange::INFO_MESSAGE_LEVEL_ERROR
    );

    return;
}

$query = 'ALTER TABLE `pkg_image_hotspot_item` ADD `cms_media_id_image_crop_id` CHAR(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
    ->setFields(
        [
            'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_EXTENDEDTABLELIST_MEDIA_CROP'),
            'fieldtype_config' => 'bShowCategorySelector=1
imageCropPresetSystemName=pkgImageHotspotItemBackground
imageCropPresetRestrictionSystemNames=pkgImageHotspotItemBackground',
        ]
    )
    ->setWhereEquals(
        [
            'id' => $fieldConfigImageField['id'],
        ]
    );
TCMSLogChange::update(__LINE__, $data);

$query = 'ALTER TABLE `pkg_image_hotspot_item` ADD INDEX ( `cms_media_id_image_crop_id` )';
TCMSLogChange::RunQuery(__LINE__, $query);

$highestPosition = (int) $databaseConnection->fetchColumn('SELECT MAX(`position`) FROM `cms_image_crop_preset`');
$newPresetId = TCMSLogChange::createUnusedRecordId('cms_image_crop_preset');

$data = TCMSLogChange::createMigrationQueryData('cms_image_crop_preset', 'en')
    ->setFields(
        [
            'name' => 'Presenter with hotspots: Background image',
            'width' => '1170',
            'height' => '385',
            'system_name' => 'pkgImageHotspotItemBackground',
            'position' => $highestPosition++,
            'id' => $newPresetId,
        ]
    );
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_image_crop_preset', 'de')
    ->setFields(
        [
            'name' => 'Presenter mit Hotspots: Hintergrundbild',
        ]
    )
    ->setWhereEquals(['id' => $newPresetId]);
TCMSLogChange::update(__LINE__, $data);
