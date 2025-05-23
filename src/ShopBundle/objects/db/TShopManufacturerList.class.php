<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopManufacturerList extends TShopManufacturerListAutoParent
{
    /**
     * @param string $sQuery
     * @param array $queryParameters - PDO style parameters
     * @param array $queryParameterTypes - PDO style parameter types
     *
     * @return void
     *
     * @psalm-suppress AssignmentToVoid, InvalidReturnStatement
     *
     * @FIXME Saving the result of `parent::DeleteExecute()` and returning does not make sense for a `void` return
     */
    public function Load($sQuery, array $queryParameters = [], array $queryParameterTypes = [])
    {
        $returnValue = parent::Load($sQuery, $queryParameters, $queryParameterTypes);
        if (!TGlobal::IsCMSMode()) {
            $this->AddFilterString('`shop_manufacturer`.`active`="1"');
        }

        return $returnValue;
    }
}
