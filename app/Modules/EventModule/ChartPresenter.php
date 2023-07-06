<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\Charts\Event\Applications\ProgressComponent;
use FKSDB\Components\Charts\Event\Applications\TimeGeoChart;
use FKSDB\Components\Charts\Event\Model\GraphComponent;
use FKSDB\Components\Charts\Event\ParticipantAcquaintance\ParticipantAcquaintanceChart;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\ChartPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;

class ChartPresenter extends BasePresenter
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
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws UnsupportedLanguageException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->registerCharts();
    }

    /**
     * @return Chart[]
     * @throws EventNotFoundException
     * @throws BadTypeException
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
