<?php

namespace Exports\Formats;

use DOMDocument;
use Exports\IExportFormat;
use Exports\StoredQuery;
use Nette\Application\IResponse;
use Nette\SmartObject;
use WebService\IXMLNodeSerializer;
use XSLTProcessor;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class XSLFormat implements IExportFormat {
    use SmartObject;

    /**
     * @var StoredQuery
     */
    private $storedQuery;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $xslFile;

    /**
     * @var IXMLNodeSerializer
     */
    private $xmlSerializer;

    /**
     * XSLFormat constructor.
     * @param StoredQuery $storedQuery
     * @param $xslFile
     * @param IXMLNodeSerializer $xmlSerializer
     */
    public function __construct(StoredQuery $storedQuery, $xslFile, IXMLNodeSerializer $xmlSerializer) {
        $this->storedQuery = $storedQuery;
        $this->xslFile = $xslFile;
        $this->xmlSerializer = $xmlSerializer;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @param $parameters
     */
    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @param $parameters
     */
    public function addParameters($parameters) {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * @return PlainTextResponse
     */
    public function getResponse(): IResponse {
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
