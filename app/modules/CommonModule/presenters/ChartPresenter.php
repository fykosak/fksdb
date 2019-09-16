<?php

namespace CommonModule;

use FKSDB\Components\Controls\Chart\AbstractChartControl;
use FKSDB\Components\Controls\Chart\ParticipantAcquaintanceChartControl;
use FKSDB\ORM\Services\ServiceEvent;
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
     * @var AbstractChartControl[]
     */
    private $chartComponents;
    /**
     * @var AbstractChartControl
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
     * @return array|AbstractChartControl[]
     */
    private function getChartsControls() {
        if (!$this->chartComponents) {
            $this->chartComponents = [
                new ParticipantAcquaintanceChartControl($this->context, +$this->id, $this->serviceEvent),
            ];
        }
        return $this->chartComponents;
    }

    public function titleChart() {

    }

    public function startup() {
        parent::startup();
        Debugger::barDump($this->getAction());
        foreach ($this->getChartsControls() as $chart) {
            if ($chart->getAction() === $this->getAction()) {

                $this->selectedChart = $chart;

            }
        }
    }

    public function beforeRender() {
        parent::beforeRender();
        if ($this->selectedChart) {
            $this->setView('chart');
        }

    }

    /**
     * @return AbstractChartControl
     */
    public function createComponentChart() {
        return $this->selectedChart;
    }
}