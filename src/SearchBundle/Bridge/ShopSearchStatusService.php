<?php

namespace ChameleonSystem\SearchBundle\Bridge;

use ChameleonSystem\SearchBundle\DataModel\ShopSearchStatusDataModel;

class ShopSearchStatusService
{
    public function getSearchStatus(): ShopSearchStatusDataModel
    {
        $shopSearchIndexerList = \TdbShopSearchIndexerList::GetList();
        $shopSearchIndexer = $shopSearchIndexerList->Current();

        return new ShopSearchStatusDataModel(
            new \DateTime($shopSearchIndexer->fieldStarted),
            new \DateTime($shopSearchIndexer->fieldCompleted),
            $shopSearchIndexer->fieldTotalRowsToProcess
        );
    }
}
