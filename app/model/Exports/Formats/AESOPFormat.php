<?php

namespace Exports\Formats;

use Exports\StoredQuery;
use WebService\IXMLNodeSerializer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class AESOPFormat extends XSLFormat {

    const ID_SCOPE = 'fksdb.person_id';

    function __construct(StoredQuery $storedQuery, $xslFile, IXMLNodeSerializer $xmlSerializer) {
        parent::__construct($storedQuery, $xslFile, $xmlSerializer);

        $this->setParameters(array(
            'version' => 1,
            'date' => date('Y-m-d H:i:s'),
            'id-scope' => self::ID_SCOPE,
        ));
    }

    public function getResponse() {
        $response = parent::getResponse();

        $parameters = $this->getParameters();
        if (isset($parameters['event'])) {
            $response->setName($parameters['event'] . '.txt');
        }
        return $response;
    }

}
