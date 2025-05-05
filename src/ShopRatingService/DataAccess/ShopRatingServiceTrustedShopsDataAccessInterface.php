<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopRatingService\DataAccess;

interface ShopRatingServiceTrustedShopsDataAccessInterface
{
    /**
     * @param string $remoteKey
     *
     * @return int
     */
    public function getItemCountForRemoteKey($remoteKey);

    /**
     * @param array<string, mixed> $data
     *
     * @psalm-param array{
     *     insertId: string,
     *     pkgShopRatingServiceId: string,
     *     remoteKey: string,
     *     score: int|'',
     *     rawData: string,
     *     ratingUser: string,
     *     ratingText: string,
     *     ratingDate: string,
     * } $data
     *
     * @return void
     */
    public function insertItem(array $data);
}
