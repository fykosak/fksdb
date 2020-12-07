<?php

namespace FKSDB\Model\Exports\Formats;

use DOMDocument;
use FKSDB\Model\Exports\IExportFormat;
use FKSDB\Model\StoredQuery\StoredQuery;
use Nette\SmartObject;
use FKSDB\Model\WebService\IXMLNodeSerializer;
use XSLTProcessor;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class XSLFormat implements IExportFormat {
    use SmartObject;

    private StoredQuery $storedQuery;

    private array $parameters = [];

    private string $xslFile;

    private IXMLNodeSerializer $xmlSerializer;

    public function __construct(StoredQuery $storedQuery, string $xslFile, IXMLNodeSerializer $xmlSerializer) {
        $this->storedQuery = $storedQuery;
        $this->xslFile = $xslFile;
        $this->xmlSerializer = $xmlSerializer;
    }

    public function getParameters(): array {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void {
        $this->parameters = $parameters;
    }

    public function addParameters(array $parameters): void {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function getResponse(): PlainTextResponse {
        // Prepare XSLT processor
        $xsl = new DOMDocument();
        $xsl->load($this->xslFile);

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);

        foreach ($this->getParameters() as $key => $value) {
            $proc->setParameter('', $key, $value);
        }

        // Render export into XML
        $doc = new DOMDocument();
        $export = $doc->createElement('export');
        $doc->appendChild($export);
        $this->xmlSerializer->fillNode($this->storedQuery, $export, $doc, IXMLNodeSerializer::EXPORT_FORMAT_1);

        // Prepare response
        return new PlainTextResponse($proc->transformToXml($doc));
    }
}
