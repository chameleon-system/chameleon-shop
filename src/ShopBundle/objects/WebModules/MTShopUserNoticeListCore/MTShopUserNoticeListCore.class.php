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
 * used to manage the notice list for a user (detail view).
 *
 * @deprecated since 6.2.0 - no longer used.
/**/
class MTShopUserNoticeListCore extends TShopUserCustomModelBase
{
    protected $bAllowHTMLDivWrapping = true;

    public function &Execute()
    {
        parent::Execute();

        $oUser = TdbDataExtranetUser::GetInstance();
        $this->data['aNoticeList'] = $oUser->GetNoticeListArticles();
        reset($this->data['aNoticeList']);

        return $this->data;
    }
}
