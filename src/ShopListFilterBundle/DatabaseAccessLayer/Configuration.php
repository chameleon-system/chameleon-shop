<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\pkgshoplistfilter\DatabaseAccessLayer;

use ChameleonSystem\pkgshoplistfilter\DatabaseAccessLayer\Interfaces\ConfigurationInterface;

class Configuration extends \ChameleonSystempkgshoplistfilterDatabaseAccessLayerConfigurationAutoParent implements ConfigurationInterface
{
    /**
     * @return bool
     */
    public function allowUseOfPostSearchFilter()
    {
        return $this->fieldCanBeFiltered;
    }
}
