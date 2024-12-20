<h1>Build #1734592870</h1>
<h2>Date: 2024-12-19</h2>
<div class="changelog">
    - #65248: remove social share privacy mapper from MTShopArticleDetails module
</div>
<?php

$dbConnection = TCMSLogChange::getDatabaseConnection();
$mapperToBeDeleted = [
    '\ChameleonSystem\ShopBundle\mappers\social\TPkgShopMapper_SocialSharePrivacy',
    'chameleon_system_shop.mapper.social.social_share_privacy',
];

$moduleManager = TCMSLogChange::getModuleManager('MTShopArticleDetails');
$mapperConfig = $moduleManager->getMapperConfig();
$hasChanges = false;
foreach ($mapperToBeDeleted as $mapper) {
    $hasChanges = $mapperConfig->replaceMapper($mapper, '') || $hasChanges;
    $hasChanges = $mapperConfig->replaceMapper('\\'.$mapper, '') || $hasChanges;
}

if (true === $hasChanges) {
    $moduleManager->updateMapperConfig($mapperConfig);
}

foreach ($mapperToBeDeleted as $mapper) {
    $moduleManager->replaceMapperInMapperChain($mapper, '');
    $moduleManager->replaceMapperInMapperChain('\\'.$mapper, '');
}
