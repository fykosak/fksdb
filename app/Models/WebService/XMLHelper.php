<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService;

use Nette\InvalidArgumentException;
use Nette\SmartObject;

class XMLHelper
{
    use SmartObject;

    public function __construct()
    {
        throw new InvalidArgumentException();
    }

    /**
     * @throws \DOMException
     */
    public static function fillArrayToNode(array $data, \DOMDocument $doc, \DOMNode $parentNode): void
    {
        foreach ($data as $key => $datum) {
            $childNode = $doc->createElement($key);
            $childNode->nodeValue = htmlspecialchars((string)$datum);
            $parentNode->appendChild($childNode);
        }
    }

    /**
     * @throws \DOMException
     */
    public static function fillArrayArgumentsToNode(
        string $attrName,
        array $data,
        \DOMDocument $doc,
        \DOMNode $parentNode
    ): void {
        foreach ($data as $key => $datum) {
            foreach ($datum as $attrValue => $value) {
                $childNode = $doc->createElement($key);
                $childNode->setAttribute($attrName, (string)$attrValue);
                $childNode->nodeValue = htmlspecialchars((string)$value);
                $parentNode->appendChild($childNode);
            }
        }
    }
}
