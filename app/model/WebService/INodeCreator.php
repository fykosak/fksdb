<?php

namespace FKSDB\WebService;
/**
 * Interface INodeCreator
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface INodeCreator {
    public function createXMLNode(\DOMDocument $document): \DOMNode;
}
