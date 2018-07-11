<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSWizardStepShopAddressShipping extends TdbCmsWizardStep
{
    /**
     * define any methods of the class that may be called via get or post.
     *
     * @return array
     */
    public function AllowedMethods()
    {
        $externalFunctions = parent::AllowedMethods();
        $externalFunctions[] = 'ShowShippingAddressInput';
        $externalFunctions[] = 'HideShippingAddressInput';

        return $externalFunctions;
    }

    protected function SetShowShippingAddressInputState($bState)
    {
        $oUser = TdbDataExtranetUser::GetInstance();
        if (false == $bState) {
            // and set shipping address of user = to billing address
            $oUser->ShipToBillingAddress(true);
        } else {
            // pick a shipping address not equal to the billing address
            $oUser->ShipToAddressOtherThanBillingAddress();
        }
    }

    /**
     * set state variable to show shipping address input. method returns false, so that
     * the same step is returned.
     */
    public function ShowShippingAddressInput()
    {
        $this->SetShowShippingAddressInputState(true);
    }

    /**
     * set state variable to show shipping address input. method returns false, so that
     * the same step is returned.
     */
    public function HideShippingAddressInput()
    {
        $this->SetShowShippingAddressInputState(false);
        $this->ExecuteStep();
    }
}
