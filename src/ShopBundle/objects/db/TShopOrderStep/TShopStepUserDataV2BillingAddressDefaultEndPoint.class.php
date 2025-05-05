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
 * this step class works like TShopStepUserDataV2 but uses the billing address as default address
 * because TShopStepUserDataV2 will use the shipping address as default.
 *
 * IMPORTANT: you should always access the class via "TShopStepUserDataV2BillingAddressDefault" (the virtual class entry point)
 * /**/
class TShopStepUserDataV2BillingAddressDefaultEndPoint extends TShopStepUserDataV2
{
    /**
     * defines which address (shipping or billing) will be used to sync the profile data (ie. is the primary Addresse)
     * default is billing address.
     *
     * @return string
     */
    protected function AddressUsedAsPrimaryAddress()
    {
        parent::AddressUsedAsPrimaryAddress();

        return TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING;
    }
}
