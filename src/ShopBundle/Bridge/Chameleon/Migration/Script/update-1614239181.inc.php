<h1>Build #1614239181</h1>
<h2>Date: 2021-02-25</h2>
<div class="changelog">
    - #584: Improve minimum voucher value message
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_message_manager_message', 'en')
  ->setFields([
      'message' => 'For the use of this voucher a minimum order value of [{TdbShopVoucherSeries__fieldRestrictToValueFormated}]  is required. Please note that book-price-bound articles are only counted towards the value of goods in the case of purchased vouchers. They cannot be included for promotion vouchers.',
  ])
  ->setWhereEquals([
      'name' => 'VOUCHER-ERROR-2',
  ])
;
TCMSLogChange::update(__LINE__, $data);

