<?php

namespace CommonModule;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\Controls\Chart\ParticipantAcquaintanceChartControl;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\UI\Control;
use Tracy\Debugger;

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
     * @return IChart[]
     */
    private function getCharts() {
        static $chartComponents;
        if (!$chartComponents) {
            $chartComponents = [
                new ParticipantAcquaintanceChartControl($this->context, +$this->id, $this->serviceEvent),
            ];
        }
        return $chartComponents;
    }

    public function titleChart() {
        $this->setTitle($this->selectedChart->getTitle());
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
    public function renderList(){
        $this->template->charts = $this->getCharts();
    }

    /**
     * @return Control
     */
    public function createComponentChart() {
        return $this->selectedChart->getControl();
    }
}
