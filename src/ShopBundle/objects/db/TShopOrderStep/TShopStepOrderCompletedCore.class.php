<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopStepOrderCompletedCore extends TShopStepUserData
{
    /**
     * we deactivate the basket step... after all, at this point, it has been reset.
     *
     * @return void
     */
    protected function CheckBasketContents()
    {
    }

    /**
     * returns true if the user may view the step.
     *
     * @param bool $bRedirectToPreviousPermittedStep
     *
     * @return bool
     */
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        if (\ChameleonSystem\CoreBundle\ServiceLocator::getParameter('chameleon_system_core.debug.debug_last_order')) {
            return true;
        }
        $bAllowAccess = true;
        if (!array_key_exists(self::SESSION_KEY_NAME_ORDER_SUCCESS, $_SESSION) || true != $_SESSION[self::SESSION_KEY_NAME_ORDER_SUCCESS]) {
            $bAllowAccess = false;
            if ($bRedirectToPreviousPermittedStep) {
                $oUserStep = &TdbShopOrderStep::GetStep('confirm');
                $this->JumpToStep($oUserStep);
            }
        }

        return $bAllowAccess;
    }

    /**
     * method should return any variables that should be replaced in the description field.
     *
     * @return array
     */
    protected function GetDescriptionVariables()
    {
        $aParameter = parent::GetDescriptionVariables();

        // add some data about the order so that we can display the details on the closing page
        $oUserOrder = &TShopBasket::GetLastCreatedOrder();
        if (!is_null($oUserOrder)) {
            foreach ($oUserOrder as $sProperty => $sPropertyValue) {
                if ('field' == substr($sProperty, 0, 5)) {
                    $aParameter[$sProperty] = $sPropertyValue;
                }
            }
            $aParameter['sOrderNumber'] = $oUserOrder->fieldOrdernumber;
        }

        $oShop = TdbShop::GetInstance();
        // add link to display a printable version of the order
        $aParameter['sLinkPrintableVersion'] = $oShop->GetLinkToSystemPage('print-order', array('id' => $oUserOrder->id));
        $aParameter[strtolower('sLinkPrintableVersion')] = $aParameter['sLinkPrintableVersion']; // links should be all lowercase - some customers force this (and some wysiwyg may require this as well) so we provide both variations
        $aParameter['oLastOrder'] = $oUserOrder;

        return $aParameter;
    }

    /**
     * used to display the step.
     *
     * @param string $sSpotName
     * @param array $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sSpotName = null, $aCallTimeVars = array())
    {
        $oUser = TdbDataExtranetUser::GetInstance();
        if (!is_null($oUser->fieldName) && !$oUser->IsLoggedIn()) {
            $this->SetLastUserBoughtToSession($oUser);
        }
        $sHTML = parent::Render($sSpotName, $aCallTimeVars);
        TdbShopOrderStep::ResetMarkOrderProcessAsCompleted();
        // if the user is not logged in, we need to clear the user object

        if (empty($oUser->id) || false == $oUser->IsLoggedIn()) {
            $tmp = $oUser->GetInstance(true);
        }

        return $sHTML;
    }

    /**
     * Returns last user bought from session.
     *
     * @return TdbDataExtranetUser|null
     */
    public function GetLastUserBoughtFromSession()
    {
        $oLastUserBought = null;
        if (array_key_exists('sLastUserBought', $_SESSION)) {
            $oLastUserBought = $_SESSION['sLastUserBought'];
        }

        return $oLastUserBought;
    }

    /**
     * Set last user bought into session.
     *
     * @param TdbDataExtranetUser $oUser
     *
     * @return void
     */
    protected function SetLastUserBoughtToSession($oUser)
    {
        $_SESSION['sLastUserBought'] = $oUser;
    }

    /**
     * Removes last user bought into session.
     *
     * @return void
     */
    public function RemoveLastUserBoughtFromSession()
    {
        if (array_key_exists('sLastUserBought', $_SESSION)) {
            unset($_SESSION['sLastUserBought']);
        }
    }
}
