<?php

namespace FKSDB\Models\WebService;

interface NodeCreator {
    public function createXMLNode(\DOMDocument $document): \DOMElement;
}
