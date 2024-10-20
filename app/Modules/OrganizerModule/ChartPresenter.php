<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Charts\Contestants\AggregatedSeriesChart;
use FKSDB\Components\Charts\Contestants\ParticipantGeoChart;
use FKSDB\Components\Charts\Contestants\PerSeriesChart;
use FKSDB\Components\Charts\Contestants\PerYearsChart;
use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\Charts\SubmitsPerSeriesChart;
use FKSDB\Components\Charts\TotalPersonsChart;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\ComponentModel\IComponent;

final class ChartPresenter extends BasePresenter
{
    use ChartPresenterTrait;

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromOwnResource($this->getSelectedContest()),
            'chart',
            $this->getSelectedContest()
        );
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
     * @phpstan-return (Chart&IComponent)[]
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function getCharts(): array
    {
        return [
            'contestantsPerSeries' => new PerSeriesChart($this->getContext(), $this->getSelectedContest()),
            'totalContestantsPerSeries' => new AggregatedSeriesChart($this->getContext(), $this->getSelectedContest()),
            'contestantsPerYears' => new PerYearsChart($this->getContext(), $this->getSelectedContest()),
            'totalPersons' => new TotalPersonsChart($this->getContext()),
            'submitsPerSeries' => new SubmitsPerSeriesChart($this->getContext(), $this->getSelectedContestYear()),
            'geo' => new ParticipantGeoChart($this->getContext(), $this->getSelectedContestYear()),
        ];
    }
}
