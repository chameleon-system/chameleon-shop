<?php

namespace ChameleonSystem\ShopCurrencyBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class CurrencyChangedEvent extends Event
{
    /**
     * @var null|string
     */
    private $newCurrencyId;

    /**
     * @param string|null $newCurrencyId
     */
    public function __construct($newCurrencyId)
    {
        $this->newCurrencyId = $newCurrencyId;
    }

    /**
     * @return null|string
     */
    public function getNewCurrencyId()
    {
        return $this->newCurrencyId;
    }
}
