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

    public function fillNode($dataSource, DOMNode $node, DOMDocument $doc);
}
