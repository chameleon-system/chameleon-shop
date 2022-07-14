<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SearchBundle\Interfaces;

interface ShopSearchSessionInterface
{
    const SESSION_KEY = 'shop/searches';

    /**
     * @param array<string, mixed> $searchRequest
     * @return void
     */
    public function addSearch(array $searchRequest);

    /**
     * @param array<string, mixed> $searchRequest
     * @return bool
     */
    public function hasSearchedFor(array $searchRequest);
}
