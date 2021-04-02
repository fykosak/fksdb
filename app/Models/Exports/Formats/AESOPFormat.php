<?php

namespace FKSDB\Models\Exports\Formats;

use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\WebService\XMLNodeSerializer;

class AESOPFormat extends XSLFormat {

    public const ID_SCOPE = 'fksdb.person_id';

    public function __construct(StoredQuery $storedQuery, string $xslFile, XMLNodeSerializer $xmlSerializer) {
        parent::__construct($storedQuery, $xslFile, $xmlSerializer);

        $this->setParameters([
            'version' => 1,
            'date' => date('Y-m-d H:i:s'),
            'id-scope' => self::ID_SCOPE,
        ]);
    }

    public function getResponse(): PlainTextResponse {
        $response = parent::getResponse();

        $parameters = $this->getParameters();
        if (isset($parameters['event'])) {
            $response->setName($parameters['event'] . '.txt');
        }
        return $response;
    }
}
