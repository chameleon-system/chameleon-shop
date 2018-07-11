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
    const ARTICLE_LIST_FILTER_EXECUTED = 'chameleon_system_shop.article_list.result_generated';

    const BASKET_UPDATE_ITEM = 'chameleon_system_shop.basket_update_item';
    const BASKET_DELETE_ITEM = 'chameleon_system_shop.basket_delete_item';
    const BASKET_CLEAR = 'chameleon_system_shop.basket_clear';
    /**
     * Event is dispatched after a product's stock was updated.
     */
    const UPDATE_PRODUCT_STOCK = 'chameleon_system_shop.update_product_stock';
}
