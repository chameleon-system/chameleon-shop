<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\FilterInterface;

class Filter extends \ChameleonSystemShopBundleobjectsArticleListDatabaseAccessLayerFilterAutoParent implements FilterInterface
{
    public function getFilterQuery(ConfigurationInterface $moduleConfiguration)
    {
        return $this->GetListQuery($moduleConfiguration);
    }
}
