<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle;

final class ShopEvents
{
    /**
     * Event is dispatched after an article list executed a filter.
     */
    public const ARTICLE_LIST_FILTER_EXECUTED = 'chameleon_system_shop.article_list.result_generated';

    public const BASKET_UPDATE_ITEM = 'chameleon_system_shop.basket_update_item';
    public const BASKET_DELETE_ITEM = 'chameleon_system_shop.basket_delete_item';
    public const BASKET_CLEAR = 'chameleon_system_shop.basket_clear';
    /**
     * Event is dispatched after a product's stock was updated.
     */
    public const UPDATE_PRODUCT_STOCK = 'chameleon_system_shop.update_product_stock';

    /**
     * called in TShopOrder::CreateOrderInDatabaseCompleteHook.
     */
    public const ORDER_SAVED = 'chameleon_system_shop.order_saved_in_database';

    /**
     * called in TShopOrder::ExportOrderForWaWiHook.
     */
    public const ORDER_SEND_TO_INVENTORY_MANAGEMENT = 'chameleon_system_shop.order_send_to_inventory_management';

    /**
     * called in TShopOrder::PostInsertHook.
     */
    public const ORDER_POST_INSERT = 'chameleon_system_shop.order_post_insert';

    /**
     * called in TShopOrder::PrePaymentExecuteHook.
     */
    public const ORDER_PRE_EXECUTED_PAYMENT = 'chameleon_system_shop.order_pre_executed_payment';

    /**
     * called in TShopOrder::ExecutePaymentHook.
     */
    public const ORDER_EXECUTED_PAYMENT = 'chameleon_system_shop.order_executed_payment';

    /**
     * called in TShopOrder::PreDeleteHook.
     */
    public const ORDER_PRE_DELETE = 'chameleon_system_shop.order_pre_delete';
}
