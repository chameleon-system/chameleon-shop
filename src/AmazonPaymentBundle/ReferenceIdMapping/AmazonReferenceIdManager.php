<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping;

use Doctrine\DBAL\Connection;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceIdList;

class AmazonReferenceIdManager
{
    const PERSIST_TABLE = 'amazon_payment_id_mapping';
    /**
     * @var AmazonReferenceIdList[]
     */
    private $idLists = array();
    /**
     * @var string
     */
    private $amazonOrderReferenceId = null;

    /**
     * @var string
     */
    private $shopOrderId = null;

    public function __construct($amazonOrderReferenceId, $shopOrderId)
    {
        $this->amazonOrderReferenceId = $amazonOrderReferenceId;
        $this->shopOrderId = $shopOrderId;
        $this->idLists = array(
            IAmazonReferenceId::TYPE_AUTHORIZE => new AmazonReferenceIdList($amazonOrderReferenceId, IAmazonReferenceId::TYPE_AUTHORIZE),
            IAmazonReferenceId::TYPE_CAPTURE => new AmazonReferenceIdList($amazonOrderReferenceId, IAmazonReferenceId::TYPE_CAPTURE),
            IAmazonReferenceId::TYPE_REFUND => new AmazonReferenceIdList($amazonOrderReferenceId, IAmazonReferenceId::TYPE_REFUND),
        );
    }

    /**
     * @param int   $requestMode IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS or IAmazonReferenceId::REQUEST_MODE_SYNCHRONOUS
     * @param float $value
     *
     * @return IAmazonReferenceId
     */
    public function createLocalAuthorizationReferenceId($requestMode, $value)
    {
        /** @var $item IAmazonReferenceId */
        $item = $this->idLists[IAmazonReferenceId::TYPE_AUTHORIZE]->getNew($value, null);
        $item->setRequestMode($requestMode);

        return $item;
    }

    /**
     * @param int    $requestMode   IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS or IAmazonReferenceId::REQUEST_MODE_SYNCHRONOUS
     * @param float  $value
     * @param string $transactionId - shop payment transaction id
     *
     * @return IAmazonReferenceId
     */
    public function createLocalAuthorizationReferenceIdWithCaptureNow($requestMode, $value, $transactionId)
    {
        /** @var $item IAmazonReferenceId */
        $item = $this->idLists[IAmazonReferenceId::TYPE_AUTHORIZE]->getNew($value, $transactionId);
        $item->setRequestMode($requestMode);
        $item->setCaptureNow(true);

        $captureItem = $this->createLocalCaptureReferenceId($item->getValue(), $transactionId);
        $captureItem->setLocalId(
            $item->getLocalId()
        ); // captures created via capture now have the same id as the auth request
        $captureItem->setRequestMode($requestMode);
        $captureItem->setCaptureNow(true);

        return $item;
    }

    /**
     * @param $value
     * @param string $transactionId - the transaction id associated with the counter
     *
     * @return IAmazonReferenceId
     */
    public function createLocalCaptureReferenceId($value, $transactionId)
    {
        return $this->idLists[IAmazonReferenceId::TYPE_CAPTURE]->getNew($value, $transactionId);
    }

    /**
     * @param $value
     * @param $transactionId
     *
     * @return IAmazonReferenceId
     */
    public function createLocalRefundReferenceId($value, $transactionId)
    {
        return $this->idLists[IAmazonReferenceId::TYPE_REFUND]->getNew($value, $transactionId);
    }

    /**
     * @param $localId
     * @param int $itemType one of IAmazonReferenceId::TYPE_*
     *
     * @return IAmazonReferenceId|null
     */
    public function findFromLocalReferenceId($localId, $itemType)
    {
        /** @var $item IAmazonReferenceId */
        foreach ($this->idLists[$itemType] as $item) {
            if ($localId === $item->getLocalId()) {
                reset($this->idLists);

                return $item;
            }
        }

        return null;
    }

    public function persist(Connection $dbal)
    {
        $dbal->beginTransaction();

        $dbal->delete(self::PERSIST_TABLE, array('shop_order_id' => $this->getShopOrderId()));
        reset($this->idLists);
        /** @var $list IAmazonReferenceIdList */
        foreach ($this->idLists as $list) {
            /** @var $item IAmazonReferenceId */
            foreach ($list as $item) {
                $dbal->insert(
                    self::PERSIST_TABLE,
                    array(
                        'id' => \TTools::GetUUID(),
                        'local_id' => $item->getLocalId(),
                        'shop_order_id' => $this->getShopOrderId(),
                        'amazon_order_reference_id' => $this->amazonOrderReferenceId,
                        'amazon_id' => (null !== $item->getAmazonId()) ? $item->getAmazonId() : '',
                        'value' => $item->getValue(),
                        'type' => $item->getType(),
                        'request_mode' => (null !== $item->getRequestMode()) ? (string) $item->getRequestMode() : '',
                        'capture_now' => (true === $item->getCaptureNow()) ? '1' : '0',
                        'pkg_shop_payment_transaction_id' => (null !== $item->getTransactionId(
                                )) ? $item->getTransactionId() : '',
                    )
                );
            }
        }
        reset($this->idLists);

        $dbal->commit();
    }

