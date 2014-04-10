<?php

namespace Exports;

use Exports\Formats\AESOPFormat;
use FKS\Config\Expressions\Helpers;
use FKS\Config\GlobalParameters;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExportFormatFactory extends Object {

    /**
     * @var GlobalParameters
     */
    private $globalParameters;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var StoredQueryFactory
     */
    private $storedQueryFactory;

    function __construct(GlobalParameters $globalParameters, Container $container, StoredQueryFactory $storedQueryFactory) {
        $this->globalParameters = $globalParameters;
        $this->container = $container;
        $this->storedQueryFactory = $storedQueryFactory;
    }

    /**
     * 
     * @param type $name
     * @param \Exports\StoredQuery $storedQuery
     * @return IExportFormat
     */
    public function createFormat($name, StoredQuery $storedQuery) {
        switch (strtolower($name)) {
            case 'aesop':
                return $this->createAesop($name, $storedQuery);
                break;
            default:
                throw new InvalidArgumentException('Unknown format \'' . $name . '\'.');
        }
    }

    public function getFormats(StoredQuery $storedQuery) {
        $queryPattern = $storedQuery->getQueryPattern();
        $qid = isset($queryPattern->qid) ? $queryPattern->qid : null;
        if (!$qid) {
            return array();
        } else {
            $formats = Arrays::get($this->globalParameters['exports']['specialFormats'], $qid, array());
            return Helpers::evalExpressionArray($formats, $this->container);
        }
    }

    private function createAesop($name, StoredQuery $storedQuery) {
        $parameters = $this->globalParameters['exports']['formats'][$name];
        $queryParameters = $storedQuery->getParameters(true);

        $xslFile = $parameters['template'];
        $contestName = $this->globalParameters['contestMapping'][$queryParameters['contest']];
        $maintainer = Arrays::get($parameters, 'maintainer', $this->globalParameters['exports']['maintainer']);
        $eventId = sprintf($parameters['idMask'], $contestName, $queryParameters['year'], $queryParameters['category']);




        $format = new AESOPFormat($storedQuery, $xslFile, $this->storedQueryFactory);
        $format->addParameters(array(
            'errors-to' => $maintainer,
            'event' => $eventId,
            'year' => $queryParameters['ac_year'],
            'max-rank' => $storedQuery->getCount(),
            'max-points' => $storedQuery->getPostProcessing()->getMaxPoints($this->container->getByType('ServiceTask')),
        ));

        return $format;
    }

}

