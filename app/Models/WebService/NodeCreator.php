<?php

namespace FKSDB\Models\WebService;
/**
 * Interface INodeCreator
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface NodeCreator {
    public function createXMLNode(\DOMDocument $document): \DOMElement;
}
