<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\Charts\Event\Applications\ProgressComponent;
use FKSDB\Components\Charts\Event\Applications\TimeGeoChart;
use FKSDB\Components\Charts\Event\Model\GraphComponent;
use FKSDB\Components\Charts\Event\ParticipantAcquaintance\ParticipantAcquaintanceChart;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\ComponentModel\IComponent;

final class ChartPresenter extends BasePresenter
{
    use ChartPresenterTrait;

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed('event.chart', 'list');
    }

    /**
     * @throws EventNotFoundException
     * @throws UnsupportedLanguageException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function startup(): void
    {
        parent::startup();
        $this->registerCharts();
    }

    /**
     * @phpstan-return (Chart&IComponent)[]
     * @throws EventNotFoundException
     */
    protected function getCharts(): array
    {
        $charts = [
            'timeProgress' => new ProgressComponent($this->getContext(), $this->getEvent(), 'time-progress'),
            'barProgress' => new ProgressComponent($this->getContext(), $this->getEvent(), 'bar-progress'),
            'timeGeo' => new TimeGeoChart($this->getContext(), $this->getEvent()),
            'model' => new GraphComponent(
                $this->getContext(),
                $this->eventDispatchFactory->getEventMachine($this->getEvent())
            ),
        ];

        if (!$this->getEvent()->isTeamEvent()) {
            $charts['acquaintance'] = new ParticipantAcquaintanceChart($this->getContext(), $this->getEvent());
        }
        return $charts;
    }
}
