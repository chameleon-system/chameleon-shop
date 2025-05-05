<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopNewsletterSignupWithOrder_TShopStepConfirm extends TPkgShopNewsletterSignupWithOrder_TShopStepConfirmAutoParent
{
    /**
     * @return void
     */
    protected function addDataToBasket(TShopBasket $oBasket)
    {
        parent::addDataToBasket($oBasket);
        if (false === method_exists($oBasket, 'getUserSelectedNewsletterOptionInOrderStep')) {
            return;
        }

        $oGlobal = TGlobal::instance();
        $aUserData = $oGlobal->GetUserData('aInput');
        if (is_array($aUserData) && array_key_exists('newsletter', $aUserData) && '1' == $aUserData['newsletter']) {
            $oBasket->setUserSelectedNewsletterOptionInOrderStep(true);
        } else {
            $oBasket->setUserSelectedNewsletterOptionInOrderStep(false);
        }
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = parent::GetAdditionalViewVariables($sViewName, $sViewType);

        $aViewVariables['bShowNewsletterSignup'] = true;
        $aViewVariables['newsletter'] = false;
        $aInput = TGlobal::instance()->GetUserData('aInput');
        if (is_array($aInput) && isset($aInput['newsletter']) && '1' == $aInput['newsletter']) {
            $aViewVariables['newsletter'] = true;
        }

        $connection = $this->getDatabaseConnection();
        $oUser = TdbDataExtranetUser::GetInstance();
        // show newsletter signup at all?
        $aCondition = [
            '`email` = '.$connection->quote($oUser->GetUserEMail()),
        ];

        if ($oUser && $oUser->IsLoggedIn()) {
            $aCondition[] = '`data_extranet_user_id` = '.$connection->quote($oUser->id);
        }
        $query = "SELECT COUNT(*) AS total FROM `pkg_newsletter_user` WHERE `optin` = '1' AND ((".implode(') OR (', $aCondition).'))';
        $aRow = $connection->fetchAssociative($query);
        if ($aRow && $aRow['total'] > 0) {
            $aViewVariables['bShowNewsletterSignup'] = false;
        }

        return $aViewVariables;
    }
}
