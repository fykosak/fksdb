<?php

declare(strict_types=1);

namespace PePa;

use Nette\Application\IResponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse as IHttpResponse;
use Nette\SmartObject;

/**
 * CSV download response.
 *
 * @author     Petr 'PePa' Pavel
 * @author     Michal Koutný <michal@fykos.cz>
 * @see http://addons.nette.org/cs/csvresponse
 * @see http://tools.ietf.org/html/rfc4180 (not fully implemented)
 *
 * @package Nette\Application\Responses
 * @template TData
 */
class CSVResponse implements IResponse
{
    use SmartObject;

    /** @phpstan-var iterable<TData> */
    private iterable $data;

    private ?string $name;

    private bool $addHeading = true;

    private string $glue = ';';

    private ?string $charset;

    private string $contentType;

    private bool $quotes;

    /**
     * @phpstan-param iterable<TData> $data
     */
    public function __construct(
        iterable $data,
        string $name = null,
        ?string $charset = null,
        ?string $contentType = null
    ) {
        // ----------------------------------------------------
        $this->data = $data;
        $this->name = $name;
        $this->charset = $charset;
//        $this->charset = $charset ? $charset : 'UTF-8';
        $this->contentType = $contentType ? $contentType : 'text/csv';
    }

    /**
     * Returns the file name.
     */
    final public function getName(): ?string
    {
        // ----------------------------------------------------
        return $this->name;
    }

    /**
     * Returns the MIME content type of a downloaded content.
     */
    final public function getContentType(): string
    {
        // ----------------------------------------------------
        return $this->contentType;
    }

    public function getGlue(): string
    {
        return $this->glue;
    }

    public function setGlue(string $glue): void
    {
        $this->glue = $glue;
    }

    public function getQuotes(): bool
    {
        return $this->quotes;
    }

    public function setQuotes(bool $quotes): void
    {
        $this->quotes = $quotes;
    }

    public function getAddHeading(): bool
    {
        return $this->addHeading;
    }

    public function setAddHeading(bool $addHeading): void
    {
        $this->addHeading = $addHeading;
    }

    /**
     * Sends response to output.
     */
    public function send(IRequest $httpRequest, IHttpResponse $httpResponse): void
    {
        // ----------------------------------------------------
        $httpResponse->setContentType($this->contentType, $this->charset);

        if (empty($this->name)) {
            $httpResponse->setHeader('Content-Disposition', 'attachment');
        } else {
            $httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');
        }

        $data = $this->formatCsv();

        $httpResponse->setHeader('Content-Length', (string)strlen($data));
        print $data;
    }

    public function formatCsv(): string
    {
        // ----------------------------------------------------
        if (empty($this->data)) {
            return '';
        }
        if ($this->getQuotes()) {
            $q = '"';
        } else {
            $q = '';
        }

        $csv = [];

        if (!is_array($this->data)) {
            $this->data = iterator_to_array($this->data);
        }
        $firstRow = reset($this->data);

        if ($this->addHeading) {
            if (!is_array($firstRow)) {
                $firstRow = iterator_to_array($firstRow); //@phpstan-ignore-line
            }

            $labels = array_keys($firstRow);
            $csv[] = $q . join($q . $this->glue . $q, $labels) . $q;
        }

        foreach ($this->data as $row) {
            if (!is_array($row)) {
                $row = iterator_to_array($row);
            }
            $escapedRow = [];
            foreach ($row as $key => $value) {
                $value = preg_replace('/[\r\n]+/', ' ', $value);  // remove line endings
                if ($q) {
                    $value = str_replace($q, $q . $q, $value);          // escape double quotes
                }
                $escapedRow[] = $value;
            }
            $csv[] = $q . join($q . $this->glue . $q, $escapedRow) . $q;
        }

        return join("\r\n", $csv);
    }
}
