<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopProductExportBundle\Interfaces;

interface ShopProductExportHandlerInterface
{
    public function Init();

    public function SetArticleList(\TIterator $articleList);

    /**
     * @param string $cacheFile
     *
     * @deprecated 6.1.4 - the exporter no longer manages caching. calling the method has no effect
     */
    public function SetCacheFile($cacheFile);

    /**
     * @param bool $enableFileGeneration
     *
     * @deprecated 6.1.4 - the exporter no longer manages caching. calling the method has no effect
     */
    public function SetGenerateFile($enableFileGeneration);

    public function SetDebug($debug);

    public function Run();
}
