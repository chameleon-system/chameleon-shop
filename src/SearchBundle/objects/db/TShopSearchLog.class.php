<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopSearchLog extends TShopSearchLogAutoParent
{
    public function __construct($id = null, $sLanguageId = null)
    {
        $this->SetChangeTriggerCacheChangeOnParentTable(false);
        parent::__construct($id, $sLanguageId);
    }
}
