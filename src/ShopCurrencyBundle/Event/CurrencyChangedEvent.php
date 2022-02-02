<?php

namespace ChameleonSystem\ShopCurrencyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CurrencyChangedEvent extends Event
{
    /**
     * @var string|null
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
     * @return string|null
     */
    public function getNewCurrencyId()
    {
        return $this->newCurrencyId;
    }
}
