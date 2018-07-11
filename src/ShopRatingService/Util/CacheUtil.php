<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopRatingService\Util;

class CacheUtil
{
    /**
     * @var string
     */
    private $cacheBaseDir;

    /**
     * @param string $cacheBaseDir
     */
    public function __construct($cacheBaseDir)
    {
        $this->cacheBaseDir = $cacheBaseDir;
    }

    /**
     * Returns the cache directory used for downloads.
     *
     * @return string|null null if the directory could not be created or is not writable
     */
    public function getCacheDirectory()
    {
        $dir = $this->cacheBaseDir.'/RatingServicesCache/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            if (!file_exists($dir)) {
                $dir = null;
            }
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            $dir = null;
        }

        return $dir;
    }
}