    /**
     * object will be loaded with the items in the same order as they where created per LIST.
     *
     * @param Connection $dbal
     * @param $shop_order_id
     *
     * @return AmazonReferenceIdManager|null
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromShopOrderId(Connection $dbal, $shop_order_id)
    {
        $data = $dbal->fetchAllAssociative(
            'select * from '.self::PERSIST_TABLE.' WHERE `shop_order_id` = ? ORDER BY `cmsident`',
            array($shop_order_id)
        );
        $manager = self::createFromRecordList($data);

        if (null === $manager) {
            throw new \InvalidArgumentException("unable to find an an id mapper object based on the shop_order_id [{$shop_order_id}]");
        }

        return $manager;
    }

    /**
     * object will be loaded with the items in the same order as they where created per LIST.
     *
     * @param Connection $dbal
     * @param $localId
     *
     * @return AmazonReferenceIdManager|null
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromLocalId(Connection $dbal, $localId)
    {
        $data = $dbal->fetchAllAssociative(
            'SELECT T.*
                                               FROM '.self::PERSIST_TABLE.' AS T
                             INNER JOIN '.self::PERSIST_TABLE.' AS S ON T.shop_order_id = S.shop_order_id
                                  WHERE S.`local_id` = ?
                               ORDER BY T.`cmsident`
                                  ',
            array($localId)
        );
        $manager = self::createFromRecordList($data);

        if (null === $manager) {
            throw new \InvalidArgumentException("unable to find an an id mapper object based on the local reference id [{$localId}]");
        }

        return $manager;
    }

    /**
     * object will be loaded with the items in the same order as they where created per LIST.
     *
     * @param Connection $dbal
     * @param $amazon_order_reference_id
     *
     * @return AmazonReferenceIdManager|null
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromOrderReferenceId(Connection $dbal, $amazon_order_reference_id)
    {
        $data = $dbal->fetchAllAssociative(
            'select * from '.self::PERSIST_TABLE.' WHERE `amazon_order_reference_id` = ? ORDER BY `cmsident`',
            array($amazon_order_reference_id)
        );
        $manager = self::createFromRecordList($data);

        if (null === $manager) {
            throw new \InvalidArgumentException("unable to find an an id mapper object based on the amazonOrderReferenceId [{$amazon_order_reference_id}]");
        }

        return $manager;
    }

    /**
     * the items are loaded in the order they are passed - so make sure to provide them in the correct order.
     *
     * @param array $recordList
     *
     * @return AmazonReferenceIdManager|null
     */
    public static function createFromRecordList(array $recordList)
    {
        $manager = null;
        foreach ($recordList as $row) {
            if (null === $manager) {
                $manager = new self($row['amazon_order_reference_id'], $row['shop_order_id']);
            }
            $item = self::createItemFromRow($row);
            $manager->idLists[$item->getType()]->addItem($item);
        }

        return $manager;
    }

    public function delete(Connection $dbal)
    {
        $dbal->delete(
            self::PERSIST_TABLE,
            array(
                'amazon_order_reference_id' => $this->amazonOrderReferenceId,
                'shop_order_id' => $this->getShopOrderId(),
            )
        );
    }

    /**
     * @param array $row
     *
     * @return AmazonReferenceId
     */
    private static function createItemFromRow(array $row)
    {
        $type = intval($row['type']);
        $item = new AmazonReferenceId(
            $type,
            $row['local_id'],
            floatval($row['value']),
            ('' !== $row['pkg_shop_payment_transaction_id']) ? $row['pkg_shop_payment_transaction_id'] : null
        );
        if ('' !== $row['amazon_id']) {
            $item->setAmazonId($row['amazon_id']);
        }
        if (isset($row['capture_now']) && '1' === $row['capture_now']) {
            $item->setCaptureNow(true);
        } else {
            $item->setCaptureNow(false);
        }
        if (isset($row['request_mode']) && '' !== $row['request_mode']) {
            $item->setRequestMode(intval($row['request_mode']));
        }

        return $item;
    }

    /**
     * @return string
     */
    public function getAmazonOrderReferenceId()
    {
        return $this->amazonOrderReferenceId;
    }

    /**
     * @return string
     */
    public function getShopOrderId()
    {
        return $this->shopOrderId;
    }

    /**
     * returns a list of all authorizations for which there are no captures.
     *
     * @return IAmazonReferenceIdList
     */
    public function getListOfAuthorizations()
    {
        if (0 === count($this->idLists[IAmazonReferenceId::TYPE_AUTHORIZE])) {
            return null;
        }

        return $this->idLists[IAmazonReferenceId::TYPE_AUTHORIZE];
    }

    public function getListOfCaptures()
    {
        if (0 === count($this->idLists[IAmazonReferenceId::TYPE_CAPTURE])) {
            return null;
        }

        return $this->idLists[IAmazonReferenceId::TYPE_CAPTURE];
    }
}
