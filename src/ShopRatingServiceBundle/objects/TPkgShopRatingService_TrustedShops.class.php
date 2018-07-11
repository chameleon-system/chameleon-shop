<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopRatingService\DataAccess\ShopRatingServiceTrustedShopsDataAccessInterface;
use ChameleonSystem\ShopRatingService\Util\CacheUtil;

class TPkgShopRatingService_TrustedShops extends TdbPkgShopRatingService
{
    const PATH_IMAGE_CACHE = '/TPkgShopRatingService_TrustedShops.gif';
    /**
     * @var SimplePie
     */
    protected $data = null;
    /**
     * @var bool
     */
    protected $bFeedLoadSuccess = false;
    /**
     * @var string
     */
    protected $sTrustedShopsWidgetSourceURL = 'https://www.trustedshops.com/bewertung/widget/widgets/';

    /**
     * Import all ratings.
     *
     * @return bool
     */
    public function Import()
    {
        if ($this->LoadFeed()) {
            $dataAccess = $this->getDataAccess();

            /** @var $item SimplePie_Item */
            foreach ($this->data->get_items() as $item) {
                $itemCount = $dataAccess->getItemCountForRemoteKey($item->get_id());
                if ($itemCount < 1) {
                    $content = $this->ParseContentData($item->get_content(true));
                    $sRaw = $this->getRawData($item, $content);

                    $iScoreValue = $this->ParseScoreFromRatingContent($item->get_content(true));
                    if ($iScoreValue < 0) {
                        $iScoreValue = '';
                    }
                    $dataAccess->insertItem(array(
                        'insertId' => TTools::GetUUID(),
                        'pkgShopRatingServiceId' => $this->id,
                        'remoteKey' => $item->get_id(),
                        'score' => $iScoreValue,
                        'rawData' => $sRaw,
                        'ratingUser' => '',
                        'ratingText' => $content,
                        'ratingDate' => $item->get_date('Y-m-d H:i:s'),
                    ));
                }
            }
        }

        //Update main score value
        return $this->UpdateMainScroeValue();
    }

    /**
     * @param SimplePie_Item $item
     * @param string         $content
     *
     * @return string
     */
    protected function getRawData(SimplePie_Item $item, $content)
    {
        return '<item>
              <title>'.$item->get_title()."</title>
              <content:encoded>
                <![CDATA[ $content ]]>
              </content:encoded>
              <pubDate>".$item->data['date']['raw'].'</pubDate>
              <link>'.$item->get_link().'</link>
              <guid>'.$item->get_id().'</guid>
            </item>';
    }

    /**
     * Cleanup TrustedShops content (remove HTML and images).
     *
     * @param string $sContent
     *
     * @return string $string
     */
    protected function ParseContentData($sContent)
    {
        $sRatingUser = '';
        $sRatingShopTitle = '';
        $sRatingShop = '';

        if (!empty($sContent)) {
            $sContent = preg_replace('/\s+/', ' ', $sContent);
            if (preg_match('#<p>(.+?)</p>#sim', $sContent, $matches)) {
                $count = count($matches);
                if ($count > 1) {
                    $sRatingUser = preg_replace('#<strong>(.*?)</strong>\s*#sim', '', $matches[1]);
                }
                if ($count > 3) {
                    $sRatingShop = preg_replace('#<strong>(.*?)</strong>\s*#sim', '', $matches[3]);
                    if (preg_match('#<strong>(.*?)</strong>#', $sRatingShop, $strongMatches)) {
                        if (count($strongMatches) > 1) {
                            $sRatingShopTitle = $strongMatches[1];
                        }
                    }
                }
            }
        }
        $sRet = '';
        if (!empty($sRatingUser)) {
            $sRet .= '<div>'.$sRatingUser.'</div>';
        }
        if (!empty($sRatingShopTitle)) {
            $sRet .= '<div>'.$sRatingShopTitle.'</div>';
        }
        if (!empty($sRatingShop)) {
            $sRet .= '<div>'.$sRatingShop.'</div>';
        }

        return $sRet;
    }

