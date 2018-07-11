<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * generate shop product search word index.
/**/
class TCMSCronJob_ShopSearchIndex extends TdbCmsCronjobs
{
    protected function _ExecuteCron()
    {
        TCacheManager::SetDisableCaching(true);
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
        TCacheManager::SetDisableCaching(false);
    }
}
