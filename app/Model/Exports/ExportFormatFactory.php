<?php

namespace FKSDB\Model\Exports;

use FKSDB\Model\Exports\Formats\CSVFormat;
use FKSDB\Model\Exports\Formats\AESOPFormat;
use FKSDB\Config\Expressions\Helpers;
use FKSDB\Model\ORM\Services\ServiceContest;
use FKSDB\Model\ORM\Services\ServiceEvent;
use FKSDB\Model\ORM\Services\ServiceTask;
use FKSDB\Model\StoredQuery\StoredQuery;
use FKSDB\Model\StoredQuery\StoredQueryFactory;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExportFormatFactory {
    use SmartObject;

    public const AESOP = 'aesop';
    public const CSV_HEADLESS = 'csv';
    public const CSV_HEAD = 'csvh';
    public const CSV_QUOTE_HEAD = 'csvqh';

    private Container $container;

    private StoredQueryFactory $storedQueryFactory;

    private ServiceEvent $serviceEvent;

    private ServiceContest $serviceContest;

    private array $defaultFormats;

    public function __construct(Container $container, StoredQueryFactory $storedQueryFactory, ServiceEvent $serviceEvent, ServiceContest $serviceContest) {
        $this->container = $container;
        $this->storedQueryFactory = $storedQueryFactory;
        $this->serviceEvent = $serviceEvent;
        $this->serviceContest = $serviceContest;
        $this->defaultFormats = [
            self::CSV_HEAD => _('Save CSV'),
            self::CSV_HEADLESS => _('Save CSV (without head)'),
            self::CSV_QUOTE_HEAD => _('Save CSV with quotes'),
        ];
    }

    public function createFormat(string $name, StoredQuery $storedQuery): IExportFormat {
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
     * @return array
     * @throws \ReflectionException
     */
    public function getFormats(StoredQuery $storedQuery): array {
        $qid = $storedQuery->getQId();
        if (!$qid) {
            return $this->defaultFormats;
        } else {
            $formats = $this->container->getParameters()['exports']['specialFormats'][$qid] ?? [];
            return $this->defaultFormats + Helpers::evalExpressionArray($formats, $this->container);
        }
    }

    private function createAesop(string $name, StoredQuery $storedQuery): AESOPFormat {
        $parameters = $this->container->getParameters()['exports']['formats'][$name];
        $queryParameters = $storedQuery->getParameters(true);

        $qid = $storedQuery->getQId();

        $xslFile = $parameters['template'];
        $contestName = $this->container->getParameters()['contestMapping'][$queryParameters['contest']];
        $maintainer = $parameters['maintainer'] ?? $this->container->getParameters()['exports']['maintainer'];
        $category = $queryParameters['category'] ?? null;
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

    private function createCSV(StoredQuery $storedQuery, bool $header, bool $quote = CSVFormat::DEFAULT_QUOTE): CSVFormat {
        return new CSVFormat($storedQuery, $header, CSVFormat::DEFAULT_DELIMITER, $quote);
    }
}
