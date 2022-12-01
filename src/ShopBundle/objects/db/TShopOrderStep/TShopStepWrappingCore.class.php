<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopStepWrappingCore extends TdbShopOrderStep
{
    /**
     * returns true if the user may view the step.
     *
     * @param bool $bRedirectToPreviousPermittedStep
     *
     * @return bool
     */
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        $bAllowAccess = parent::AllowAccessToStep($bRedirectToPreviousPermittedStep);
        if ($bAllowAccess) {
            // step only allowed
        }

        return $bAllowAccess;
    }

    /**
     * returns true if the step is active (and included in the order process) and
     * false if it is not.
     *
     * @return bool
     */
    public function IsActive()
    {
        $bIsActive = $this->GetFromInternalCache('bIsActive');
        if (null === $bIsActive) {
            $bIsActive = parent::IsActive();
            if ($bIsActive) {
                // wrapping only if wrapping is set
                $bIsActive = false;
                $oWrapping = TdbShopWrappingList::GetList();
                if ($oWrapping->Length() > 0) {
                    $bIsActive = true;
                }
                if (false == $bIsActive) {
                    $oCards = TdbShopWrappingCardList::GetList();
                    if ($oCards->Length() > 0) {
                        $bIsActive = true;
                    }
                }
            }
            $this->SetInternalCache('bIsActive', $bIsActive);
        }

        return $bIsActive;
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

        $aViewVariables['oWrappingList'] = TdbShopWrappingList::GetList();
        $aViewVariables['oCardList'] = TdbShopWrappingCardList::GetList();

        return $aViewVariables;
    }

    /**
     * called by the ExecuteStep Method - place any logic for the standard proccessing of this step here
     * return false if any errors occure (returns the user to the current step for corrections).
     *
     * @return bool
     */
    protected function ProcessStep()
    {
        $bContinue = parent::ProcessStep();
        if ($bContinue) {
            $aInput = TGlobal::instance()->GetUserData('aInput');
            if (!is_array($aInput)) {
                $aInput = array();
            }
            $sCardId = false;
            $sWrappId = false;
            $aArticleIds = false;
            if (array_key_exists('shop_wrapping_id', $aInput) && !empty($aInput['shop_wrapping_id'])) {
                $sWrappId = $aInput['shop_wrapping_id'];
            }
            if (array_key_exists('shop_wrapping_card_id', $aInput) && !empty($aInput['shop_wrapping_card_id'])) {
                $sCardId = $aInput['shop_wrapping_card_id'];
            }
            if (array_key_exists('shop_article_id', $aInput)) {
                if (is_array($aInput['shop_article_id'])) {
                    $aArticleIds = $aInput['shop_article_id'];
                } elseif (!empty($aInput['shop_article_id'])) {
                    $aArticleIds = array($aInput['shop_article_id']);
                }
            }

            $oBasket = TShopBasket::GetInstance();
            if ($sWrappId) {
                /**
                 * @psalm-suppress UndefinedMethod
                 * @FIXME Method `AddWrapping` does not exist?
                 */
                $oBasket->AddWrapping($sWrappId, $aArticleIds);
            }
            if ($sCardId) {
                /**
                 * @psalm-suppress UndefinedMethod
                 * @FIXME Method `AddWrappingCard` does not exist?
                 */
                $oBasket->AddWrappingCard($sCardId, $aArticleIds);
            }
        }
        if ($bContinue) {
            $sNextStep = TGlobal::instance()->GetUserData('nextStep');
            if (!empty($sNextStep)) {
                $oStep = TdbShopOrderStep::GetStep($sNextStep);
                $this->JumpToStep($oStep);
            }
        }

        return $bContinue;
    }
}
