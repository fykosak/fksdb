<?php

namespace Exports\Formats;

use DOMDocument;
use Exports\IExportFormat;
use Exports\StoredQuery;
use Nette\Object;
use WebService\IXMLNodeSerializer;
use XSLTProcessor;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class XSLFormat extends Object implements IExportFormat {

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

    function __construct(StoredQuery $storedQuery, $xslFile, IXMLNodeSerializer $xmlSerializer) {
        $this->storedQuery = $storedQuery;
        $this->xslFile = $xslFile;
        $this->xmlSerializer = $xmlSerializer;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    public function addParameters($parameters) {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function getDescription() {
        return _('Aplikuje předem danou XSL transformaci na XML tvar exportu.');
    }

    public function getResponse() {
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
        $this->xmlSerializer->fillNode($this->storedQuery, $export, $doc);

        //echo $doc->saveXML();
        // Prepare response
        $response = new PlainTextResponse($proc->transformToXml($doc));
        return $response;
    }

}
