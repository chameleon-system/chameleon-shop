<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopNewsletterSignupWithOrder_TShopBasket extends TPkgShopNewsletterSignupWithOrder_TShopBasketAutoParent
{
    /**
     * @var bool
     */
    private $userSelectedNewsletterOptionInOrderStep = false;

    /**
     * @return bool
     */
    public function getUserSelectedNewsletterOptionInOrderStep()
    {
        return $this->userSelectedNewsletterOptionInOrderStep;
    }

    /**
     * @param bool $userSelectedNewsletterOptionInOrderStep
     *
     * @return void
     */
    public function setUserSelectedNewsletterOptionInOrderStep($userSelectedNewsletterOptionInOrderStep)
    {
        $this->userSelectedNewsletterOptionInOrderStep = $userSelectedNewsletterOptionInOrderStep;
    }
}
