<?php

use Authorization\ContestAuthorizator;
use Nette\Diagnostics\Debugger;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use SQL\StoredQuery;
use SQL\StoredQueryFactory;

/**
 * Web service provider for fksdb.wdsl
 * @author michal
 */
class WebServiceModel {

    /**
     * @var array  contest name => contest_id
     */
    private $inverseContestMap;

    /**
     * @var ServiceContest
     */
    private $serviceContest;

    /**
     * @var ResultsModelFactory
     */
    private $resultsModelFactory;

    /**
     * @var StatsModelFactory
     */
    private $statsModelFactory;

    /**
     * @var ModelLogin
     */
    private $authenticatedLogin;

    /**
     * @var IAuthenticator
     */
    private $authenticator;

    /**
     * @var StoredQueryFactory
     */
    private $storedQueryFactory;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    function __construct(array $inverseContestMap, ServiceContest $serviceContest, ResultsModelFactory $resultsModelFactory, StatsModelFactory $statsModelFactory, IAuthenticator $authenticator, StoredQueryFactory $storedQueryFactory, ContestAuthorizator $contestAuthorizator) {
        $this->inverseContestMap = $inverseContestMap;
        $this->serviceContest = $serviceContest;
        $this->resultsModelFactory = $resultsModelFactory;
        $this->statsModelFactory = $statsModelFactory;
        $this->authenticator = $authenticator;
        $this->storedQueryFactory = $storedQueryFactory;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /**
     * This method should be called when handling AuthenticationCredentials SOAP header.
     * 
     * @param stdClass $args
     * @throws SoapFault
     */
    public function AuthenticationCredentials($args) {
        if (!is_object($args) || !isset($args->username) || !isset($args->password)) {
            $this->log('Missing credentials.');
            throw new SoapFault('Sender', 'Missing credentials.');
        }

        $credentials = array(
            IAuthenticator::USERNAME => $args->username,
            IAuthenticator::PASSWORD => $args->password,
        );

        try {
            $this->authenticatedLogin = $this->authenticator->authenticate($credentials);
            $this->log(" Successfully authenticated for web service request.");
        } catch (AuthenticationException $e) {
            $this->log('Invalid credentials.');
            throw new SoapFault('Sender', 'Invalid credentials.');
        }
    }

    public function GetResults($args) {
        $this->checkAuthentication(__FUNCTION__);
        if (!isset($this->inverseContestMap[$args->contest])) {
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
                $args->cumulatives->cumulative = array($args->cumulatives->cumulative);
            }

            foreach ($args->cumulatives->cumulative as $cumulative) {
                $resultsModel->setSeries(explode(' ', $cumulative));
                $resultsNode->appendChild($this->createCumulativeNode($resultsModel, $doc));
            }
        }

        if (isset($args->brojure)) {
            $resultsModel = $this->resultsModelFactory->createBrojureResultsModel($contest, $args->year);

            $series = explode(' ', $args->brojure);
            foreach ($series as $seriesSingle) {
                $resultsModel->setListedSeries($seriesSingle);
                $resultsModel->setSeries(range(1, $seriesSingle));
                $resultsNode->appendChild($this->createBrojureNode($resultsModel, $doc));
            }
        }

        $doc->formatOutput = true;

        return new SoapVar($doc->saveXML($resultsNode), XSD_ANYXML);
    }

    public function GetStats($args) {
        $this->checkAuthentication(__FUNCTION__);
        if (!isset($this->inverseContestMap[$args->contest])) {
            throw new SoapFault('Sender', 'Unknown contest.');
        }

        $contest = $this->serviceContest->findByPrimary($this->inverseContestMap[$args->contest]);
        $year = (string) $args->year;

        $doc = new DOMDocument();
        $statsNode = $doc->createElement('stats');
        $doc->appendChild($statsNode);

        $model = $this->statsModelFactory->createTaskStatsModel($contest, $year);

        if (isset($args->series)) {
            if (!is_array($args->series)) {
                $args->series = array($args->series);
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

    public function GetExport($args) {
        $this->checkAuthentication(__FUNCTION__);

        // parse arguments
        $qid = $args->qid;
        $parameters = array();

        // stupid PHP deserialization
        if (!is_array($args->parameter)) {
            $args->parameter = array($args->parameter);
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
        } catch (InvalidArgumentException $e) {
            throw new SoapFault('Sender', $e->getMessage(), $e);
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

        $this->fillExportNode($storedQuery, $exportNode, $doc);

        $doc->formatOutput = true;

        return new SoapVar($doc->saveXML($exportNode), XSD_ANYXML);
    }

    private function checkAuthentication($serviceName) {
        if (!$this->authenticatedLogin) {
            $this->log("Unauthenticated access to $serviceName.");
            throw new SoapFault('Sender', "Unauthenticated access to $serviceName.");
        } else {
            $this->log("Called $serviceName.");
        }
    }

    private function isAuthorizedExport(StoredQuery $query) {
        $implicitParameters = $query->getImplicitParameters();
        if (!isset($implicitParameters[StoredQueryFactory::PARAM_CONTEST])) {
            return false;
        }
        return $this->contestAuthorizator->isAllowedForLogin($this->authenticatedLogin, $query, 'execute', $implicitParameters[StoredQueryFactory::PARAM_CONTEST]);
    }

    private function log($msg) {
        if (!$this->authenticatedLogin) {
            $message = "unauthenticated@";
        } else {
            $message = $this->authenticatedLogin->__toString() . ")@";
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message);
    }

    private function createDetailNode(IResultsModel $resultsModel, DOMDocument $doc) {
        $detailNode = $doc->createElement('detail');
        $detailNode->setAttribute('series', $resultsModel->getSeries());

        $this->fillNodeWithCategories($resultsModel, $detailNode, $doc);
        return $detailNode;
    }

    private function createCumulativeNode(IResultsModel $resultsModel, DOMDocument $doc) {
        $cumulativeNode = $doc->createElement('cumulative');
        $cumulativeNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));

        $this->fillNodeWithCategories($resultsModel, $cumulativeNode, $doc);
        return $cumulativeNode;
    }

    private function createBrojureNode(IResultsModel $resultsModel, DOMDocument $doc) {
        $brojureNode = $doc->createElement('brojure');
        $brojureNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));
        $brojureNode->setAttribute('listed-series', $resultsModel->getListedSeries());

        $this->fillNodeWithCategories($resultsModel, $brojureNode, $doc);
        return $brojureNode;
    }

