<?php

namespace Exports;

use CSVFormat;
use Exports\Formats\AESOPFormat;
use FKSDB\Config\Expressions\Helpers;
use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Services\ServiceTask;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExportFormatFactory {
    use SmartObject;

    const AESOP = 'aesop';
    const CSV_HEADLESS = 'csv';
    const CSV_HEAD = 'csvh';
    const CSV_QUOTE_HEAD = 'csvqh';

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

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var ServiceContest
     */
    private $serviceContest;
    private $defaultFormats;

    /**
     * ExportFormatFactory constructor.
     * @param GlobalParameters $globalParameters
     * @param Container $container
     * @param StoredQueryFactory $storedQueryFactory
     * @param ServiceEvent $serviceEvent
     * @param ServiceContest $serviceContest
     */
    public function __construct(GlobalParameters $globalParameters, Container $container, StoredQueryFactory $storedQueryFactory, ServiceEvent $serviceEvent, ServiceContest $serviceContest) {
        $this->globalParameters = $globalParameters;
        $this->container = $container;
        $this->storedQueryFactory = $storedQueryFactory;
        $this->serviceEvent = $serviceEvent;
        $this->serviceContest = $serviceContest;
        $this->defaultFormats = [
            self::CSV_HEAD => _('Save CSV'),
            self::CSV_HEADLESS => _('Uložit CSV (bez hlavičky)'),
            self::CSV_QUOTE_HEAD => _('Uložit CSV s uvozovkami')
        ];
    }

    /**
     *
     * @param mixed $name
     * @param StoredQuery $storedQuery
     * @return IExportFormat
     */
    public function createFormat($name, StoredQuery $storedQuery) {
        switch (strtolower($name)) {
            case self::AESOP:
                return $this->createAesop($name, $storedQuery);
            case self::CSV_HEADLESS:
                return $this->createCSV($storedQuery, false);
            case self::CSV_HEAD:
                return $this->createCSV($storedQuery, true);
            case self::CSV_QUOTE_HEAD:
                return $this->createCSV($storedQuery, true, true);
            default:
                throw new InvalidArgumentException('Unknown format \'' . $name . '\'.');
        }
    }

    /**
     * @param StoredQuery $storedQuery
     * @return array|mixed
     */
    public function getFormats(StoredQuery $storedQuery) {
        $queryPattern = $storedQuery->getQueryPattern();
        $qid = isset($queryPattern->qid) ? $queryPattern->qid : null;
        if (!$qid) {
            return $this->defaultFormats;
        } else {
            $formats = Arrays::get($this->globalParameters['exports']['specialFormats'], $qid, []);
            return $this->defaultFormats + Helpers::evalExpressionArray($formats, $this->container);
        }
    }

    /**
     * @param $name
     * @param StoredQuery $storedQuery
     * @return AESOPFormat
     */
    private function createAesop($name, StoredQuery $storedQuery) {
        $parameters = $this->globalParameters['exports']['formats'][$name];
        $queryParameters = $storedQuery->getParameters(true);

        $qid = $storedQuery->getQueryPattern()->qid;

        $xslFile = $parameters['template'];
        $contestName = $this->globalParameters['contestMapping'][$queryParameters['contest']];
        $maintainer = Arrays::get($parameters, 'maintainer', $this->globalParameters['exports']['maintainer']);
        $category = Arrays::get($queryParameters, 'category', null);
        $eventId = sprintf($parameters[$qid]['idMask'], $contestName, $queryParameters['year'], $category);

        $format = new AESOPFormat($storedQuery, $xslFile, $this->storedQueryFactory);
        $format->addParameters([
            'errors-to' => $maintainer,
            'event' => $eventId,
            'year' => $queryParameters['ac_year'],
        ]);

        if (array_key_exists('eventTypeId', $parameters[$qid])) {
            $contest = $this->serviceContest->findByPrimary($queryParameters['contest']);
            $event = $this->serviceEvent->getByEventTypeId($contest, $queryParameters['year'], $parameters[$qid]['eventTypeId']);
            $format->addParameters([
                'start-date' => $event->begin->format('Y-m-d'),
                'end-date' => $event->end->format('Y-m-d'),
            ]);
        }

        // temporary 'bugfix' for team competition max-rank computation
        if ($qid != 'aesop.fol' && $qid != 'aesop.klani.ct' && $qid != 'aesop.klani.uc') {
            $format->addParameters([
                'max-rank' => $storedQuery->getCount(),
            ]);
        }

        if ($qid == 'aesop.ct') {
            $format->addParameters([
                'max-points' => $storedQuery->getPostProcessing()
                    ->getMaxPoints($this->container->getByType(ServiceTask::class)),
            ]);
        }

        return $format;
    }

    /**
     * @param StoredQuery $storedQuery
     * @param $header
     * @param bool $quote
     * @return CSVFormat
     */
    private function createCSV(StoredQuery $storedQuery, $header, $quote = CSVFormat::DEFAULT_QUOTE): CSVFormat {
        return new CSVFormat($storedQuery, $header, CSVFormat::DEFAULT_DELIMITER, $quote);
    }

}
