<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SearchBundle\ArticleList\Mapper;

use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;

class SearchRequestMapper extends \AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(\IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('shop', 'TdbShop');
        $oRequirements->NeedsSourceObject('stateObject', '\ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        \IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        \IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        /** @var $shop \TdbShop */
        $shop = $oVisitor->GetSourceObject('shop');
        /** @var $state StateInterface */
        $state = $oVisitor->GetSourceObject('stateObject');

        $queryParam = $state->getQueryParameter();
        $query = (isset($queryParam['q'])) ? $queryParam['q'] : '';
        $oSearchCache = $shop->GetActiveSearchObject();

        $searchWasAltered = ($query !== $oSearchCache->sSearchTerm);

        $aData = [
            'searchWasAltered' => $searchWasAltered,
            'searchQueryOriginal' => $query,
            'searchQuery' => $oSearchCache->sSearchTerm,
            'spellCheckLink' => false,
            'spellCheckSuggestion' => false,
        ];
        if (null !== $oSearchCache->sSearchTermSpellChecked) {
            $aData['spellCheckLink'] = $oSearchCache->GetSearchLinkForTerm($oSearchCache->sSearchTermSpellChecked);
            $aData['spellCheckSuggestion'] = $oSearchCache->sSearchTermSpellCheckedFormated;
        }
        $oVisitor->SetMappedValueFromArray(
            $aData
        );
    }
}
