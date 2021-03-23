<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatterTwigExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'chameleon_system_ecommerce_stats_format_number',
                [$this, 'formatNumber'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function formatNumber($value, int $fractionDigits = 0): string
    {
        if (null === $value || '' === $value) {
            return '';
        }

        return $this->getLocal()->FormatNumber($value, $fractionDigits);
    }

    private function getLocal(): \TCMSLocal
    {
        return \TCMSLocal::GetActive();
    }
}
