<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSWizardStepListMapper_Basket extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oStepList', 'TdbShopOrderStepList');
        $oRequirements->NeedsSourceObject('oActiveStep', 'TdbShopOrderStep');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oActiveStep TdbShopOrderStep */
        $oActiveStep = $oVisitor->GetSourceObject('oActiveStep');
        if ($oActiveStep && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oActiveStep->table, $oActiveStep->id);
        }
        /**
        - aSteps: array of items (object or assoc array). each item has the following properties:
         ** bIsActive
         ** sLink
         ** sTitle
         ** sSeoTitle (defaults to sTitle)
         ** sIconUrl shows icon before link
         ** sIconClass css class (used in a span) that defines the icon this will be used if no icon url is given.
         */
        /** @var $oStepList TdbShopOrderStepList */
        $oStepList = $oVisitor->GetSourceObject('oStepList');
        /** @var $oStep TdbShopOrderStep */
        $iActive = $oStepList->GetActiveStepPosition();

        $oStep = null;
        $aSteps = array();
        $counter = 1;
        while (false !== ($oStep = $oStepList->Next())) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oStep->table, $oStep->id);
            }
            $bStepIsActive = ($counter++ <= $iActive);
            $aSteps[] = array(
                'isCurrentStep' => ($oStep->id === $oActiveStep->id),
                'bIsActive' => $bStepIsActive,
                'sLink' => ('thankyou' != $oActiveStep->fieldSystemname) ? ($oStep->GetStepURL(false)) : (''),
                'sTitle' => $oStep->GetName(),
                'sSeoTitle' => $oStep->GetName(),
                'sIconUrl' => '',
                'sIconClass' => $bStepIsActive ? $oStep->fieldCssIconClassActive : $oStep->fieldCssIconClassInactive,
            );
        }
        $oVisitor->SetMappedValue('bOrderCompleted', ('thankyou' == $oActiveStep->fieldSystemname));
        $oVisitor->SetMappedValue('aSteps', $aSteps);
    }
}
