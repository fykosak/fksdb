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
     * @phpstan-param array<string,mixed> $data
     */
    public static function fillArrayToNode(array $data, \DOMDocument $doc, \DOMNode $parentNode, bool $recursive = false): void
    {
        foreach ($data as $key => $datum) {
            $childNode = $doc->createElement($key);
            if ($recursive && is_array($datum)) {
                XMLHelper::fillArrayToNode($datum, $doc, $childNode);
            } else {
                $childNode->nodeValue = htmlspecialchars((string)$datum);
            }
            $parentNode->appendChild($childNode);
        }
    }

    /**
     * @throws \DOMException
     * @phpstan-param array<string,array<string,string>> $data
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
