<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Charts\Contestants\AggregatedSeriesChart;
use FKSDB\Components\Charts\Contestants\PerSeriesChart;
use FKSDB\Components\Charts\Contestants\PerYearsChart;
use FKSDB\Components\Charts\TotalPersonsChart;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;

class ChartPresenter extends BasePresenter
{
    use ChartPresenterTrait;

    public function authorizedList(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('chart', 'list', $this->getSelectedContest()));
    }

    public function authorizedChart(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('chart', 'chart', $this->getSelectedContest()));
    }

    protected function startup(): void
    {
        parent::startup();
        $this->selectChart();
    }

    protected function registerCharts(): array
    {
        return [
            'contestantsPerSeries' => new PerSeriesChart($this->getContext(), $this->getSelectedContest()),
            'totalContestantsPerSeries' => new AggregatedSeriesChart($this->getContext(), $this->getSelectedContest()),
            'contestantsPerYears' => new PerYearsChart($this->getContext(), $this->getSelectedContest()),
            'totalPersons' => new TotalPersonsChart($this->getContext()),
        ];
    }

    protected function beforeRender(): void
    {
        switch ($this->getAction()) {
            case 'list':
                break;
            default:
                $this->getPageStyleContainer()->setWidePage();
        }
        parent::beforeRender();
    }
}
