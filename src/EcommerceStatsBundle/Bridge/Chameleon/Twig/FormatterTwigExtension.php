<?php

declare(strict_types=1);

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
use Twig\TwigFunction;

class FormatterTwigExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'chameleon_system_ecommerce_stats_format_number',
                [$this, 'formatNumber'],
                ['is_safe' => ['html']]
            )
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('generate_color', [$this, 'generateColor']),
        ];
    }

    /**
     * @param string|int|float|null $value
     */
    public function formatNumber($value, int $fractionDigits = 0): string
    {
        if (null === $value || '' === $value) {
            return '';
        }

        $local = $this->getLocal();
        if (null === $local) {
            return (string) $value;
        }

        return $local->FormatNumber($value, $fractionDigits);
    }

    public function generateColor(int $index, int $total): string
    {
        $palette = [
            '#20a8d8', // Blue
            '#6610f2', // Indigo
            '#6f42c1', // Purple
            '#e83e8c', // Pink
            '#f86c6b', // Red
            '#f8cb00', // Orange
            '#ffc107', // Yellow
            '#4dbd74', // Green
            '#20c997', // Teal
            '#17a2b8', // Cyan
            '#73818f', // Gray
            '#63c2de', // Light-blue
            '#20a8d8', // Primary
            '#c8ced3', // Secondary
            '#4dbd74', // Success
            '#63c2de', // Info
            '#ffc107', // Warning
            '#f86c6b', // Danger
            '#f0f3f5', // Light
        ];

        $paletteSize = count($palette);

        if ($total <= $paletteSize) {
            // small color palette is enough
            return $palette[$index % $paletteSize];
        }

        // lots of colors needed, interpolate between them
        $step = ($index / max(1, $total - 1)) * ($paletteSize - 1); // Position zwischen den Farben
        $startIndex = floor($step);
        $endIndex = ceil($step);

        $startColor = $this->hexToRgb($palette[$startIndex]);
        $endColor = $this->hexToRgb($palette[$endIndex]);

        $factor = $step - $startIndex;
        $r = (1 - $factor) * $startColor[0] + $factor * $endColor[0];
        $g = (1 - $factor) * $startColor[1] + $factor * $endColor[1];
        $b = (1 - $factor) * $startColor[2] + $factor * $endColor[2];

        return sprintf('#%02x%02x%02x', (int)$r, (int)$g, (int)$b);
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function getLocal(): ?\TCMSLocal
    {
        return \TCMSLocal::GetActive() ?: null;
    }
}
