<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgRatingServiceEkomiMapper extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        $oRatingService = TdbPkgShopRatingService::GetInstanceFromSystemName('ekomi');
        if ($oRatingService) {
            $oVisitor->SetMappedValue('sRatingApiId', $oRatingService->fieldRatingApiId);
            $oVisitor->SetMappedValue('current_rating', $oRatingService->fieldCurrentRating);
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oRatingService->table, $oRatingService->id);
            }
        }
    }
}
