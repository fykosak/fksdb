<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
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

    protected function startup() {
        parent::startup();
        $this->selectChart();
    }

    protected function registerCharts(): array {
        return [
            'totalPersons' => new TotalPersonsChartControl($this->getContext()),
        ];
    }
}
