<h1>Build #1605192409</h1>
<h2>Date: 2020-09-25</h2>
<div class="changelog">
    - ref #636: Add a composite index key for faster requests
</div>
<?php

$query = 'ALTER TABLE `shop_order` ADD INDEX `stat_request_idx` (`canceled`, `datecreated`, `shop_payment_method_name`) USING BTREE;';
TCMSLogChange::RunQuery(__LINE__, $query);
