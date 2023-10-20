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
 * show the current basket.
/**/
class TShopStepBasketCore extends TdbShopOrderStep
{
    /**
     * redirects the user to the wrapping step.
     *
     * @return void
     */
    public function JumpToWrapping()
    {
        // Not yet implemented
    }

    /**
     * returns the link to the previous step (or false if there is none).
     *
     * @return string
     */
    protected function GetReturnToLastStepURL()
    {
        $sLink = MTShopOrderWizardCore::GetCallingURL();

        return $sLink;
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

        $aViewVariables['oBasket'] = TShopBasket::GetInstance();

        return $aViewVariables;
    }

    /**
     * define any methods of the class that may be called via get or post.
     *
     * @return array
     */
    public function AllowedMethods()
    {
        $externalFunctions = parent::AllowedMethods();
        $externalFunctions[] = 'UpdateBasket';

        return $externalFunctions;
    }

    /**
     * called by the ExecuteStep Method - place any logic for the standard proccessing of this step here
     * return false if any errors occure (returns the user to the current step for corrections).
     *
     * @return bool
     */
    protected function ProcessStep()
    {
        $bContinue = $this->UpdateBasket(true);

        return $bContinue;
    }

    /**
     * overwrite this to jump directly to the payment page for signed in users.
     *
     * @return TdbShopOrderStep|null
     */
    public function GetNextStep()
    {
        static $oNextStep;
        if (!$oNextStep) {
            $oUsers = TdbDataExtranetUser::GetInstance();
            if ($oUsers->IsLoggedIn() && $oUsers->HasData()) {
                $oBasket = TShopBasket::GetInstance();
                $oBasket->aCompletedOrderStepList['user'] = true;
                // now search for the next step not marked as completed
                $oStepList = TdbShopOrderStepList::GetList();
                $oStepList->bAllowItemCache = true;
                $oNextStep = null;
                $bDone = false;
                // do not allow access to the last stpe
                $iNumItems = $oStepList->Length();
                while (($iNumItems > 0) && !$bDone && ($oTmpStep = $oStepList->Next())) {
                    --$iNumItems;
                    if (!$oTmpStep->AllowAccessToStepPublic()) {
                        // step not completed...
                        $bDone = true;
                    } else {
                        $oNextStep = $oTmpStep;
                    }
                }
            } else {
                $oNextStep = TdbShopOrderStepList::GetNextStep($this);
            }
        }

        return $oNextStep;
    }

    /**
     * update current basket.
     *
     * @param bool $bInternalCall - if set to true, all redirects will be supressed
     *
     * @return bool
     */
    public function UpdateBasket($bInternalCall = false)
    {
        // we use the existing basket module to do all the work...
        $oShop = TdbShop::GetInstance();
        $oController = TGlobal::GetController();
        $oBasketModule = $oController->getModuleLoader()->GetPointerToModule($oShop->fieldBasketSpotName);
        $bSuccess = $oBasketModule->UpdateBasketItems(null, false, true);

        // redirect to current page
        if (!$bInternalCall) {
            $this->ReloadCurrentStep();
        }

        return $bSuccess;
    }
}
