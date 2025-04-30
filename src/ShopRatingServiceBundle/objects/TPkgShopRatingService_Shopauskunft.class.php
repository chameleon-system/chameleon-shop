<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopRatingService\Util\CacheUtil;

class TPkgShopRatingService_Shopauskunft extends TdbPkgShopRatingService
{
    /**
     * Import all ratings.
     *
     * @return bool
     */
    public function Import()
    {
        $cacheDirectoryPath = $this->getCacheUtil()->getCacheDirectory();
        $xmlFileOrData = null;
        if (null !== $cacheDirectoryPath) {
            $cacheFilePath = $cacheDirectoryPath.'ShopAuskunftAPI_data.xml';
            $this->downloadXml($cacheFilePath);
            if (file_exists($cacheFilePath)) {
                $xmlFileOrData = $cacheFilePath;
            }
        }
        if (null === $xmlFileOrData) {
            $xmlFileOrData = $this->fieldRatingUrl;
        }

        /**
         * streamer will only process elements with tag "rating" in element with tag "ratinglist".
         */
        $xmlStreamer = new ShopauskunftXmlStreamer($xmlFileOrData, 16384, 'ratinglist', null, 'rating');
        $xmlStreamer->setRatingServiceId($this->id);
        $xmlStreamer->parse();

        return $this->UpdateMainScroeValue();
    }

    /**
     * @param string $targetFile
     *
     * @return void
     */
    private function downloadXml($targetFile)
    {
        /**
         * We always download the file as the cronjob manages the update time.
         */
        $buffer = '';
        $fp = @fopen($this->fieldRatingUrl, 'r');
        if (!$fp) {
            echo "<!-- shopauskunft - no connection! -->\n";
        } else {
            stream_set_timeout($fp, 5);
            while (!feof($fp)) {
                $buffer .= fread($fp, 128);
            }
            fclose($fp);
        }
        file_put_contents($targetFile, $buffer);
    }

    /**
     * {@inheritdoc}
     */
    public function Render(
        $sViewName = 'RatingService_standard',
        $sViewSubType = 'pkgShopRatingService/views',
        $sViewType = 'Customer',
        $sSpotName = null,
        $aCallTimeVars = []
    ) {
        $aCallTimeVars['sRatingURL'] = $this->fieldRatingUrl;
        $aCallTimeVars['sRatingApiId'] = $this->fieldRatingApiId;
        $aCallTimeVars['oRatingServiceImage'] = $this->GetImage(0, 'icon_cms_media_id');

        // please call parent here to render!
        $sHTML = parent::Render($sViewName, $sViewSubType, $sViewType, $sSpotName, $aCallTimeVars);

        return $sHTML;
    }

    /**
     * @return CacheUtil
     */
    private function getCacheUtil()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_rating_service.util.cache');
    }
}
