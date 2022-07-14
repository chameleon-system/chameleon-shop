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
    /**
     * do any initialization work that needs to be done before you want to run the export.
     *
     * @return void
     */
    public function Init();

    /**
     * @param \TdbShopArticleList $oArticleList
     * @return void
     */
    public function SetArticleList(\TIterator $articleList);

    /**
     * @param bool $debug
     * @return void
     */
    public function SetDebug($debug);

    /**
     * Run the export. returns true if the export was successful, otherwise false
     *
     * @return bool
     */
    public function Run();
}
