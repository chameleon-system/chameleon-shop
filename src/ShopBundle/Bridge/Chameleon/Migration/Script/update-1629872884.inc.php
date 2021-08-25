<h1>Build #1629872884</h1>
<h2>Date: 2021-08-25</h2>
<div class="changelog">
    - ref #584: Change voucher message if yet unchanged
</div>
<?php

$connection = TCMSLogChange::getDatabaseConnection();

$id = $connection->fetchOne("SELECT `id` FROM `cms_message_manager_message` WHERE `message` =
'Für die Verwendung dieses Gutscheins ist ein Mindestbestellwert von [{TdbShopVoucherSeries__fieldRestrictToValueFormated}] erforderlich. Bitte beachten Sie, dass buchpreisgebundene Artikel nur bei gesponserten Gutscheinen zum Warenwert gezählt werden.'
OR `message` =
'A minimum order value of [{TdbShopVoucherSeries__fieldRestrictToValueFormated}] is required to use this voucher. Please note that articles with a fixed retail prices such as books are added to the value of goods only when using a sponsored voucher.'
");

if (false !== $id) {
    $data = TCMSLogChange::createMigrationQueryData('cms_message_manager_message', 'de')
        ->setFields([
            'message' => 'Für die Verwendung dieses Gutscheins ist ein Mindestbestellwert von [{TdbShopVoucherSeries__fieldRestrictToValueFormated}] erforderlich. Bitte beachten Sie, dass buchpreisgebundene Artikel nur bei gekauften Gutscheinen zum Warenwert gezählt werden. Auf Aktionsgutscheine können buchpreisgebundene Artikel leider nicht berücksichtigt werden.',
        ])
        ->setWhereEquals([
            'id' => $id,
        ])
    ;
    TCMSLogChange::update(__LINE__, $data);

    $data = TCMSLogChange::createMigrationQueryData('cms_message_manager_message', 'en')
        ->setFields([
            'message' => 'For the use of this voucher a minimum order value of [{TdbShopVoucherSeries__fieldRestrictToValueFormated}] is required. Please note that book-price-bound articles are only counted towards the value of goods in the case of purchased vouchers. Unfortunately, book-price related articles cannot be included in promotion vouchers.',
        ])
        ->setWhereEquals([
            'id' => $id,
        ])
    ;
    TCMSLogChange::update(__LINE__, $data);
}
