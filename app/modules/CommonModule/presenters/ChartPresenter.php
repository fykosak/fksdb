<?php

namespace CommonModule;

use FKSDB\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\TotalPersonsChartControl;

/**
 * Class ChartPresenter
 * @package CommonModule
 */
class ChartPresenter extends BasePresenter {
    use ChartPresenterTrait;

    public function startup() {
        parent::startup();
        $this->selectChart();
    }

    public function renderList() {
        $this->template->charts = $this->getCharts();
    }

    /**
     * @inheritDoc
     */
    protected function registerCharts(): array {
        return [
            new TotalPersonsChartControl($this->context),
        ];
    }
}
