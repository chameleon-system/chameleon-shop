<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface IPkgShopOrderItemWithCustomData
{
    /**
     * return the twig template to use when displaying custom data.
     *
     * @return string
     */
    public function getCustomDataTwigTemplate();

    /**
     * @return array
     */
    public function getCustomDataForTwigOutput();

    /**
     * @return array
     */
    public function getCustomData();
}
