<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\WebService;

interface NodeCreator
{
    public function createXMLNode(\DOMDocument $document): \DOMElement;
}
