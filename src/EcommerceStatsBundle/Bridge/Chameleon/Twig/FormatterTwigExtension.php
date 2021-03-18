<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatterTwigExtension extends AbstractExtension
{
    static array $weekLocaleFormatMap = ['de' => '%1$d-KW%2d', 'en' => 'W%2$d/%1$d'];

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('format_number', [$this, 'formatNumber'], ['is_safe' => ['html']]),
            new TwigFilter('format_date', [$this, 'formatDate'], ['is_safe' => ['html']]),
            new TwigFilter('format_typed_date', [$this, 'formatTypedDate'], ['is_safe' => ['html']]),
        ];
    }

    public function formatNumber($value, int $fractionDigits = 0): string
    {
        if (null === $value || '' === $value) {
            return '';
        }

        return $this->getLocal()->FormatNumber($value, $fractionDigits);
    }

    public function formatDate($dateTime, string $format = 'Ymd'): string
    {
        static $dateFormatMap = [
            'Y' => \TCMSLocal::DATEFORMAT_SHOW_DATE_YEAR,
            'm' => \TCMSLocal::DATEFORMAT_SHOW_DATE_MONTH,
            'd' => \TCMSLocal::DATEFORMAT_SHOW_DATE_DAY,
        ];

        if (true === $dateTime instanceof \DateTimeInterface) {
            $dateTime = $dateTime->format('Y-m-d');
        }

        if (null === $dateTime || false === $dateTime) {
            return '';
        }

        $local = $this->getLocal();

        if ('W' === $format) {
            $language = $local->GetLanguage();

            $cmsLanguage = \TCMSLanguage::GetNewInstance();
            if (true === $cmsLanguage->LoadFromField('cmsident', $language)) {
                $locale = $cmsLanguage->fieldIso6391;
                $formatStr = self::$weekLocaleFormatMap[$locale] ?? null;
                if (null !== $formatStr) {
                    $dateTimeObj = new \DateTime($dateTime);
                    if (false !== $dateTimeObj) {
                        return sprintf($formatStr, $dateTimeObj->format('Y'), $dateTimeObj->format('W'));
                    }
                }
            }

            return '';
        }

        $formatBits = 0;

        foreach ($dateFormatMap as $symbol => $bits) {
            if (false !== strpos($format, $symbol)) {
                $formatBits |= $bits;
            }
        }

        if (0 === $formatBits) {
            $formatBits = \TCMSLocal::DATEFORMAT_SHOW_DATE;
        }

        return $local->FormatDate($dateTime, $formatBits);
    }

    public function formatTypedDate($dateTime, string $type = 'day'): string
    {
        switch ($type) {
            case 'day': return $this->formatDate($dateTime, 'Y-m-d');
            case 'month': return $this->formatDate($dateTime, 'Y-m');
            case 'week': return $this->formatDate($dateTime, 'W');
            case 'year': return $this->formatDate($dateTime, 'Y');
        }

        return '';
    }

    private function getLocal(): \TCMSLocal
    {
        return \TCMSLocal::GetActive();
    }
}
