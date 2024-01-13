<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\Results\Models\AbstractResultsModel;
use FKSDB\Models\Results\Models\BrojureResultsModel;
use FKSDB\Models\Results\ResultsModelFactory;
use FKSDB\Models\WebService\XMLNodeSerializer;
use Nette\Application\BadRequestException;
use Nette\Schema\Elements\Structure;

/**
 * @phpstan-extends WebModel<array<string,mixed>,array<string,mixed>>
 */
class ResultsWebModel extends WebModel
{
    private ResultsModelFactory $resultsModelFactory;
    private ContestYearService $contestYearService;

    public function inject(ContestYearService $contestYearService, ResultsModelFactory $resultsModelFactory): void
    {
        $this->contestYearService = $contestYearService;
        $this->resultsModelFactory = $resultsModelFactory;
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \SoapFault
     * @throws \DOMException
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (
            !isset($args->contest)
            || !isset($this->container->getParameters()['inverseContestMapping'][$args->contest])
        ) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        if (!isset($args->year)) {
            throw new \SoapFault('Sender', 'Unknown year.');
        }
        $contestYear = $this->contestYearService->findByContestAndYear(
            $this->container->getParameters()['inverseContestMapping'][$args->contest],
            (int)$args->year
        );
        $doc = new \DOMDocument();
        $resultsNode = $doc->createElement('results');
        $doc->appendChild($resultsNode);

        if (isset($args->detail)) {
            $resultsModel = $this->resultsModelFactory->createDetailResultsModel($contestYear);

            $series = explode(' ', $args->detail);
            foreach ($series as $seriesSingle) {
                $resultsModel->setSeries((int)$seriesSingle);
                $resultsNode->appendChild($this->createDetailNode($resultsModel, $doc));
            }
        }

        if (isset($args->cumulatives)) {
            $resultsModel = $this->resultsModelFactory->createCumulativeResultsModel($contestYear);

            if (!is_array($args->cumulatives->cumulative)) {
                $args->cumulatives->cumulative = [$args->cumulatives->cumulative];
            }

            foreach ($args->cumulatives->cumulative as $cumulative) {
                $resultsModel->setSeries(array_map(fn($x) => (int)$x, explode(' ', $cumulative)));
                $resultsNode->appendChild($this->createCumulativeNode($resultsModel, $doc));
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
                $series = array_map(fn($x) => (int)$x, explode(' ', $brojure));
                $listedSeries = $series[count($series) - 1];
                $resultsModel->setListedSeries((int)$listedSeries);
                $resultsModel->setSeries($series);
                $resultsNode->appendChild($this->createBrojureNode($resultsModel, $doc));
            }
        }

        if (isset($args->{'school-cumulatives'})) {
            $resultsModel = $this->resultsModelFactory->createSchoolCumulativeResultsModel($contestYear);

            if (!is_array($args->{'school-cumulatives'}->{'school-cumulative'})) {
                $args->{'school-cumulatives'}->{'school-cumulative'}
                    = [$args->{'school-cumulatives'}->{'school-cumulative'}];
            }

            foreach ($args->{'school-cumulatives'}->{'school-cumulative'} as $cumulative) {
                $resultsModel->setSeries(array_map(fn($x) => (int)$x, explode(' ', $cumulative)));
                $resultsNode->appendChild($this->createSchoolCumulativeNode($resultsModel, $doc));
            }
        }

        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($resultsNode), XSD_ANYXML);
    }

    /**
     * @throws \SoapFault
     * @throws BadTypeException
     * @throws \DOMException
     */
    private function createDetailNode(AbstractResultsModel $resultsModel, \DOMDocument $doc): \DOMElement
    {
        $detailNode = $doc->createElement('detail');
        $detailNode->setAttribute('series', (string)$resultsModel->getSeries()); // @phpstan-ignore-line

        $this->resultsModelFactory->fillNode($resultsModel, $detailNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $detailNode;
    }

    /**
     * @throws BadTypeException
     * @throws \SoapFault
     * @throws \DOMException
     */
    private function createCumulativeNode(AbstractResultsModel $resultsModel, \DOMDocument $doc): \DOMElement
    {
        $cumulativeNode = $doc->createElement('cumulative');
        $cumulativeNode->setAttribute('series', implode(' ', $resultsModel->getSeries())); // @phpstan-ignore-line

        $this->resultsModelFactory->fillNode($resultsModel, $cumulativeNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $cumulativeNode;
    }

    /**
     * @throws \SoapFault
     * @throws BadTypeException
     * @throws \DOMException
     */
    private function createSchoolCumulativeNode(AbstractResultsModel $resultsModel, \DOMDocument $doc): \DOMElement
    {
        $schoolNode = $doc->createElement('school-cumulative');
        $schoolNode->setAttribute('series', implode(' ', $resultsModel->getSeries())); // @phpstan-ignore-line

        $this->resultsModelFactory->fillNode($resultsModel, $schoolNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $schoolNode;
    }

    /**
     * @param BrojureResultsModel $resultsModel
     * @throws \SoapFault
     * @throws BadTypeException
     * @throws \DOMException
     */
    private function createBrojureNode(AbstractResultsModel $resultsModel, \DOMDocument $doc): \DOMElement
    {
        $brojureNode = $doc->createElement('brojure');
        $brojureNode->setAttribute('series', implode(' ', $resultsModel->getSeries()));
        $brojureNode->setAttribute('listed-series', (string)$resultsModel->getListedSeries());

        $this->resultsModelFactory->fillNode($resultsModel, $brojureNode, $doc, XMLNodeSerializer::EXPORT_FORMAT_1);
        return $brojureNode;
    }

    protected function isAuthorized(array $params): bool
    {
        return false;
    }

    protected function getExpectedParams(): Structure
    {
        throw new NotImplementedException();
    }

    protected function getJsonResponse(array $params): array
    {
        throw new NotImplementedException();
    }
}
