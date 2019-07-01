<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use Symfony\Component\Translation\TranslatorInterface;

class TShopBreadcrumbItemManufacturer extends TShopBreadcrumbItem
{
    public function __construct(TdbShopManufacturer $manufacturer)
    {
        $this->oItem = $manufacturer;
    }

    /**
     * {@inheritDoc}
     */
    public function GetName()
    {
        // TODO do I want to translate here?
        $productsPrefix = $this->getTranslator()->trans('chameleon_system_shop.products_of_manufacturer');

        return $productsPrefix.' '.parent::GetName();
    }

    /**
     * {@inheritDoc}
     */
    public function GetLink($bForcePortal = false)
    {
        return $this->oItem->GetLinkProducts();
    }

    private function getTranslator(): TranslatorInterface
    {
        return ServiceLocator::get('translator');
    }
}
