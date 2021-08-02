<?php

declare(strict_types=1);

namespace FKSDB\Models\Exports\Formats;

use FKSDB\Models\Exports\ExportFormat;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\WebService\XMLNodeSerializer;
use Nette\SmartObject;

class XSLFormat implements ExportFormat
{
    use SmartObject;

    private StoredQuery $storedQuery;

    private array $parameters = [];

    private string $xslFile;

    private XMLNodeSerializer $xmlSerializer;

    public function __construct(StoredQuery $storedQuery, string $xslFile, XMLNodeSerializer $xmlSerializer)
    {
        $this->storedQuery = $storedQuery;
        $this->xslFile = $xslFile;
        $this->xmlSerializer = $xmlSerializer;
    }

    public function addParameters(array $parameters): void
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function getResponse(): PlainTextResponse
    {
        // Prepare XSLT processor
        $xsl = new \DOMDocument();
        $xsl->load($this->xslFile);

        $proc = new \XSLTProcessor();
        $proc->importStylesheet($xsl);

        foreach ($this->getParameters() as $key => $value) {
            $proc->setParameter('', $key, $value);
        }

        // Render export into XML
        $doc = new \DOMDocument();
        $export = $doc->createElement('export');
        $doc->appendChild($export);
        $this->xmlSerializer->fillNode($this->storedQuery, $export, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);

        // Prepare response
        return new PlainTextResponse($proc->transformToXml($doc));
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
