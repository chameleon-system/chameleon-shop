<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class ExposeAsApi
{
    public function __construct(public ?string $description = null)
    {
    }
}