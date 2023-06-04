<?php

declare(strict_types=1);

namespace FKSDB\Models\Exports;

use FKSDB\Models\Exports\Formats\CSVFormat;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

class ExportFormatFactory
{
    use SmartObject;

    public const CSV_HEADLESS = 'csv';
    public const CSV_HEAD = 'csvh';
    public const CSV_QUOTE_HEAD = 'csvqh';

    private Container $container;
    private StoredQueryFactory $storedQueryFactory;
    private EventService $eventService;
    private ContestService $contestService;
    public array $defaultFormats;

    public function __construct(
        Container $container,
        StoredQueryFactory $storedQueryFactory,
        EventService $eventService,
        ContestService $contestService
    ) {
        $this->container = $container;
        $this->storedQueryFactory = $storedQueryFactory;
        $this->eventService = $eventService;
        $this->contestService = $contestService;
        $this->defaultFormats = [
            self::CSV_HEAD => _('Save CSV'),
            self::CSV_HEADLESS => _('Save CSV (without head)'),
            self::CSV_QUOTE_HEAD => _('Save CSV with quotes'),
        ];
    }

    public function createFormat(string $name, StoredQuery $storedQuery): ExportFormat
    {
        switch (strtolower($name)) {
            case self::CSV_HEADLESS:
                return $this->createCSV($storedQuery, false);
            case self::CSV_HEAD:
                return $this->createCSV($storedQuery, true);
            case self::CSV_QUOTE_HEAD:
                return $this->createCSV($storedQuery, true, true);
            default:
                throw new InvalidArgumentException(sprintf(_('Unknown format "%s".'), $name));
        }
    }

    private function createCSV(
        StoredQuery $storedQuery,
        bool $header,
        bool $quote = CSVFormat::DEFAULT_QUOTE
    ): CSVFormat {
        return new CSVFormat($storedQuery, $header, CSVFormat::DEFAULT_DELIMITER, $quote);
    }
}
