<h1>Build #1520932193</h1>
<h2>Date: 2018-03-13</h2>
<div class="changelog">
    - Use service IDs for cronjobs.
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
  ->setFields([
      'cron_class' => 'chameleon_system_shop_rating_service.cronjob.import_ratings_cronjob',
  ])
  ->setWhereEquals([
      'cron_class' => 'TPkgShopRating_CronJob_ImportRating',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
  ->setFields([
      'cron_class' => 'chameleon_system_shop_rating_service.cronjob.send_rating_emails_cronjob',
  ])
  ->setWhereEquals([
      'cron_class' => 'TPkgShopRating_CronJob_SendRatingMails',
  ])
;
TCMSLogChange::update(__LINE__, $data);
