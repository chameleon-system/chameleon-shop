<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CMSShopArticleIndex extends TCMSModelBase
{
    /** @var bool */
    protected $bIndexCompleted = false;

    /** @var bool */
    protected $bIndexIsRunning = false;

    /** @var float|false */
    protected $bPercentDone = 0;

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'TickerIndexGeneration';
    }

    /**
     * @return void
     */
    public function TickerIndexGeneration()
    {
        $this->bIndexIsRunning = true;
        // see if some indexer exists
        $oIndexers = TdbShopSearchIndexerList::GetList();
        //      $bRegenerateCompleteIndex = false;
        $oIndex = null;
        if ($oIndexers->Length() < 1) {
            $oIndex = TdbShopSearchIndexer::GetNewInstance();
            /** @var $oIndex TdbShopSearchIndexer */
            //        $oIndex->bRegenerateCompleteIndex = $bRegenerateCompleteIndex;
            $oIndex->InitializeIndexer();
        } else {
            $oIndex = $oIndexers->Current();
            //        $oIndex->bRegenerateCompleteIndex = $bRegenerateCompleteIndex;
        }

        if (!$oIndex->IsRunning()) {
            $oIndex->InitializeIndexer();
        }
        if (!$oIndex->IndexerHasFinished()) {
            $oIndex->ProcessNextIndexStep();
            $this->bPercentDone = $oIndex->GetIndexStatus();
            if (false === $this->bPercentDone) {
                $this->bIndexCompleted = true;
                $this->bIndexIsRunning = false;
                $this->bPercentDone = 100;
            }
        } else {
            $this->bIndexIsRunning = false;
            $this->bIndexCompleted = true;
            $this->bPercentDone = 100;
            $this->ClearSearchCacheTables();
        }
    }

    /**
     * Deletes the system's search cache tables. This method is called after
     * the successfull creation of the shop's search article index.
     *
     * @return void
     */
    public function ClearSearchCacheTables()
    {
        $sQuery = 'TRUNCATE TABLE `shop_search_cache_item`';
        \MySqlLegacySupport::getInstance()->query($sQuery);
        $sQuery = 'TRUNCATE TABLE `shop_search_cache`';
        \MySqlLegacySupport::getInstance()->query($sQuery);
    }

    public function Execute()
    {
        parent::Execute();
        $this->data['bIndexIsRunning'] = $this->bIndexIsRunning;
        $this->data['bPercentDone'] = $this->bPercentDone;
        $this->data['bIndexCompleted'] = $this->bIndexCompleted;

        return $this->data;
    }
}
