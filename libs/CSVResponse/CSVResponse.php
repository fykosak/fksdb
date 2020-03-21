<?php

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
 * @property-read array $data
 * @property-read string $name
 * @property-read bool $addHeading
 * @property-read string $glue
 * @property-read string $contentType
 * @package Nette\Application\Responses
 */
class CSVResponse implements IResponse {
    use SmartObject;
    /** @var array */
    private $data;

    /** @var string */
    private $name;

    /** @var bool */
    private $addHeading = true;

    /** @var string */
    private $glue = ';';

    /** @var string */
    private $charset;

    /** @var string */
    private $contentType;

    /**
     * @var bool
     */
    private $quotes;

    /**
     * @param  string  data (array of arrays - rows/columns)
     * @param  string  imposed file name
     * @param  string  glue between columns (comma or a semi-colon)
     * @param  string  MIME content type
     */
    public function __construct($data, $name = NULL, $charset = NULL, $contentType = NULL) {
        // ----------------------------------------------------
        $this->data = $data;
        $this->name = $name;
        $this->charset = $charset;
//        $this->charset = $charset ? $charset : 'UTF-8';
        $this->contentType = $contentType ? $contentType : 'text/csv';
    }

    /**
     * Returns the file name.
     * @return string
     */
    final public function getName() {
        // ----------------------------------------------------
        return $this->name;
    }

    /**
     * Returns the MIME content type of a downloaded content.
     * @return string
     */
    final public function getContentType() {
        // ----------------------------------------------------
        return $this->contentType;
    }

    public function getGlue() {
        return $this->glue;
    }

    public function setGlue($glue) {
        $this->glue = $glue;
    }

    public function getQuotes() {
        return $this->quotes;
    }

    public function setQuotes($quotes) {
        $this->quotes = $quotes;
    }

    public function getAddHeading() {
        return $this->addHeading;
    }

    public function setAddHeading($addHeading) {
        $this->addHeading = $addHeading;
    }

    /**
     * Sends response to output.
     * @return void
     */
    public function send(IRequest $httpRequest, IHttpResponse $httpResponse) {
        // ----------------------------------------------------
        $httpResponse->setContentType($this->contentType, $this->charset);

        if (empty($this->name)) {
            $httpResponse->setHeader('Content-Disposition', 'attachment');
        } else {
            $httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');
        }

        $data = $this->formatCsv();

        $httpResponse->setHeader('Content-Length', strlen($data));
        print $data;
    }

    public function formatCsv() {
        // ----------------------------------------------------
        if (empty($this->data)) {
            return '';
        }
        if ($this->getQuotes()) {
            $q = '"';
        } else {
            $q = '';
        }

        $csv = array();

        if (!is_array($this->data)) {
            $this->data = iterator_to_array($this->data);
        }
        $firstRow = reset($this->data);

        if ($this->addHeading) {
            if (!is_array($firstRow)) {
                $firstRow = iterator_to_array($firstRow);
            }

            $labels = array_keys($firstRow);
            $csv[] = $q . join($q . $this->glue . $q, $labels) . $q;
        }

        foreach ($this->data as $row) {
            if (!is_array($row)) {
                $row = iterator_to_array($row);
            }
            $escapedRow = array();
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
