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
 * item is used to simulate a breadcrumb node.
 *
 * @template TItem of TCMSRecord
 */
class TShopBreadcrumbItem
{
    /**
     * @var TItem
     */
    public $oItem = null;

    /**
     * @var string
     */
    public $id = null;

    /**
     * @return string
     */
    public function GetName()
    {
        return $this->oItem->GetName();
    }

    /**
     * Subclasses *MUST* override this method
     * @return string
     * @abstract
     * @psalm-suppress InvalidReturnType
     */
    public function GetLink()
    {
    }

    /**
     * @return string
     */
    public function GetTarget()
    {
        return '_self';
    }
}
