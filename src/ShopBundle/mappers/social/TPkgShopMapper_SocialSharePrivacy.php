<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\mappers\social;

use AbstractViewMapper;
use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;
use IMapperCacheTriggerRestricted;
use IMapperRequirementsRestricted;
use IMapperVisitorRestricted;
use TdbCmsLanguage;

/**
 * @deprecated since 6.2.11 - not used anymore
 */
class TPkgShopMapper_SocialSharePrivacy extends AbstractViewMapper
{
    /**
     * @var LanguageServiceInterface
     */
    private $languageService;

    /**
     * @param LanguageServiceInterface|null $languageService
     */
    public function __construct(LanguageServiceInterface $languageService = null)
    {
        if (null === $languageService) {
            $this->languageService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.language_service');
        } else {
            $this->languageService = $languageService;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject(
            'activeLanguage',
            'TdbCmsLanguage',
            $this->languageService->getActiveLanguage()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void{
        $language = 'en';
        /** @var TdbCmsLanguage $activeLanguage */
        $activeLanguage = $oVisitor->GetSourceObject('activeLanguage');
        if (null !== $activeLanguage && 'de' === $activeLanguage->fieldIso6391) {
            $language = 'de';
        }
        $oVisitor->SetMappedValue('socialLanguage', $language);
    }
}
