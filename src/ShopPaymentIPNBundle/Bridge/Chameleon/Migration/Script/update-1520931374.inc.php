<h1>Build #1520931374</h1>
<h2>Date: 2018-03-13</h2>
<div class="changelog">
    - Use service IDs for cronjobs.
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
  ->setFields([
      'cron_class' => 'chameleon_system_shop_payment_ipn.cronjob.process_triggers_cronjob',
  ])
  ->setWhereEquals([
      'cron_class' => 'TPkgShopPaymentIPN_TCmsCronJob_ProcessTrigger',
  ])
;
TCMSLogChange::update(__LINE__, $data);
