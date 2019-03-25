<h1>Build #1553528290</h1>
<h2>Date: 2019-03-25</h2>
<div class="changelog">
    - Remove TCMSSmartURLHandler_ShopProduct
</div>
<?php

$databaseConnection = TCMSLogChange::getDatabaseConnection();

$urlHandlerId = $databaseConnection->fetchColumn("SELECT `id` FROM `cms_smart_url_handler` WHERE `name` = 'TCMSSmartURLHandler_ShopProduct'");
if (false === $urlHandlerId) {
    return;
}

$data = TCMSLogChange::createMigrationQueryData('cms_smart_url_handler_cms_portal_mlt', 'en')
  ->setWhereEquals([
      'source_id' => $urlHandlerId,
  ])
;
TCMSLogChange::delete(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_smart_url_handler', 'en')
  ->setWhereEquals([
      'id' => $urlHandlerId,
  ])
;
TCMSLogChange::delete(__LINE__, $data);

