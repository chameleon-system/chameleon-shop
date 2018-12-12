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
    const URL_IPN_IDENTIFIER = '_api_pkgshopipn_';

    /**
     * @param TdbCmsPortal $portal
     * @param TdbShopOrder $order
     *
     * @throws TPkgShopPaymentIPNException_OrderHasNoPaymentGroup
     *
     * @return string
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
        $url = $this->getPageService()->getLinkToPortalHomePageAbsolute(array(), $portal, $language, true);

        $url .= '/'.self::URL_IPN_IDENTIFIER.$identifier.'__'.$order->sqlData['cmsident'];

        return $url;
    }

    /**
     * return the IPNIdentifier for an order passed.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return string|null
     */
    private function getIPNIdentifierFromOrder(TdbShopOrder $oOrder)
    {
        $sIdentifier = null;
        $query = "SELECT `shop_payment_handler_group`.*
                    FROM `shop_payment_handler_group`
              INNER JOIN `shop_payment_handler` on `shop_payment_handler_group`.`id` = `shop_payment_handler`.`shop_payment_handler_group_id`
              INNER JOIN `shop_payment_method` on `shop_payment_handler`.`id` = `shop_payment_method`.`shop_payment_handler_id`
              INNER JOIN `shop_order` on `shop_payment_method`.`id` = `shop_order`.`shop_payment_method_id`
                   WHERE `shop_order`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oOrder->id)."'
        ";
        if ($aGroup = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
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
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.language_service');
    }

    /**
     * @return PageServiceInterface
     */
    private function getPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.page_service');
    }
}
