<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\Results\Models\AbstractResultsModel;
use FKSDB\Models\Results\Models\BrojureResultsModel;
use FKSDB\Models\Results\ResultsModelFactory;
use FKSDB\Models\WebService\XMLNodeSerializer;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

class ResultsWebModel extends WebModel
{

    private ServiceContest $serviceContest;
    private ResultsModelFactory $resultsModelFactory;

    public function inject(
        Container $container,
        ServiceContest $serviceContest,
        ResultsModelFactory $resultsModelFactory
    ): void {
        $this->serviceContest = $serviceContest;
        $this->resultsModelFactory = $resultsModelFactory;
        $this->container = $container;
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \SoapFault
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (
            !isset($args->contest) || !isset(
                $this->container->getParameters()['inverseContestMapping'][$args->contest]
            )
        ) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        if (!isset($args->year)) {
            throw new \SoapFault('Sender', 'Unknown year.');
        }
        $contestYear = $this->serviceContest->findByPrimary(
            $this->container->getParameters()['inverseContestMapping'][$args->contest]
        )->getContestYear($args->year);
        $doc = new \DOMDocument();
        $resultsNode = $doc->createElement('results');
        $doc->appendChild($resultsNode);

        if (isset($args->detail)) {
            $resultsModel = $this->resultsModelFactory->createDetailResultsModel($contestYear);

            $series = explode(' ', $args->detail);
            foreach ($series as $seriesSingle) {
                $resultsModel->setSeries($seriesSingle);
                $resultsNode->appendChild($this->createDetailNode($resultsModel, $doc));
            }
        }

        if (isset($args->cumulatives)) {
            $resultsModel = $this->resultsModelFactory->createCumulativeResultsModel($contestYear);

            if (!is_array($args->cumulatives->cumulative)) {
                $args->cumulatives->cumulative = [$args->cumulatives->cumulative];
            }

            foreach ($args->cumulatives->cumulative as $cumulative) {
                $resultsModel->setSeries(explode(' ', $cumulative));
                $resultsNode->appendChild($this->createCumulativeNode($resultsModel, $doc));
            }
        }

        if (isset($args->{'school-cumulatives'})) {
            $resultsModel = $this->resultsModelFactory->createSchoolCumulativeResultsModel($contestYear);

            if (!is_array($args->{'school-cumulatives'}->{'school-cumulative'})) {
                $args->{'school-cumulatives'}->{'school-cumulative'} = [$args->{'school-cumulatives'}->{'school-cumulative'}];
            }

            foreach ($args->{'school-cumulatives'}->{'school-cumulative'} as $cumulative) {
                $resultsModel->setSeries(explode(' ', $cumulative));
                $resultsNode->appendChild($this->createSchoolCumulativeNode($resultsModel, $doc));
            }
        }

        // This type of call is deprecated (2015-10-02), when all possible callers
        // are notified about it, change it to \SoapFault exception.
        if (isset($args->brojure)) {
            $resultsModel = $this->resultsModelFactory->createBrojureResultsModel($contestYear);

            $series = explode(' ', $args->brojure);
            foreach ($series as $seriesSingle) {
                $resultsModel->setListedSeries((int)$seriesSingle);
                $resultsModel->setSeries(range(1, $seriesSingle));
                $resultsNode->appendChild($this->createBrojureNode($resultsModel, $doc));
            }
        }

        if (isset($args->brojures)) {
            $resultsModel = $this->resultsModelFactory->createBrojureResultsModel($contestYear);

            if (!is_array($args->brojures->brojure)) {
                $args->brojures->brojure = [$args->brojures->brojure];
            }

            foreach ($args->brojures->brojure as $brojure) {
                $series = explode(' ', $brojure);
                $listedSeries = $series[count($series) - 1];
                $resultsModel->setListedSeries((int)$listedSeries);
                $resultsModel->setSeries($series);
                $resultsNode->appendChild($this->createBrojureNode($resultsModel, $doc));
            }
        }

        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($resultsNode), XSD_ANYXML);
    }

    /**
     * @throws \SoapFault
     * @throws BadTypeException
     */
    private function createDetailNode(AbstractResultsModel $resultsModel, \DOMDocument $doc): \DOMElement
    {
        $detailNode = $doc->createElement('detail');
        $detailNode->setAttribute('series', (string)$resultsModel->getSeries());

        $this->resultsModelFactory->fillNode($resultsModel, $detailNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $detailNode;
    }

    /**
     * @throws \SoapFault
     * @throws BadTypeException
     */
    private function createCumulativeNode(AbstractResultsModel $resultsModel, \DOMDocument $doc): \DOMElement
    {
        $cumulativeNode = $doc->createElement('cumulative');
        $cumulativeNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));

        $this->resultsModelFactory->fillNode($resultsModel, $cumulativeNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $cumulativeNode;
    }

    /**
     * @throws \SoapFault
     * @throws BadTypeException
     */
    private function createSchoolCumulativeNode(AbstractResultsModel $resultsModel, \DOMDocument $doc): \DOMElement
    {
        $schoolNode = $doc->createElement('school-cumulative');
        $schoolNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));

        $this->resultsModelFactory->fillNode($resultsModel, $schoolNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $schoolNode;
    }

    /**
     * @param AbstractResultsModel|BrojureResultsModel $resultsModel
     * @throws \SoapFault
     * @throws BadTypeException
     */
    private function createBrojureNode(AbstractResultsModel $resultsModel, \DOMDocument $doc): \DOMElement
    {
        $brojureNode = $doc->createElement('brojure');
        $brojureNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));
        $brojureNode->setAttribute('listed-series', (string)$resultsModel->getListedSeries());

        $this->resultsModelFactory->fillNode($resultsModel, $brojureNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $brojureNode;
    }
}
