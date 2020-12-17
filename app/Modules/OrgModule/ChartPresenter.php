<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Chart\ContestantsPerSeries\AggregatedSeries;
use FKSDB\Components\Controls\Chart\ContestantsPerSeries\PerSeriesChart;
use FKSDB\Components\Controls\Chart\ContestantsPerYearsChart;
use FKSDB\Components\Controls\Chart\TotalPersonsChartControl;
use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\ContestPresenterTrait;

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
            'contestantsPerSeries' => new PerSeriesChart($this->getContext(), $this->getSelectedContest()),
            'totalContestantsPerSeries' => new AggregatedSeries($this->getContext(), $this->getSelectedContest()),
            'contestantsPerYears' => new ContestantsPerYearsChart($this->getContext(), $this->getSelectedContest()),
            'totalPersons' => new TotalPersonsChartControl($this->getContext()),
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
