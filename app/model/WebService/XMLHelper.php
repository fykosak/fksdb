<?php

namespace FKSDB\WebService;

use Nette\InvalidArgumentException;

/**
 * Class XMLHelper
 * @author Michal Červeňák <miso@fykos.cz>
 */
class XMLHelper {
    public function __construct() {
        throw new InvalidArgumentException();
    }

    public static function fillArrayToNode(array $data, \DOMDocument $doc, \DOMNode $parentNode): void {
        foreach ($data as $key => $datum) {
            $childNode = $doc->createElement($key);
            $childNode->nodeValue = $datum;
            $parentNode->appendChild($childNode);
        }
    }

    public static function fillArrayArgumentsToNode(string $attrName, array $data, \DOMDocument $doc, \DOMNode $parentNode): void {
        foreach ($data as $key => $datum) {
            foreach ($datum as $attrValue => $value) {
                $childNode = $doc->createElement($key);
                $childNode->setAttribute($attrName, $attrValue);
                $childNode->nodeValue = $value;
                $parentNode->appendChild($childNode);
            }
        }
    }
}
