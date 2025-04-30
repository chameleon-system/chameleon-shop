<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\DataAccess;

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\PaymentMethodDataAccessInterface;
use Doctrine\DBAL\Connection;

class PaymentMethodDataAccess implements PaymentMethodDataAccessInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCountryIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_data_country_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingCountryIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_data_country_billing_data_country_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_data_extranet_user_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserGroupIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_data_extranet_group_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedArticleIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_shop_article_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedCategoryIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_shop_category_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedArticleGroupIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_shop_article_group_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidArticleIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_shop_article1_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidCategoryIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_shop_category1_mlt', $paymentMethodId);
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidArticleGroupIds($paymentMethodId)
    {
        return $this->getIdsFromMlt('shop_payment_method_shop_article_group1_mlt', $paymentMethodId);
    }

    /**
     * @param string $mltName
     * @param string $paymentMethodId
     *
     * @return array
     */
    private function getIdsFromMlt($mltName, $paymentMethodId)
    {
        $query = sprintf(
            'SELECT %1$s.`target_id`
                    FROM %1$s
                   WHERE %1$s.`source_id` = :paymentMethodId
                    ',
            $this->connection->quoteIdentifier($mltName)
        );
        $idRows = $this->connection->fetchAllAssociative($query, ['paymentMethodId' => $paymentMethodId]);

        return array_map(
            function (array $row) {
                return $row['target_id'];
            },
            $idRows
        );
    }
}
