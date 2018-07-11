<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopRatingService_Idealo extends TdbPkgShopRatingService
{
    /**
     * {@inheritdoc}
     */
    public function Render(
        $sViewName = 'RatingService_standard',
        $sViewSubType = 'pkgShopRatingService/views',
        $sViewType = 'Customer',
        $sSpotName = null,
        $aCallTimeVars = array()
    ) {
        $aCallTimeVars['sRatingURL'] = $this->fieldRatingUrl;
        $aCallTimeVars['sRatingApiId'] = $this->fieldRatingApiId;
        $aCallTimeVars['oRatingServiceImage'] = $this->GetImage(0, 'icon_cms_media_id');

        //please call parent here to render!
        $sHTML = parent::Render($sViewName, $sViewSubType, $sViewType, $sSpotName, $aCallTimeVars);

        return $sHTML;
    }
}
