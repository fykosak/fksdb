<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Charts\Contestants\AggregatedSeriesChartComponent;
use FKSDB\Components\Charts\Contestants\PerSeriesChartComponent;
use FKSDB\Components\Charts\Contestants\PerYearsChartComponent;
use FKSDB\Components\Charts\TotalPersonsChartComponent;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;

/**
 * Class ChartPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ChartPresenter extends BasePresenter {
    use ChartPresenterTrait;

    public function authorizedList(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('chart', 'list', $this->getSelectedContest()));
    }

    public function authorizedChart(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('chart', 'chart', $this->getSelectedContest()));
    }

    protected function startup(): void {
        parent::startup();
        $this->selectChart();
    }

    protected function registerCharts(): array {
        return [
            'contestantsPerSeries' => new PerSeriesChartComponent($this->getContext(), $this->getSelectedContest()),
            'totalContestantsPerSeries' => new AggregatedSeriesChartComponent($this->getContext(), $this->getSelectedContest()),
            'contestantsPerYears' => new PerYearsChartComponent($this->getContext(), $this->getSelectedContest()),
            'totalPersons' => new TotalPersonsChartComponent($this->getContext()),
        ];
    }

    protected function beforeRender(): void {
        switch ($this->getAction()) {
            case 'list':
                break;
            default:
                $this->getPageStyleContainer()->setWidePage();
        }
        parent::beforeRender();
    }
}
