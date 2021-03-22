<?php declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\DataModel;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CsvResponse extends Response
{

    public static function fromRows(string $fileName, array $data, string $separator = ';'): CsvResponse
    {
        $csv = fopen('php://temp/maxmemory:'. 1024 * 1024, 'r+');

        foreach ($data as $row) {
            fputcsv($csv, $row, $separator);
        }

        rewind($csv);

        return new CsvResponse(
            $fileName,
            (string) stream_get_contents($csv)
        );
    }

    public function __construct(string $fileName, string $content, $status = 200, $headers = [])
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Pragma', 'public');
        $this->headers->set('Expires', 0);
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

    /**
     * @return never-returns - Uses `exit()` to finish the current request
     */
    public function sendAndExit(): void
    {
        $this->send();
        exit(0);
    }
}
