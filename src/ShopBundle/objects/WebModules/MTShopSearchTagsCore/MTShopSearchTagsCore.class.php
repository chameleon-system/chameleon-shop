<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;

/**
 * module shows a tag cloud.
/**/
class MTShopSearchTagsCore extends TShopUserCustomModelBase
{
    protected $bAllowHTMLDivWrapping = true;

    public function &Execute()
    {
        parent::Execute();

        $this->data['oCloud'] = &$this->GetSearchKeywordCloud();

        return $this->data;
    }

    /**
     * return cloud for search keywords.
     *
     * @return TCMSTagCloud
     */
    protected function &GetSearchKeywordCloud()
    {
        $iSize = 13;
        $aCustomWords = array();
        $oCustomList = &TdbShopSearchCloudWordList::GetList();
        while ($oWord = &$oCustomList->Next()) {
            $aCustomWords[$oWord->fieldName] = $oWord->fieldWeight;
        }
        $iSize = $iSize - count($aCustomWords);
        if ($iSize < 0) {
            $iSize = 0;
        }
        $activeLanguage = $this->getLanguageService()->getActiveLanguageId();
        $query = 'SELECT COUNT(`shop_search_log`.`id`) AS '.TCMSTagCloud::QUERY_ITEM_COUNT_NAME.',
                       `shop_search_log`.`name` AS '.TCMSTagCloud::QUERY_ITEM_KEY_NAME.",
                       `shop_search_log`.*
                  FROM `shop_search_log`
                 WHERE `cms_language_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($activeLanguage)."' OR `cms_language_id` =  ''
              GROUP BY `shop_search_log`.`name`
                HAVING ".TCMSTagCloud::QUERY_ITEM_COUNT_NAME.' > 0
              ORDER BY '.TCMSTagCloud::QUERY_ITEM_COUNT_NAME." DESC
                 LIMIT 0,{$iSize}
               ";
        // add custom words...

        return TCMSTagCloud::GetCloud($query, 'TdbShopSearchLog', $aCustomWords);
    }

    private function getLanguageService(): LanguageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.language_service');
    }
}
