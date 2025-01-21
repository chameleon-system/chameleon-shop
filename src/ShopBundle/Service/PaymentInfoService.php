<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Service;

use ChameleonSystem\ShopBundle\Interfaces\PaymentInfoServiceInterface;
use Doctrine\DBAL\Connection;

class PaymentInfoService implements PaymentInfoServiceInterface
{
    public function __construct(private readonly Connection $databaseConnection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isPaymentMethodActive($paymentMethodInternalName, ?\TdbCmsPortal $portal = null)
    {
        $query = 'SELECT COUNT(*) FROM `shop_payment_method`';
        $parameters = [];

        if (null !== $portal) {
            $query .= "\nLEFT JOIN `shop_payment_method_cms_portal_mlt` ON `shop_payment_method`.`id` = `shop_payment_method_cms_portal_mlt`.`source_id`
                WHERE (`shop_payment_method_cms_portal_mlt`.`target_id` = :portalId OR `shop_payment_method_cms_portal_mlt`.`target_id` IS NULL)";
            $query .= ' AND ';
            $parameters['portalId'] = $portal->id;
        } else {
            $query .= ' WHERE ';
        }
        $query .= "\n`name_internal` = :paymentMethodInternalName AND `active` = '1'";
        $parameters['paymentMethodInternalName'] = $paymentMethodInternalName;

        $result = $this->databaseConnection->fetchColumn($query, $parameters);

        return '0' !== $result;
    }
}
