<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;
use ChameleonSystem\CoreBundle\Service\PageServiceInterface;

class TPkgShopPaymentIPNManager
{
    public const URL_IPN_IDENTIFIER = '_api_pkgshopipn_';

    /**
     * @return string
     *
     * @throws TPkgShopPaymentIPNException_OrderHasNoPaymentGroup
     */
    public function getIPNURL(TdbCmsPortal $portal, TdbShopOrder $order)
    {
        $identifier = $this->getIPNIdentifierFromOrder($order);
        if (null === $identifier) {
            throw new TPkgShopPaymentIPNException_OrderHasNoPaymentGroup($order->id, 'failed to generate an IPN URL because the payment handler of the order is not assigned to any payment handler group');
        }

        $language = null;
        if (property_exists($order, 'fieldCmsLanguageId')) {
            $language = $this->getLanguageService()->getLanguage($order->fieldCmsLanguageId);
        }
        $url = $this->getPageService()->getLinkToPortalHomePageAbsolute([], $portal, $language, true);

        if (array_key_exists('cmsident', $order->sqlData)) {
            $url .= '/'.self::URL_IPN_IDENTIFIER.$identifier.'__'.$order->sqlData['cmsident'];
        }

        return $url;
    }

    /**
     * return the IPNIdentifier for an order passed.
     *
     * @return string|null
     */
    private function getIPNIdentifierFromOrder(TdbShopOrder $oOrder)
    {
        $sIdentifier = null;
        $connection = $this->getDatabaseConnection();
        $quotedOrderId = $connection->quote($oOrder->id);

        $query = "SELECT `shop_payment_handler_group`.*
                FROM `shop_payment_handler_group`
          INNER JOIN `shop_payment_handler` ON `shop_payment_handler_group`.`id` = `shop_payment_handler`.`shop_payment_handler_group_id`
          INNER JOIN `shop_payment_method` ON `shop_payment_handler`.`id` = `shop_payment_method`.`shop_payment_handler_id`
          INNER JOIN `shop_order` ON `shop_payment_method`.`id` = `shop_order`.`shop_payment_method_id`
               WHERE `shop_order`.`id` = {$quotedOrderId}
    ";
        $aGroup = $connection->fetchAssociative($query);
        if ($aGroup) {
            $sIdentifier = trim($aGroup['ipn_group_identifier']);
            if ('' === $sIdentifier) {
                $sIdentifier = trim($aGroup['system_name']);
            }
        }

        return $sIdentifier;
    }

    /**
     * @return LanguageServiceInterface
     */
    private function getLanguageService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.language_service');
    }

    /**
     * @return PageServiceInterface
     */
    private function getPageService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.page_service');
    }
}
