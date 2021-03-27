<?php

namespace FKSDB\Models\WebService;

use DOMDocument;
use DOMElement;
use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\Results\Models\AbstractResultsModel;
use FKSDB\Models\Results\Models\BrojureResultsModel;
use FKSDB\Models\Results\ResultsModelFactory;
use FKSDB\Models\Stats\StatsModelFactory;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use InvalidArgumentException;
use Nette\Application\BadRequestException;
use Nette\Security\AuthenticationException;
use SoapFault;
use SoapVar;
use stdClass;
use Tracy\Debugger;

/**
 * Web service provider for fksdb.wdsl
 * @author michal
 */
class WebServiceModel {

    /** @var array  contest name => contest_id */
    private array $inverseContestMap;
    private ServiceContest $serviceContest;
    private ResultsModelFactory $resultsModelFactory;
    private StatsModelFactory $statsModelFactory;
    private ModelLogin $authenticatedLogin;
    private PasswordAuthenticator $authenticator;
    private StoredQueryFactory $storedQueryFactory;
    private ContestAuthorizator $contestAuthorizator;
    private EventSoapFactory $eventSoapFactory;

    public function __construct(
        array $inverseContestMap,
        ServiceContest $serviceContest,
        ResultsModelFactory $resultsModelFactory,
        StatsModelFactory $statsModelFactory,
        PasswordAuthenticator $authenticator,
        StoredQueryFactory $storedQueryFactory,
        ContestAuthorizator $contestAuthorizator,
        EventSoapFactory $fyziklaniSoapFactory
    ) {
        $this->inverseContestMap = $inverseContestMap;
        $this->serviceContest = $serviceContest;
        $this->resultsModelFactory = $resultsModelFactory;
        $this->statsModelFactory = $statsModelFactory;
        $this->authenticator = $authenticator;
        $this->storedQueryFactory = $storedQueryFactory;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->eventSoapFactory = $fyziklaniSoapFactory;
    }

    /**
     * This method should be called when handling AuthenticationCredentials SOAP header.
     *
     * @param stdClass $args
     * @throws SoapFault
     * @throws \Exception
     */
    public function authenticationCredentials(stdClass $args): void {
        if (!is_object($args) || !isset($args->username) || !isset($args->password)) {
            $this->log('Missing credentials.');
            throw new SoapFault('Sender', 'Missing credentials.');
        }

        try {
            $this->authenticatedLogin = $this->authenticator->authenticate($args->username, $args->password);
            $this->log('Successfully authenticated for web service request.');
        } catch (AuthenticationException $exception) {
            $this->log('Invalid credentials.');
            throw new SoapFault('Sender', 'Invalid credentials.');
        }
    }

    /**
     * @param stdClass $args
     * @return SoapVar
     * @throws SoapFault
     */
    public function getEvent(stdClass $args): SoapVar {
        $this->checkAuthentication(__FUNCTION__);
        return $this->eventSoapFactory->handleGetEvent($args);
    }

    /**
     * @param stdClass $args
     * @return SoapVar
     * @throws SoapFault
     */
    public function getEventsList(stdClass $args): SoapVar {
        $this->checkAuthentication(__FUNCTION__);
        return $this->eventSoapFactory->handleGetEventsList($args);
    }