    /**
     * Parse score from user rating
     * string to parse: https://www.trustedshops.com/bewertung/images/mini_5.gif.
     *
     * @param string $sRawData
     *
     * @return int
     */
    protected function ParseScoreFromRatingContent($sRawData)
    {
        if (preg_match('#mini_(\d).gif#sim', $sRawData, $matches)) {
            return intval($matches[1]);
        } else {
            return -1;
        }
    }

    /**
     * @return bool
     */
    protected function LoadFeed()
    {
        $this->bFeedLoadSuccess = false;

        // Create a new instance of the SimplePie object
        $feedURL = $this->fieldRatingUrl;

        if (!empty($feedURL) && stristr($feedURL, 'http://') || stristr($feedURL, 'https://')) {
            $feed = new SimplePie();
            $feed->set_feed_url($feedURL);

            $cachePath = $this->getCacheUtil()->getCacheDirectory();
            if (null === $cachePath) {
                $feed->cache = false;
                trigger_error('RSS cache directory '.$cachePath.' is not writable or does not exist.', E_USER_NOTICE);
            } else {
                $feed->cache = true;
                $feed->cache_location = $cachePath;
            }

            // Initialize the whole SimplePie object.  Read the feed, process it, parse it, cache it, and
            // all that other good stuff.  The feed's information will not be available to SimplePie before
            // this is called.
            $this->bFeedLoadSuccess = $feed->init();

            // We'll make sure that the right content type and character encoding gets set automatically.
            // This function will grab the proper character encoding, as well as set the content type to text/html.
            $feed->handle_content_type();
            $this->data = &$feed;

            return $this->bFeedLoadSuccess;
        }
    }

    /**
     * Return FALSE if cache no more valid!
     *
     * @param string $filename_cache
     * @param int    $timeout
     *
     * @return bool
     */
    public function TrustedShopsImage_CacheCheck($filename_cache, $timeout = 10800)
    {
        $bRet = false;
        if (file_exists($filename_cache)) {
            $timestamp = filemtime($filename_cache);
            // Seconds
            if (mktime() - $timestamp < $timeout) {
                $bRet = true;
            } else {
                $bRet = false;
            }
        } else {
            $bRet = false;
        }

        return $bRet;
    }

    /**
     * @return string
     */
    protected function GetUserRatingWidgetImage()
    {
        $bFileExists = false;
        $sCacheFile = PATH_OUTBOX.self::PATH_IMAGE_CACHE;
        if (false === $this->TrustedShopsImage_CacheCheck($sCacheFile, 10800)) {
            $sImageData = file_get_contents($this->sTrustedShopsWidgetSourceURL.'/'.$this->fieldRatingApiId.'.gif');
            if ($sImageData) {
                if (false !== file_put_contents($sCacheFile, $sImageData)) {
                    $bFileExists = true;
                }
            }
        } else {
            $bFileExists = true;
        }
        if ($bFileExists) {
            $timestamp = filemtime($sCacheFile);

            return URL_OUTBOX.self::PATH_IMAGE_CACHE.'?v='.$timestamp;
        } else {
            return URL_OUTBOX.self::PATH_IMAGE_CACHE;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function Render(
        $sViewName = 'RatingService_standard',
        $sViewSubType = 'pkgShopRatingService/views',
        $sViewType = 'Customer',
        $sSpotName = null,
        $aCallTimeVars = array()
    ) {
        $aCallTimeVars['oRatingServiceImage'] = $this->GetImage(0, 'icon_cms_media_id');
        $aCallTimeVars['sWidgetImageURL'] = $this->GetUserRatingWidgetImage();
        $aCallTimeVars['sRatingApiId'] = $this->fieldRatingApiId;

        //please call parent here to render!
        return parent::Render($sViewName, $sViewSubType, $sViewType, $sSpotName, $aCallTimeVars);
    }

    /**
     * @return ShopRatingServiceTrustedShopsDataAccessInterface
     */
    private function getDataAccess()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_rating_service.data_access.trusted_shops');
    }

    /**
     * @return CacheUtil
     */
    private function getCacheUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_rating_service.util.cache');
    }
}
