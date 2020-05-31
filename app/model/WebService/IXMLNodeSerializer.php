<?php

namespace WebService;

use DOMDocument;
use DOMNode;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IXMLNodeSerializer {

    public const EXPORT_FORMAT_1 = 1;
    public const EXPORT_FORMAT_2 = 2;

    /**
     * @param $dataSource
     * @param DOMNode $node
     * @param DOMDocument $doc
     * @param int $formatVersion
     * @return void
     */
    public function fillNode($dataSource, DOMNode $node, DOMDocument $doc, int $formatVersion);
}
