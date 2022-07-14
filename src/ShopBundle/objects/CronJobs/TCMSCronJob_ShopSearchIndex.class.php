<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use esono\pkgCmsCache\CacheInterface;

/**
 * generate shop product search word index.
/**/
class TCMSCronJob_ShopSearchIndex extends TdbCmsCronjobs
{
    /**
     * @return void
     */
    protected function _ExecuteCron()
    {
        $this->getCache()->disable();
        $oIndexers = &TdbShopSearchIndexerList::GetList();
        $oIndex = null;
        if ($oIndexers->Length() < 1) {
            $oIndex = TdbShopSearchIndexer::GetNewInstance();
            /** @var $oIndex TdbShopSearchIndexer */
            $bFullIndex = (false == $oIndex->IndexHasContent());
            $oIndex->SetRegenerateCompleteIndex($bFullIndex);
            $oIndex->InitializeIndexer();
        } else {
            $oIndex = $oIndexers->Current();
            $bFullIndex = (false == $oIndex->IndexHasContent());
            $oIndex->SetRegenerateCompleteIndex($bFullIndex);
        }

        if (!$oIndex->IsRunning()) {
            $oIndex->InitializeIndexer();
        }
        if (!$oIndex->IndexerHasFinished()) {
            $oIndex->ProcessNextIndexStep(false);
        }
        $this->getCache()->enable();
    }

    private function getCache(): CacheInterface
    {
        return ServiceLocator::get('chameleon_system_core.cache');
    }
}
