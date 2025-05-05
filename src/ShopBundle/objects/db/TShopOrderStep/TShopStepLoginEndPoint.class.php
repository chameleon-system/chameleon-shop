<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * the step allows the user to select if he wants to login, register, or order as guest.
 * it should be used in combination with TShopStepUserDataV2.
 *
 * IMPORTANT: you should always access the class via "TShopStepLogin" (the virtual class entry point)
 * /**/
class TShopStepLoginEndPoint extends TdbShopOrderStep
{
    /**
     * we allow access only if
     *   a) the user is not yet registered.
     *
     * @param bool $bRedirectToPreviousPermittedStep
     *
     * @return bool
     */
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        $bAllowAccess = parent::AllowAccessToStep($bRedirectToPreviousPermittedStep);
        if ($bAllowAccess && $bRedirectToPreviousPermittedStep) {
            $oUser = TdbDataExtranetUser::GetInstance();
            if ($oUser->IsLoggedIn()) {
                $this->JumpToStep($this->GetNextStep());
            }
        }

        return $bAllowAccess;
    }

    protected function ProcessStep()
    {
        $bContinue = parent::ProcessStep();

        $oGlobal = TGlobal::instance();
        $umode = $oGlobal->GetUserData('umode');
        if ('register' != $umode && 'guest' != $umode) {
            $umode = 'register';
        }
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oUser && $oUser->IsLoggedIn()) {
            $umode = 'user';
        } // force the mode to "user" if this is a logged in user
        TShopStepUserDataV2::SetUserMode($umode);

        return $bContinue;
    }
}
