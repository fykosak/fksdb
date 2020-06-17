<?php

namespace FKSDB;

use FKSDB\Components\Controls\Chart\IChart;
use Nette\Application\UI\Control;

/**
 * Trait ChartPresenterTrait
 * *
 */
trait ChartPresenterTrait {
    /**
     * @var IChart
     */
    protected $selectedChart;


    public function titleChart() {
        $this->setTitle($this->selectedChart->getTitle(), 'fa fa-pie-chart');
    }

    public function titleList() {
        $this->setTitle(_('Charts'), 'fa fa fa-pie-chart');
    }

    public function renderChart() {
        $this->template->chart = $this->selectedChart;
    }

    public function renderList() {
        $this->template->charts = $this->getCharts();
    }

    /**
     * @return IChart[]
     */
    protected function getCharts(): array {
        static $chartComponents;
        if (!$chartComponents) {
            $chartComponents = $this->registerCharts();
        }
        return $chartComponents;
    }

    protected function selectChart() {
        foreach ($this->getCharts() as $chart) {
            if ($chart->getAction() === $this->getAction()) {
                $this->selectedChart = $chart;
                $this->setView('chart');
            }
        }
    }

    protected function createComponentChart(): Control {
        return $this->selectedChart->getControl();
    }


    abstract public function authorizedList();

    abstract public function authorizedChart();

    /**
     * @return IChart[]
     */
    abstract protected function registerCharts(): array;

    /**
     * @param bool $fullyQualified
     * @return string
     */
    abstract public function getAction($fullyQualified = false);

    /**
     * @param $id
     * @return static
     */
    abstract public function setView($id);
}
