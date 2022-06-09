<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MTShopArticleListResponse
{
    /**
     * @var string|null
     */
    public $iListKey = null;

    /**
     * @var int
     * @psalm-var positive-int|0
     */
    public $iNumberOfResults = 0;

    /**
     * @var bool
     */
    public $bHasNextPage = false;

    /**
     * @var bool
     */
    public $bHasPreviousPage = false;

    /**
     * The rendered page.
     * @var string
     */
    public $sItemPage = '';
}
