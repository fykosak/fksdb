<?php

use Exports\IExportFormat;
use Exports\StoredQuery;
use Nette\Application\IResponse;
use Nette\SmartObject;
use PePa\CSVResponse;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CSVFormat implements IExportFormat {
    use SmartObject;

    const DEFAULT_DELIMITER = ';';
    const DEFAULT_QUOTE = false;

    /** @var StoredQuery */
    private $storedQuery;
    /** @var string */
    private $delimiter;
    /** @var bool */
    private $quote;
    /** @var bool */
    private $header;

    /**
     * CSVFormat constructor.
     * @param StoredQuery $storedQuery
     * @param bool $header
     * @param string $delimiter
     * @param bool $quote
     */
    public function __construct(StoredQuery $storedQuery, bool $header, string $delimiter = self::DEFAULT_DELIMITER, bool $quote = self::DEFAULT_QUOTE) {
        $this->storedQuery = $storedQuery;
        $this->delimiter = $delimiter;
        $this->quote = $quote;
        $this->header = $header;
    }

    /**
     * @return CSVResponse
     */
    public function getResponse(): IResponse {
        $data = $this->storedQuery->getData();
        $name = isset($this->storedQuery->getQueryPattern()->name) ? $this->storedQuery->getQueryPattern()->name : 'adhoc';
        $name .= '.csv';
        $response = new CSVResponse($data, $name);
        $response->setAddHeading($this->header);
        $response->setQuotes($this->quote);
        $response->setGlue($this->delimiter);
        return $response;
    }
}
