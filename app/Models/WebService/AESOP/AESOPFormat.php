<?php

namespace FKSDB\Models\WebService\AESOP;

use FKSDB\Models\Exports\Formats\PlainTextResponse;
use Nette\Database\Row;

class AESOPFormat {

    private array $params;
    private iterable $data;
    private array $cools;

    public function __construct(array $params, iterable $data, array $cools) {
        $this->params = $params;
        $this->data = $data;
        $this->cools = $cools;
    }

    public function createResponse(): PlainTextResponse {
        $text = '';

        foreach ($this->params as $key => $value) {
            $text .= $key . "\t" . $value . "\n";
        }
        $text .= "\n";
        /** @var Row $datum */
        $text .= join("\t", $this->cools) . "\n";
        foreach ($this->data as $datum) {
            $text .= join("\t", iterator_to_array($datum->getIterator())) . "\n";
        }
        return new PlainTextResponse($text);
    }

}
