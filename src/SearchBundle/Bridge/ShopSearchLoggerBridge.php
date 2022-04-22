<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SearchBundle\Bridge;

use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use ChameleonSystem\SearchBundle\Interfaces\ShopSearchLoggerInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class ShopSearchLoggerBridge implements ShopSearchLoggerInterface
{
    /**
     * @var \ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface
     */
    private $shop;
    /**
     * @var \ChameleonSystem\CoreBundle\Service\LanguageServiceInterface
     */
    private $languageService;

    public function __construct(
        ShopServiceInterface $shop,
        LanguageServiceInterface $languageService
    ) {
        $this->shop = $shop;
        $this->languageService = $languageService;
    }


    /**
     * @param string $searchString
     * @param array<string, string> $searchFilter
     * @param int $numberOfMatches
     * @return void
     * @throws \ErrorException
     */
    public function logSearch($searchString, array $searchFilter, $numberOfMatches)
    {
        $searchString = $this->convertSearchToString($searchString, $searchFilter);
        $oLog = \TdbShopSearchLog::GetNewInstance();
        /** @var $oLog \TdbShopSearchLog */
        $aData = array(
            'name' => $searchString,
            'shop_id' => $this->shop->getId(),
            'number_of_results' => $numberOfMatches,
            'search_date' => date('Y-m-d H:i:s'),
            'cms_language_id' => $this->languageService->getActiveLanguageId(),
        );
        $oUser = $this->getExtranetUserProvider()->getActiveUser();
        if (null !== $oUser && null !== $oUser->id) {
            $aData['data_extranet_user_id'] = $oUser->id;
        }
        $oLog->LoadFromRow($aData);
        $oLog->AllowEditByAll(true);
        $oLog->Save();
    }

    /**
     * @param string $searchString
     * @param array<string, string> $searchFilter
     * @return string
     */
    private function convertSearchToString($searchString, array $searchFilter)
    {
        $parts = array();
        foreach ($searchFilter as $key => $value) {
            $parts[] = $key.'=>('.$value.')';
        }
        $searchFilterString = implode(', ', $parts);
        if ('' !== $searchFilterString) {
            $searchString .= ' ['.$searchFilterString.']';
        }

        return $searchString;
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
