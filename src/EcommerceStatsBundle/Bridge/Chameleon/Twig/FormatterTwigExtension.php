<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
