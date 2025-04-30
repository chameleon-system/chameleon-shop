<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopOrderStatusDataEndPoint extends AbstractPkgCmsCoreParameterContainer implements IPkgShopOrderStatusData
{
    /**
     * @var TdbShopOrder
     */
    private $order;
    /**
     * @var string
     */
    private $shopOrderStatusCode;

    /**
     * @var TdbShopOrderStatusCode
     */
    private $shopOrderStatusCodeObject;

    /**
     * @var int
     */
    private $statusTimestamp;
    /**
     * @var string|null
     */
    private $info;

    /**
     * @var TPkgShopOrderStatusItemData[]
     */
    private $items = [];

    /** @var Symfony\Component\HttpFoundation\ParameterBag|null */
    private $statusData;

    /**
     * @param string $shopOrderStatusCode - system_name of shop_order_status_code
     * @param int $iStatusTimestamp
     * @param string|null $info
     */
    public function __construct(TdbShopOrder $order, $shopOrderStatusCode, $iStatusTimestamp, $info = null)
    {
        $this
            ->setOrder($order)
            ->setStatusTimestamp($iStatusTimestamp)
            ->setShopOrderStatusCode($shopOrderStatusCode)
            ->setInfo($info);
    }

    /**
     * @param string|null $info
     *
     * @return $this
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @param TdbShopOrder $shopOrder
     *
     * @return $this
     */
    public function setOrder($shopOrder)
    {
        $this->order = $shopOrder;

        return $this;
    }

    /**
     * @param string $shopOrderStatusCode
     *
     * @return $this
     */
    public function setShopOrderStatusCode($shopOrderStatusCode)
    {
        $this->shopOrderStatusCode = $shopOrderStatusCode;

        return $this;
    }

    /**
     * @param int $statusTimestamp
     *
     * @return $this
     */
    public function setStatusTimestamp($statusTimestamp)
    {
        $this->statusTimestamp = $statusTimestamp;

        return $this;
    }

    /**
     * @return $this
     */
    public function addItem(TPkgShopOrderStatusItemData $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return TdbShopOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getShopOrderStatusCode()
    {
        return $this->shopOrderStatusCode;
    }

    /**
     * @return int
     */
    public function getStatusTimestamp()
    {
        return $this->statusTimestamp;
    }

    /**
     * @return array of TPkgShopOrderStatusItemData
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param bool $bRefresh
     *
     * @return TdbShopOrderStatusCode
     *
     * @throws TPkgShopOrderStatusException_OrderStatusCodeNotFound
     */
    public function getShopOrderStatusCodeObject($bRefresh = false)
    {
        if (true === $bRefresh) {
            $this->shopOrderStatusCodeObject = null;
        }
        if (null === $this->shopOrderStatusCodeObject) {
            $this->shopOrderStatusCodeObject = TdbShopOrderStatusCode::GetNewInstance();
            $aFilter = [
                'shop_id' => $this->getOrder()->fieldShopId,
                'system_name' => $this->getShopOrderStatusCode(),
            ];
            if (false === $this->shopOrderStatusCodeObject->LoadFromFields($aFilter)) {
                $shopId = $this->getOrder()->fieldShopId;
                $e = new TPkgShopOrderStatusException_OrderStatusCodeNotFound(
                    "no order status code in shop [{$shopId}] with system_name [{$this->getShopOrderStatusCode()}]",
                    ['orderStatus' => $this]
                );
                $e->setStatusCode($this->getShopOrderStatusCode())
                    ->setShopId($this->getOrder()->fieldShopId);
                throw $e;
            }
        }

        return $this->shopOrderStatusCodeObject;
    }

    /**
     * @return Symfony\Component\HttpFoundation\ParameterBag|null
     */
    public function getStatusData()
    {
        return $this->statusData;
    }

    /**
     * @param Symfony\Component\HttpFoundation\ParameterBag $statusData
     *
     * @return $this
     */
    public function setStatusData($statusData)
    {
        $this->statusData = $statusData;

        return $this;
    }

    /**
     * use $this->addRequirement to add the requirements of the container.
     */
    protected function defineRequirements()
    {
        $this
            ->addRequirement(new TPkgCmsCoreParameterContainerParameterDefinition('order', true, 'TdbShopOrder'))
            ->addRequirement(new TPkgCmsCoreParameterContainerParameterDefinition('shopOrderStatusCode', true))
            ->addRequirement(new TPkgCmsCoreParameterContainerParameterDefinition('statusTimestamp', true));
    }

    /**
     * returns an assoc array with the data of the object mapped to to the tdb fields.
     *
     * @return array
     */
    public function getDataAsTdbArray()
    {
        $data = (null === $this->getStatusData()) ? [] : $this->getStatusData()->all();
        $data = serialize($data);

        return [
            'shop_order_id' => $this->getOrder()->id,
            'shop_order_status_code_id' => $this->getShopOrderStatusCodeObject()->id,
            'status_date' => date('Y-m-d H:i:s', $this->getStatusTimestamp()),
            'info' => $this->getInfo(),
            'data' => $data,
        ];
    }
}
