<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopRatingServiceRating extends TPkgShopRatingServiceRatingAutoParent
{
    /**
     * Render object.
     *
     * @param string $sViewName
     * @param string $sViewSubType
     * @param string $sViewType
     * @param null $sSpotName
     * @param array $aCallTimeVars
     *
     * @return string
     */
    public function Render($sViewName = 'RatingServiceRating_standard', $sViewSubType = 'pkgShopRatingService/views', $sViewType = 'Customer', $sSpotName = null, $aCallTimeVars = [])
    {
        $oView = new TViewParser();

        $oRatingService = $this->GetFieldPkgShopRatingService();
        $oView->AddVar('oRating', $this);
        $oView->AddVar('oRatingService', $oRatingService);

        $oView->AddVar('fieldRatingDate', $this->fieldRatingDate);
        $sRatingText = $this->fieldRatingText;
        if (false == $oRatingService->fieldRatingsContainHtml) {
            $sRatingText = nl2br(TGlobal::OutHTML(strip_tags($sRatingText)));
        }
        $oView->AddVar('fieldRatingText', $sRatingText);

        $oView->AddVar('fieldRatingService_CurrentRating', $this->GetFieldPkgShopRatingService()->fieldCurrentRating);

        return $oView->RenderObjectPackageView($sViewName, $sViewSubType, $sViewType);
    }
}
