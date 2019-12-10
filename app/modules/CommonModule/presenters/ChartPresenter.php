<?php

namespace CommonModule;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\Controls\Chart\ParticipantAcquaintanceChartControl;
use FKSDB\Components\Controls\Chart\TotalPersonsChartControl;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\UI\Control;

/**
 * Class ChartPresenter
 * @package CommonModule
 */
class ChartPresenter extends BasePresenter {
    /**
     * @var int|string
     * @persistent
     */
    public $id;
    /**
     * @var IChart
     */
    private $selectedChart;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @param ServiceEvent $serviceEvent
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @param ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @return IChart[]
     */
    private function getCharts(): array {
        static $chartComponents;
        if (!$chartComponents) {
            $chartComponents = [
                new ParticipantAcquaintanceChartControl($this->context, +$this->id, $this->serviceEvent),
                new TotalPersonsChartControl($this->context, $this->servicePerson),
            ];
        }
        return $chartComponents;
    }

    public function titleChart() {
        $this->setTitle($this->selectedChart->getTitle());
        $this->setIcon('fa fa-pie-chart');
    }

    public function titleDefault() {
        $this->setTitle(_('List of charts'));
        $this->setIcon('fa fa-pie-chart');
    }

    public function startup() {
        parent::startup();
        foreach ($this->getCharts() as $chart) {
            if ($chart->getAction() === $this->getAction()) {
                $this->selectedChart = $chart;
                $this->setView('chart');
            }
        }
    }

    public function renderDefault() {
        $this->template->charts = $this->getCharts();
    }

    /**
     * @return Control
     */
    public function createComponentChart() {
        return $this->selectedChart->getControl();
    }
}
