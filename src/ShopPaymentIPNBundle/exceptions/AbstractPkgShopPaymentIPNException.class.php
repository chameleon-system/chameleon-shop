<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class AbstractPkgShopPaymentIPNException extends Exception
{
    public function __toString(): string
    {
        $sString = parent::__toString();

        $sString .= "\ncalled in [".$this->getFile().'] on line ['.$this->getLine().']';

        return $sString;
    }

    /**
     * @return string
     * @psalm-return class-string<self>
     */
    public function getErrorType()
    {
        return get_class($this);
    }
}
