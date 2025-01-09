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

use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
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
            ),
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
        return $this->getColorGeneratorService()->generateColor($index, $total);
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

    private function getColorGeneratorService(): ColorGeneratorServiceInterface
    {
        return ServiceLocator::get('chameleon_system_cms_dashboard.service.color_generator_service');
    }
}
