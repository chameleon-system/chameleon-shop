<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopOrderStepList extends TShopOrderStepListAutoParent
{
    public const VIEW_PATH = 'pkgShop/views/db/TShopOrderStep/TShopOrderStepList';

    /**
     * the order step list should allow caching of the steps.
     */
    public function __construct()
    {
        parent::__construct();
        $this->bAllowItemCache = true;
    }

    /**
     * return the next step in line (null if there no other step).
     *
     * @param TdbShopOrderStep $oStep
     *
     * @return TdbShopOrderStep|null
     */
    public static function GetNextStep($oStep)
    {
        /** @var Doctrine\DBAL\Connection $connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedPosition = $connection->quote($oStep->fieldPosition);

        $query = "SELECT * FROM `shop_order_step` WHERE `position` > {$quotedPosition} ORDER BY `position`";
        $oSteps = TdbShopOrderStepList::GetList($query);
        $oNextStep = null;

        if ($oSteps->Length() > 0) {
            /** @var TdbShopOrderStep $oNextStep */
            $oNextStep = $oSteps->Current();
            if ($oNextStep && !$oNextStep->IsActive()) {
                $oNextStep = TdbShopOrderStepList::GetNextStep($oNextStep);
            }
        }

        return $oNextStep;
    }

    /**
     * return the previous step in line (null if there no other step).
     *
     * @param TdbShopOrderStep $oStep
     *
     * @return TdbShopOrderStep|null
     */
    public static function GetPreviousStep($oStep)
    {
        /** @var Doctrine\DBAL\Connection $connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedPosition = $connection->quote($oStep->fieldPosition);

        $query = "SELECT * FROM `shop_order_step` WHERE `position` < {$quotedPosition} ORDER BY `position` DESC";
        $oSteps = TdbShopOrderStepList::GetList($query);
        $oPreviousStep = null;

        if ($oSteps->Length() > 0) {
            /** @var TdbShopOrderStep $oPreviousStep */
            $oPreviousStep = $oSteps->Current();
        }

        return $oPreviousStep;
    }

    /**
     * returns all navi steps marked as navi steps. the active step will be marked as "is active".
     *
     * @return TdbShopOrderStepList
     */
    public static function GetNavigationStepList(TdbShopOrderStep $oActiveStep)
    {
        /** @var Doctrine\DBAL\Connection $connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $query = "SELECT *
              FROM `shop_order_step`
             WHERE `show_in_navigation` = '1'
          ORDER BY `position` ASC";
        $oSteps = TdbShopOrderStepList::GetList($query);
        $oSteps->bAllowItemCache = true;

        $stepIdList = [];
        while ($oStep = $oSteps->Next()) {
            if (false === $oStep->IsActive()) {
                continue;
            }
            $stepIdList[] = $oStep->id;
            $oStep->bIsTheActiveStep = ($oActiveStep->id === $oStep->id);
        }

        if (count($stepIdList) > 0) {
            $quotedIds = array_map(
                static function ($id) use ($connection) {
                    return $connection->quote($id);
                },
                $stepIdList
            );

            $query = "SELECT *
                  FROM `shop_order_step`
                 WHERE `show_in_navigation` = '1' AND `id` IN (".implode(',', $quotedIds).')
              ORDER BY `position` ASC';
            $oSteps = TdbShopOrderStepList::GetList($query);
            $oSteps->bAllowItemCache = true;

            while ($oStep = $oSteps->Next()) {
                $oStep->bIsTheActiveStep = ($oActiveStep->id === $oStep->id);
            }

            $oSteps->GoToStart();
        }

        return $oSteps;
    }

    /**
     * returns the position of the currently active step. if no step is marked as active,
     * it will return false. Step positions will start at 1 (not zero).
     *
     * @return int|false
     */
    public function GetActiveStepPosition()
    {
        $iActivePos = false;
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        $iStepNumber = 0;
        while ($oItem = $this->Next()) {
            ++$iStepNumber;
            if ($oItem->bIsTheActiveStep) {
                $iActivePos = $iStepNumber;
            }
        }
        $this->setItemPointer($iPointer);

        return $iActivePos;
    }

    /**
     * render the step list.
     *
     * @param string $sViewName
     * @param string $sViewType
     * @param string|null $sSpotName
     * @param array<string, mixed> $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'navi', $sViewType = 'Core', $sSpotName = null, $aCallTimeVars = [])
    {
        $oView = new TViewParser();
        $oView->AddVar('oSteps', $this);

        $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $oView->AddVar('oShop', $oShop);
        $oView->AddVar('sSpotName', $sSpotName);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);

        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
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
        $aViewVariables = [];

        return $aViewVariables;
    }
}
