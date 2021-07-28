<?php

namespace FKSDB\Models\WebService;

use Nette\InvalidArgumentException;

class XMLHelper {

    public function __construct() {
        throw new InvalidArgumentException();
    }

    public static function fillArrayToNode(array $data, \DOMDocument $doc, \DOMNode $parentNode): void {
        foreach ($data as $key => $datum) {
            $childNode = $doc->createElement($key);
            $childNode->nodeValue = htmlspecialchars($datum);
            $parentNode->appendChild($childNode);
        }
    }

    public static function fillArrayArgumentsToNode(string $attrName, array $data, \DOMDocument $doc, \DOMNode $parentNode): void {
        foreach ($data as $key => $datum) {
            foreach ($datum as $attrValue => $value) {
                $childNode = $doc->createElement($key);
                $childNode->setAttribute($attrName, $attrValue);
                $childNode->nodeValue = htmlspecialchars($value);
                $parentNode->appendChild($childNode);
            }
        }
    }
}
