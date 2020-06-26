<?php

namespace Exports\Formats;

use FKSDB\StoredQuery\StoredQuery;
use Nette\Application\IResponse;
use WebService\IXMLNodeSerializer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AESOPFormat extends XSLFormat {

    const ID_SCOPE = 'fksdb.person_id';

    /**
     * AESOPFormat constructor.
     * @param StoredQuery $storedQuery
     * @param $xslFile
     * @param IXMLNodeSerializer $xmlSerializer
     */
    public function __construct(StoredQuery $storedQuery, $xslFile, IXMLNodeSerializer $xmlSerializer) {
        parent::__construct($storedQuery, $xslFile, $xmlSerializer);

        $this->setParameters([
            'version' => 1,
            'date' => date('Y-m-d H:i:s'),
            'id-scope' => self::ID_SCOPE,
        ]);
    }

    /**
     * @return PlainTextResponse
     */
    public function getResponse(): IResponse {
        $response = parent::getResponse();

        $parameters = $this->getParameters();
        if (isset($parameters['event'])) {
            $response->setName($parameters['event'] . '.txt');
        }
        return $response;
    }
}