    /**
     * @param \stdClass $args
     * @return SoapVar
     * @throws \SoapFault
     */
    public function getSignatures(\stdClass $args): SoapVar {
        if (!isset($args->contestId)) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        $contest = $this->serviceContest->findByPrimary($args->contestId);

        $doc = new \DOMDocument();

        $rootNode = $doc->createElement('signatures');
        $orgs = $contest->related(DbNames::TAB_ORG);
        foreach ($orgs as $row) {
            $org = ModelOrg::createFromActiveRow($row);
            $orgNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $org->getPerson()->getFullName(),
                'texSignature' => $org->tex_signature,
                'domainAlias' => $org->domain_alias,
            ], $doc, $orgNode);
            $rootNode->appendChild($orgNode);
        }
        $doc->appendChild($rootNode);
        $doc->formatOutput = true;

        $nodeString = '';
        foreach ($doc->childNodes as $node) {
            $nodeString .= $doc->saveXML($node);
        }
        return new SoapVar($nodeString, XSD_ANYXML);
    }

    /**
     * @param $args
     * @return SoapVar
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws SoapFault
     */
    public function getResults(stdClass $args): SoapVar {
        $this->checkAuthentication(__FUNCTION__);
        if (!isset($args->contest) || !isset($this->inverseContestMap[$args->contest])) {
            throw new SoapFault('Sender', 'Unknown contest.');
        }
        $contest = $this->serviceContest->findByPrimary($this->inverseContestMap[$args->contest]);
        $doc = new DOMDocument();
        $resultsNode = $doc->createElement('results');
        $doc->appendChild($resultsNode);

        if (isset($args->detail)) {
            $resultsModel = $this->resultsModelFactory->createDetailResultsModel($contest, $args->year);

            $series = explode(' ', $args->detail);
            foreach ($series as $seriesSingle) {
                $resultsModel->setSeries($seriesSingle);
                $resultsNode->appendChild($this->createDetailNode($resultsModel, $doc));
            }
        }

        if (isset($args->cumulatives)) {
            $resultsModel = $this->resultsModelFactory->createCumulativeResultsModel($contest, $args->year);

            if (!is_array($args->cumulatives->cumulative)) {
                $args->cumulatives->cumulative = [$args->cumulatives->cumulative];
            }

            foreach ($args->cumulatives->cumulative as $cumulative) {
                $resultsModel->setSeries(explode(' ', $cumulative));
                $resultsNode->appendChild($this->createCumulativeNode($resultsModel, $doc));
            }
        }

        if (isset($args->{'school-cumulatives'})) {
            $resultsModel = $this->resultsModelFactory->createSchoolCumulativeResultsModel($contest, $args->year);

            if (!is_array($args->{'school-cumulatives'}->{'school-cumulative'})) {
                $args->{'school-cumulatives'}->{'school-cumulative'} = [$args->{'school-cumulatives'}->{'school-cumulative'}];
            }

            foreach ($args->{'school-cumulatives'}->{'school-cumulative'} as $cumulative) {
                $resultsModel->setSeries(explode(' ', $cumulative));
                $resultsNode->appendChild($this->createSchoolCumulativeNode($resultsModel, $doc));
            }
        }

        // This type of call is deprecated (2015-10-02), when all possible callers
        // are notified about it, change it to SoapFault exception.
        if (isset($args->brojure)) {
            $resultsModel = $this->resultsModelFactory->createBrojureResultsModel($contest, $args->year);

            $series = explode(' ', $args->brojure);
            foreach ($series as $seriesSingle) {
                $resultsModel->setListedSeries($seriesSingle);
                $resultsModel->setSeries(range(1, $seriesSingle));
                $resultsNode->appendChild($this->createBrojureNode($resultsModel, $doc));
            }
        }

        if (isset($args->brojures)) {
            $resultsModel = $this->resultsModelFactory->createBrojureResultsModel($contest, $args->year);

            if (!is_array($args->brojures->brojure)) {
                $args->brojures->brojure = [$args->brojures->brojure];
            }

            foreach ($args->brojures->brojure as $brojure) {
                $series = explode(' ', $brojure);
                $listedSeries = $series[count($series) - 1];
                $resultsModel->setListedSeries($listedSeries);
                $resultsModel->setSeries($series);
                $resultsNode->appendChild($this->createBrojureNode($resultsModel, $doc));
            }
        }

        $doc->formatOutput = true;

        return new SoapVar($doc->saveXML($resultsNode), XSD_ANYXML);
    }

    /**
     * @param stdClass $args
     * @return SoapVar
     * @throws SoapFault
     */
    public function getStats(stdClass $args): SoapVar {
        $this->checkAuthentication(__FUNCTION__);
        if (!isset($args->contest) || !isset($this->inverseContestMap[$args->contest])) {
            throw new SoapFault('Sender', 'Unknown contest.');
        }
        $contest = $this->serviceContest->findByPrimary($this->inverseContestMap[$args->contest]);
        $year = (string)$args->year;

        $doc = new DOMDocument();
        $statsNode = $doc->createElement('stats');
        $doc->appendChild($statsNode);

        $model = $this->statsModelFactory->createTaskStatsModel($contest, (int)$year);

        if (isset($args->series)) {
            if (!is_array($args->series)) {
                $args->series = [$args->series];
            }
            foreach ($args->series as $series) {
                $seriesNo = $series->series;
                $model->setSeries($seriesNo);
                $tasks = $series->{'_'};
                foreach ($model->getData(explode(' ', $tasks)) as $task) {
                    $taskNode = $doc->createElement('task');
                    $statsNode->appendChild($taskNode);

                    $taskNode->setAttribute('series', $seriesNo);
                    $taskNode->setAttribute('label', $task['label']);
                    $taskNode->setAttribute('tasknr', $task['tasknr']);

                    $node = $doc->createElement('points', $task['points']);
                    $taskNode->appendChild($node);

                    $node = $doc->createElement('solvers', $task['task_count']);
                    $taskNode->appendChild($node);

                    $node = $doc->createElement('average', $task['task_avg']);
                    $taskNode->appendChild($node);
                }
            }
        }

        $doc->formatOutput = true;

        return new SoapVar($doc->saveXML($statsNode), XSD_ANYXML);
    }

    /**
     * @param stdClass $args
     * @return SoapVar
     * @throws BadRequestException
     * @throws SoapFault
     */
    public function getExport(stdClass $args): SoapVar {
        // parse arguments
        $qid = $args->qid;
        $format = isset($args->{'format-version'}) ? ((int)$args->{'format-version'}) : XMLNodeSerializer::EXPORT_FORMAT_1;
        $parameters = [];

        $this->checkAuthentication(__FUNCTION__, $qid);

        // stupid PHP deserialization
        if (!is_array($args->parameter)) {
            $args->parameter = [$args->parameter];
        }
        foreach ($args->parameter as $parameter) {
            $parameters[$parameter->name] = $parameter->{'_'};
            if ($parameter->name == StoredQueryFactory::PARAM_CONTEST) {
                if (!isset($this->inverseContestMap[$parameters[$parameter->name]])) {
                    $msg = "Unknown contest '{$parameters[$parameter->name]}'.";
                    $this->log($msg);
                    throw new SoapFault('Sender', $msg);
                }
                $parameters[$parameter->name] = $this->inverseContestMap[$parameters[$parameter->name]];
            }
        }

        try {
            $storedQuery = $this->storedQueryFactory->createQueryFromQid($qid, $parameters);
        } catch (InvalidArgumentException $exception) {
            throw new SoapFault('Sender', $exception->getMessage(), $exception);
        }

        // authorization
        if (!$this->isAuthorizedExport($storedQuery)) {
            $msg = 'Unauthorized';
            $this->log($msg);
            throw new SoapFault('Sender', $msg);
        }

        $doc = new DOMDocument();
        $exportNode = $doc->createElement('export');
        $exportNode->setAttribute('qid', $qid);
        $doc->appendChild($exportNode);

        $this->storedQueryFactory->fillNode($storedQuery, $exportNode, $doc, $format);

        $doc->formatOutput = true;

        return new SoapVar($doc->saveXML($exportNode), XSD_ANYXML);
    }

    /**
     * @param string $serviceName
     * @param ...$args
     * @throws SoapFault
     */
    private function checkAuthentication(string $serviceName, ...$args): void {
        if (!isset($this->authenticatedLogin)) {
            $this->log("Unauthenticated access to $serviceName.");
            throw new SoapFault('Sender', "Unauthenticated access to $serviceName.");
        } else {
            $this->log(sprintf('Called %s (%s).', $serviceName, join(',', $args)));
        }
    }

    private function isAuthorizedExport(StoredQuery $query): bool {
        $implicitParameters = $query->getImplicitParameters();
        if (!isset($implicitParameters[StoredQueryFactory::PARAM_CONTEST])) {
            return false;
        }
        return $this->contestAuthorizator->isAllowedForLogin($this->authenticatedLogin, $query, 'execute', $implicitParameters[StoredQueryFactory::PARAM_CONTEST]);
    }

    private function log(string $msg): void {
        if (!isset($this->authenticatedLogin)) {
            $message = 'unauthenticated@';
        } else {
            $message = $this->authenticatedLogin->__toString() . '@';
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message);
    }

    /**
     * @param AbstractResultsModel $resultsModel
     * @param DOMDocument $doc
     * @return DOMElement
     * @throws SoapFault
     * @throws BadTypeException
     */
    private function createDetailNode(AbstractResultsModel $resultsModel, DOMDocument $doc): DOMElement {
        $detailNode = $doc->createElement('detail');
        $detailNode->setAttribute('series', $resultsModel->getSeries());

        $this->resultsModelFactory->fillNode($resultsModel, $detailNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $detailNode;
    }

    /**
     * @param AbstractResultsModel $resultsModel
     * @param DOMDocument $doc
     * @return DOMElement
     * @throws SoapFault
     * @throws BadTypeException
     */
    private function createCumulativeNode(AbstractResultsModel $resultsModel, DOMDocument $doc): DOMElement {
        $cumulativeNode = $doc->createElement('cumulative');
        $cumulativeNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));

        $this->resultsModelFactory->fillNode($resultsModel, $cumulativeNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $cumulativeNode;
    }

    /**
     * @param AbstractResultsModel $resultsModel
     * @param DOMDocument $doc
     * @return DOMElement
     * @throws SoapFault
     * @throws BadTypeException
     */
    private function createSchoolCumulativeNode(AbstractResultsModel $resultsModel, DOMDocument $doc): DOMElement {
        $schoolNode = $doc->createElement('school-cumulative');
        $schoolNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));

        $this->resultsModelFactory->fillNode($resultsModel, $schoolNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $schoolNode;
    }

    /**
     * @param AbstractResultsModel|BrojureResultsModel $resultsModel
     * @param DOMDocument $doc
     * @return DOMElement
     * @throws SoapFault
     * @throws BadTypeException
     */
    private function createBrojureNode(AbstractResultsModel $resultsModel, DOMDocument $doc): DOMElement {
        $brojureNode = $doc->createElement('brojure');
        $brojureNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));
        $brojureNode->setAttribute('listed-series', $resultsModel->getListedSeries());

        $this->resultsModelFactory->fillNode($resultsModel, $brojureNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $brojureNode;
    }

}
