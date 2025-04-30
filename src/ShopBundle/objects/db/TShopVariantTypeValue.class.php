<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Util\UrlNormalization\UrlNormalizationUtil;

class TShopVariantTypeValue extends TAdbShopVariantTypeValue
{
    /**
     * returns the url string part (type/value) for the value.
     *
     * @return string
     */
    public function GetURLString()
    {
        $sURLString = $this->GetFromInternalCache('sURLString');

        if (is_null($sURLString)) {
            $oType = $this->GetFieldShopVariantType();

            $urlNormalizationUtil = $this->getUrlNormalizationUtil();
            if (empty($oType->fieldUrlName)) {
                $oType->fieldUrlName = $urlNormalizationUtil->normalizeUrl($oType->fieldName);
            }
            if (empty($this->fieldUrlName)) {
                $this->fieldUrlName = $urlNormalizationUtil->normalizeUrl($this->fieldUrlName);
            }

            $sURLString = $oType->fieldUrlName.'/'.$this->fieldUrlName;
            $this->SetInternalCache('sURLString', $sURLString);
        }

        return $sURLString;
    }

    /**
     * @return UrlNormalizationUtil
     */
    private function getUrlNormalizationUtil()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.url_normalization');
    }
}
