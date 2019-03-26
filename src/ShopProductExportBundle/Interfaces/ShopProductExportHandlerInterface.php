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

    public function SetDebug($debug);

    public function Run();
}
