<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface IPkgShopBasketArticleWithCustomData
{
    /**
     * validates the custom data.
     *
     * @return TShopBasketArticleCustomDataValidationError[]
     */
    public function validateCustomData(array $customData);

    /**
     * return true if the item may only be purchased if custom data is present (and valid).
     *
     * @return bool
     */
    public function requiresCustomData();

    /**
     * return true if the article is configurable (and therefor accepts custom data). Since any extension of the TShopBasketArticle class
     * affects every article we need each article to be able to decided if it is a configurable article or not.
     *
     * @return bool
     */
    public function isConfigurableArticle();

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
}
