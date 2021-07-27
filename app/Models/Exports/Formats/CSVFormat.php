<?php

namespace FKSDB\Models\Exports\Formats;

use FKSDB\Models\Exports\ExportFormat;
use FKSDB\Models\StoredQuery\StoredQuery;
use Nette\SmartObject;
use PePa\CSVResponse;

class CSVFormat implements ExportFormat {
    use SmartObject;

    public const DEFAULT_DELIMITER = ';';
    public const DEFAULT_QUOTE = false;

    private StoredQuery $storedQuery;

    private string $delimiter;

    private bool $quote;

    private bool $header;

    public function __construct(StoredQuery $storedQuery, bool $header, string $delimiter = self::DEFAULT_DELIMITER, bool $quote = self::DEFAULT_QUOTE) {
        $this->storedQuery = $storedQuery;
        $this->delimiter = $delimiter;
        $this->quote = $quote;
        $this->header = $header;
    }

    public function getResponse(): CSVResponse {
        $data = $this->storedQuery->getData();
        $name = $this->storedQuery->getName();
        $name .= '.csv';
        $response = new CSVResponse($data, $name);
        $response->setAddHeading($this->header);
        $response->setQuotes($this->quote);
        $response->setGlue($this->delimiter);
        return $response;
    }
}
