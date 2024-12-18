<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\StatsProvider;

use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsProviderInterface;

class StatsProviderCollection
{
    /**
     * @var StatsProviderInterface[]
     */
    private array $providers = [];

    public function addProvider(StatsProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * @return StatsProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
