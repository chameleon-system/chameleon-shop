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

namespace ChameleonSystem\EcommerceStatsBundle\Library\DataModel;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use function Safe\fopen;
use function Safe\fputcsv;
use function Safe\rewind;
use function Safe\stream_get_contents;

class CsvResponse extends Response
{
    /**
     * @param string[][] $data
     */
    public static function fromRows(string $fileName, array $data, string $separator = ';'): CsvResponse
    {
        $csv = fopen('php://temp/maxmemory:'. 1024 * 1024, 'r+');

        foreach ($data as $row) {
            fputcsv($csv, $row, $separator);
        }

        rewind($csv);

        return new CsvResponse(
            $fileName,
            stream_get_contents($csv)
        );
    }

    /**
     * @param array<string, string> $headers
     */
    public function __construct(string $fileName, string $content, $status = 200, $headers = [])
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Pragma', 'public');
        $this->headers->set('Expires', '0');
        $this->headers->set('Content-Type', 'text/csv');
        $this->headers->set('Content-Transfer-Encoding', 'binary');

        // Disable caching
        $this->setPrivate();
        $this->headers->addCacheControlDirective('must-revalidate');
        $this->headers->addCacheControlDirective('post-check', '0');
        $this->headers->addCacheControlDirective('pre-check', '0');
        $this->headers->addCacheControlDirective('private');

        // Force download
        $this->headers->set('Content-Disposition', $this->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        ));
    }
}
