<?php

namespace FKSDB;

use FKSDB\Components\Controls\Chart\IChart;
use Nette\Application\UI\Control;

/**
 * Trait ChartPresenterTrait
 * @package FKSDB
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
    protected function getCharts() {
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

    /**
     * @return Control
     */
    public function createComponentChart() {
        return $this->selectedChart->getControl();
    }

    /**
     * @param bool $fullyQualified
     * @return string
     */
    abstract function getAction($fullyQualified = false);

    /**
     * @param $id
     * @return mixed
     */
    abstract function setView($id);

    /**
     * @return IChart[]
     */
    abstract protected function registerCharts(): array;
}
