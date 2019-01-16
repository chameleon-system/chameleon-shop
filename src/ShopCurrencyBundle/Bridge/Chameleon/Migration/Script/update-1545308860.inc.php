<h1>Build #1545308860</h1>
<h2>Date: 2018-12-20</h2>
<div class="changelog">
    -#234 correct currency in order history
</div>
<?php


$moduleManager = TCMSLogChange::getModuleManager('MTShopOrderHistory');
$mapperConfig = $moduleManager->getMapperConfig();
$mapperConfig->removeMapper('standard','chameleon_system_shop_currency.mapper.shop_currency_mapper');
$mapperConfig->addMapper('standard','chameleon_system_shop_currency.mapper.shop_currency_mapper');
$moduleManager->updateMapperConfig($mapperConfig);


