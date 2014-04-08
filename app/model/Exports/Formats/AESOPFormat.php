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

    function __construct(StoredQuery $storedQuery, $xslFile, IXMLNodeSerializer $xmlSerializer) {
        parent::__construct($storedQuery, $xslFile, $xmlSerializer);

        $queryParameters = $storedQuery->getParameters(true);
        $this->setParameters(array(
            'version' => 1,
            'year' => $queryParameters['ac_year'],
            'date' => date('Y-m-d H:i:s'),
        ));
    }

    public function getDescription() {
        return _('AESOP importní formát získaný XSL transformací.');
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
