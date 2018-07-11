<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

if (!class_exists('\TdbShopArticle')) {
    class TdbShopArticle
    {
        public $id;
        public $table;
        public $iLanguageId;
        public $dAmount;
        public $dPriceTotal;
        public $dPriceAfterDiscount;
        public $dPriceTotalAfterDiscount;
        public $dPriceAfterDiscountWithoutVouchers;
        public $dPriceTotalAfterDiscountWithoutVouchers;
        public $dTotalWeight;
        public $dTotalVolume;
        public $sBasketItemKey;
    }
}

class TShopBasketArticleCoreTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_keep_custom_data_even_when_serializing_and_unserializing()
    {
        $testCustomData = array('custom' => 'data');
        $basketArticle = new TShopBasketArticleCore();

        $basketArticle->setCustomData($testCustomData);

        $basketArticleSerialized = serialize($basketArticle);

        $unserializedArticle = unserialize($basketArticleSerialized);

        $this->assertEquals($testCustomData, $unserializedArticle->getCustomData());
    }
}
