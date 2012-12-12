<?php

/**
 * Web service provider for fksdb.wdsl TODO
 * @author michal
 */
class WebServiceModel {

    private $contestMap = array(
        'fykos' => ModelContest::ID_FYKOS,
        'vyfuk' => ModelContest::ID_VYFUK,
    );

    /**
     * @var ServiceContest
     */
    private $serviceContest;

    /**
     * @var ResultsModelFactory
     */
    private $resultsModelFactory;

    /**
     * @var ModelPerson
     */
    private $authenticatedUser;

    /**
     * @var Nette\Security\IAuthenticator
     */
    private $authenticator;

    function __construct(Nette\Security\IAuthenticator $authenticator, ServiceContest $serviceContest, ResultsModelFactory $resultsModelFactory) {
        $this->serviceContest = $serviceContest;
        $this->resultsModelFactory = $resultsModelFactory;
        $this->authenticator = $authenticator;
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
            Nette\Security\IAuthenticator::USERNAME => $args->username,
            Nette\Security\IAuthenticator::PASSWORD => $args->password,
        );

        try {
            $this->authenticatedUser = $this->authenticator->authenticate($credentials);
            $this->log("User " . $this->authenticatedUser->getFullname() . "(" . $this->authenticatedUser->person_id . ") authenticated for web service request.");
        } catch (Nette\Security\AuthenticationException $e) {
            $this->log('Invalid credentials.');
            throw new SoapFault('Sender', 'Invalid credentials.');
        }
    }

    public function GetResults($args) {
        $this->checkAuthentication(__FUNCTION__);
        if (!isset($this->contestMap[$args->contest])) {
            throw new SoapFault('Sender', 'Unknown contest.');
        }

        $contest = $this->serviceContest->findByPrimary($this->contestMap[$args->contest]);

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

    private function checkAuthentication($serviceName) {
        if (!$this->authenticatedUser) {
            $this->log("Unauthenticated access to $serviceName.");
            throw new SoapFault('Sender', "Unauthenticated access to $serviceName.");
        } else {
            $this->log("User " . $this->authenticatedUser->getFullname() . "(" . $this->authenticatedUser->person_id . ") called $serviceName.");
        }
    }

    private function log($msg) {
        Nette\Diagnostics\Debugger::log($_SERVER['REMOTE_ADDR'] . ': ' . $msg);
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
                foreach ($resultsModel->getDataColumns() as $column) {
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
                    foreach ($resultsModel->getDataColumns() as $column) {
                        $columnNode = $doc->createElement('column', $row[$column[IResultsModel::COL_ALIAS]]);
                        $contestantNode->appendChild($columnNode);
                    }
                }
            }
        } catch (Exception $e) {
            Nette\Diagnostics\Debugger::log($e);
            throw new SoapFault('Receiver', 'Internal error.');
        }
    }

}

?>
