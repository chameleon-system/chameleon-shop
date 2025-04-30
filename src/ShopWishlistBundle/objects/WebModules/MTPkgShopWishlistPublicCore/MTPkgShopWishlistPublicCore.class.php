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
 * module is used to allow a user to search for public wishlist, and to display
 * the detailpage of a wishlist when given the lists id.
 * /**/
class MTPkgShopWishlistPublicCore extends TUserCustomModelBase
{
    public const URL_PARAMETER_NAME = 'MTPkgShopWishlistPublicCore';

    /**
     * active wishlist if there is one.
     *
     * @var TdbPkgShopWishlist
     */
    protected $oActiveWishlist;

    /**
     * @var array<string, mixed>
     */
    protected $aUserInput = [];

    /**
     * @var bool
     */
    protected $bAllowHTMLDivWrapping = true;

    public function Init()
    {
        parent::Init();
        if ($this->global->userdataExists(self::URL_PARAMETER_NAME)) {
            $this->aUserInput = $this->global->GetUserData(self::URL_PARAMETER_NAME);
            if (!is_array($this->aUserInput)) {
                $this->aUserInput = [];
            }
        }

        if (array_key_exists('id', $this->aUserInput)) {
            $this->oActiveWishlist = TdbPkgShopWishlist::GetNewInstance();
            if (!$this->oActiveWishlist->Load($this->aUserInput['id'])) {
                $this->oActiveWishlist = null;
            }
            $this->SetTemplate(get_class($this), 'system/vWishlistDetail');
        }
    }

    public function Execute()
    {
        parent::Execute();
        $this->data['oActiveWishlist'] = $this->oActiveWishlist;

        return $this->data;
    }
}
