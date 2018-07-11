<h1>Build #1528203546</h1>
<h2>Date: 2018-06-05</h2>
<div class="changelog">
    - Fix placeholder for product name.
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_message_manager_message', 'en')
  ->setFields([
      'message' => 'Only [{dStockAvailable}] pieces of [{shop_article__name}] can be ordered. Your order has been adjusted accordingly.',
  ])
  ->setWhereEquals([
      'name' => 'ERROR-ADD-TO-BASKET-NOT-ENOUGH-STOCK',
  ])
;
TCMSLogChange::update(__LINE__, $data);
