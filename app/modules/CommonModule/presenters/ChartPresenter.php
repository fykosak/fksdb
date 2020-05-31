<?php

namespace CommonModule;

use FKSDB\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\TotalPersonsChartControl;

/**
 * Class ChartPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ChartPresenter extends BasePresenter {
    use ChartPresenterTrait;

    public function authorizedList(): void {
        $this->setAuthorized($this->isAnyContestAuthorized('chart', 'list'));
    }

    public function authorizedChart(): void {
        $this->setAuthorized($this->isAnyContestAuthorized('chart', 'chart'));
    }

    public function startup() {
        parent::startup();
        $this->selectChart();
    }

    /**
     * @return array|TotalPersonsChartControl[]
     */
    protected function registerCharts(): array {
        return [
            new TotalPersonsChartControl($this->getContext()),
        ];
    }
}
