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

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;

interface ShopProductExporterInterface
{
    /**
     * @param string $exportKey
     *
     * @return bool
     */
    public function isValidExportKey($exportKey);

    /**
     * @param string $alias
     *
     * @return string
     */
    public function export(ConfigurationInterface $moduleConfiguration, $alias);

    /**
     * @param string $alias
     *
     * @return void
     */
    public function registerHandler($alias, ShopProductExportHandlerInterface $exportHandler);

    /**
     * @param string $alias
     *
     * @return bool
     */
    public function aliasExists($alias);
}
