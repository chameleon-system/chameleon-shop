<h1>pkgnewsletter - Build #1527065005</h1>
<div class="changelog">
    - turn mappers into services
</div>
<?php

$mappers = array(
    'MTMyAccountMapper_Newsletter' => 'chameleon_system_shop.mapper.my_account.my_account_mapper_newsletter',
    'MTShopViewMyOrderDetailsOrderObjectAsStringMapper' => 'chameleon_system_shop.mapper.view_my_order_details.view_my_order_details_order_object_as_string',
    'TPkgShopMapper_ShopAddress' => 'chameleon_system_shop.mapper.shop_address',
    'TPkgShopMapper_ShopCentralHandler' => 'chameleon_system_shop.mapper.shop_central_handler',
    'TPkgShopMapper_SystemPageLinks' => 'chameleon_system_shop.mapper.system_page_links',
    'TPkgShopManufacturerMapper_Intro' => 'chameleon_system_shop.mapper.manufacturer_intro',
    'TPkgShopManufacturerMapper_Overview' => 'chameleon_system_shop.mapper.manufacturer_overview',
    'TPkgShopManufacturerMapper_OverviewNavigation' => 'chameleon_system_shop.mapper.manufacturer_overview_navigation',
    'TPkgShopMapper_ArticleListHeader' => 'chameleon_system_shop.mapper.list.article_list_header',
    'TPkgShopMapper_ArticleListOrderBy' => 'chameleon_system_shop.mapper.list.article_list_order_by',
    'TPkgShopMapper_ArticleListPager' => 'chameleon_system_shop.mapper.list.article_list_pager',
    'TPkgShopMapper_ArticleListResultInfo' => 'chameleon_system_shop.mapper.list.article_list_result_info',
    'TPkgShopMapper_ArticleRemoveNoticeList' => 'chameleon_system_shop.mapper.notice_list.article_remove',
    'TPkgShopMapper_ArticleToNoticeList' => 'chameleon_system_shop.mapper.notice_list.article_add',
    'TPkgShopMapper_Order' => 'chameleon_system_shop.mapper.order.order',
    'TPkgShopMapper_OrderArticleList' => 'chameleon_system_shop.mapper.order.order_article_list',
    'TPkgShopMapper_OrderArticleListSummary' => 'chameleon_system_shop.mapper.order.order_article_list_summary',
    'TPkgShopMapper_OrderPayment' => 'chameleon_system_shop.mapper.order.order_payment',
    'TPkgShopMapper_OrderUserData' => 'chameleon_system_shop.mapper.order.order_user_data',
    'TPkgShopMapper_OrderCompleted' => 'chameleon_system_shop.mapper.orderwizard.order_completed',
    'TPkgShopMapper_OrderStep' => 'chameleon_system_shop.mapper.orderwizard.order_step',
    'TPkgShopMapper_PaymentList' => 'chameleon_system_shop.mapper.orderwizard.payment_list',
    'TPkgShopMapperOrderwizard_AddressSelection' => 'chameleon_system_shop.mapper.orderwizard.address_selection',
    'TPkgShopMapper_ShippingGroupList' => 'chameleon_system_shop.mapper.shipping_group.shipping_group_list',
    'ChameleonSystem\ShopBundle\mappers\social\TPkgShopMapper_SocialSharePrivacy' => 'chameleon_system_shop.mapper.social.social_share_privacy',
    'ChameleonSystem\ShopBundle\objects\ArticleList\Mapper\ArticleListLegacyMapper' => 'chameleon_system_shop.mapper.objects.article_list.article_list_legacy',
    'ChameleonSystem\ShopBundle\objects\ArticleList\Mapper\ItemMapperNoticeList' => 'chameleon_system_shop.mapper.objects.article_list.item_mapper_notice_list',
    'ChameleonSystem\ShopBundle\objects\ArticleList\Mapper\ItemMapperStandard' => 'chameleon_system_shop.mapper.objects.article_list.item_mapper_standard',
);

$databaseConnection = TCMSLogChange::getDatabaseConnection();
$statement = $databaseConnection->executeQuery("SELECT `classname` FROM `cms_tpl_module` WHERE `view_mapper_config` != '' OR `mapper_chain` != ''");
if (false === $statement->execute()) {
    return;
}

while (false !== $row = $statement->fetch(PDO::FETCH_NUM)) {
    $moduleManager = TCMSLogChange::getModuleManager($row[0]);

    $mapperConfig = $moduleManager->getMapperConfig();
    $hasChanges = false;
    foreach ($mappers as $oldMapper => $newMapper) {
        $hasChanges = $mapperConfig->replaceMapper($oldMapper, $newMapper) || $hasChanges;
        $hasChanges = $mapperConfig->replaceMapper('\\'.$oldMapper, $newMapper) || $hasChanges;
    }
    if (true === $hasChanges) {
        $moduleManager->updateMapperConfig($mapperConfig);
    }

    foreach ($mappers as $oldMapper => $newMapper) {
        $moduleManager->replaceMapperInMapperChain($oldMapper, $newMapper);
        $moduleManager->replaceMapperInMapperChain('\\'.$oldMapper, $newMapper);
    }
}
