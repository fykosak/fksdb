<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use FKSDB\Components\Controls\Chart\TotalPersonsChartControl;

/**
 * Class ChartPresenter
 * *
 */
class ChartPresenter extends BasePresenter {
    use ChartPresenterTrait;

    public function authorizedList() {
        $this->setAuthorized($this->isAnyContestAuthorized('chart', 'list'));
    }

    public function authorizedChart() {
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
