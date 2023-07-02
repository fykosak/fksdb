<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Charts\Contestants\AggregatedSeriesChart;
use FKSDB\Components\Charts\Contestants\PerSeriesChart;
use FKSDB\Components\Charts\Contestants\PerYearsChart;
use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\Charts\TotalPersonsChart;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

class ChartPresenter extends BasePresenter
{
    use ChartPresenterTrait;

    public function authorizedList(): bool
    {
        return $this->contestAuthorizator->isAllowed('chart', 'list', $this->getSelectedContest());
    }

    /**
     * @throws EventNotFoundException
     * @throws BadTypeException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws UnsupportedLanguageException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->registerCharts();
    }

    /**
     * @return Chart[]
     */
    protected function getCharts(): array
    {
        return [
            'contestantsPerSeries' => new PerSeriesChart($this->getContext(), $this->getSelectedContest()),
            'totalContestantsPerSeries' => new AggregatedSeriesChart($this->getContext(), $this->getSelectedContest()),
            'contestantsPerYears' => new PerYearsChart($this->getContext(), $this->getSelectedContest()),
            'totalPersons' => new TotalPersonsChart($this->getContext()),
        ];
    }
}
