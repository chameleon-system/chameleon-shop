<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Interfaces\DataAccess;

/**
 * access the stock message configuration.
 */
interface ShopStockMessageDataAccessInterface
{
    /**
     * Return the stock message in the specified language, or null if the message could not be loaded.
     *
     * @param string $id
     * @param string $languageId
     *
     * @return \TdbShopStockMessage|null
     */
    public function getStockMessage($id, $languageId);

    /**
     * Return all stock messages.
     *
     * @return array
     */
    public function getAll();
}