    private function fillNodeWithCategories(IResultsModel $resultsModel, DOMElement $node, DOMDocument $doc) {
        try {
            foreach ($resultsModel->getCategories() as $category) {
                // category node
                $categoryNode = $doc->createElement('category');
                $node->appendChild($categoryNode);
                $categoryNode->setAttribute('id', $category->id);

                $columnDefsNode = $doc->createElement('column-definitions');
                $categoryNode->appendChild($columnDefsNode);

                // columns definitions
                foreach ($resultsModel->getDataColumns($category) as $column) {
                    $columnDefNode = $doc->createElement('column-definition');
                    $columnDefsNode->appendChild($columnDefNode);

                    $columnDefNode->setAttribute('label', $column[IResultsModel::COL_DEF_LABEL]);
                    $columnDefNode->setAttribute('limit', $column[IResultsModel::COL_DEF_LIMIT]);
                }

                // data
                $dataNode = $doc->createElement('data');
                $categoryNode->appendChild($dataNode);

                // data for each contestant
                foreach ($resultsModel->getData($category) as $row) {
                    $contestantNode = $doc->createElement('contestant');
                    $dataNode->appendChild($contestantNode);

                    $contestantNode->setAttribute('name', $row[IResultsModel::DATA_NAME]);
                    $contestantNode->setAttribute('school', $row[IResultsModel::DATA_SCHOOL]);
                    // rank
                    $rankNode = $doc->createElement('rank');
                    $contestantNode->appendChild($rankNode);
                    $rankNode->setAttribute('from', $row[IResultsModel::DATA_RANK_FROM]);
                    if (isset($row[IResultsModel::DATA_RANK_TO]) && $row[IResultsModel::DATA_RANK_FROM] != $row[IResultsModel::DATA_RANK_TO]) {
                        $rankNode->setAttribute('to', $row[IResultsModel::DATA_RANK_TO]);
                    }

                    // data columns
                    foreach ($resultsModel->getDataColumns($category) as $column) {
                        $columnNode = $doc->createElement('column', $row[$column[IResultsModel::COL_ALIAS]]);
                        $contestantNode->appendChild($columnNode);
                    }
                }
            }
        } catch (Exception $e) {
            Debugger::log($e);
            throw new SoapFault('Receiver', 'Internal error.');
        }
    }

    private function fillExportNode(StoredQuery $storedQuery, DOMElement $exportNode, DOMDocument $doc) {
        // parameters
        $parametersNode = $doc->createElement('parameters');
        $exportNode->appendChild($parametersNode);
        foreach ($storedQuery->getImplicitParameters() as $name => $value) {
            $parameterNode = $doc->createElement('parameters', $value);
            $parameterNode->setAttribute('name', $name);
            $parametersNode->appendChild($parameterNode);
        }

        // column definitions
        $columnDefinitionsNode = $doc->createElement('column-definitions');
        $exportNode->appendChild($columnDefinitionsNode);
        foreach ($storedQuery->getColumnNames() as $column) {
            $columnDefinitionNode = $doc->createElement('column-definition');
            $columnDefinitionNode->setAttribute('name', $column);
            $columnDefinitionsNode->appendChild($columnDefinitionNode);
        }

        // data
        $dataNode = $doc->createElement('data');
        $exportNode->appendChild($dataNode);
        foreach ($storedQuery->getData() as $row) {
            $rowNode = $doc->createElement('row');
            $dataNode->appendChild($rowNode);
            foreach ($row as $col) {
                $colNode = $doc->createElement('col', $col);
                $rowNode->appendChild($colNode);
            }
        }
    }

}

?>
