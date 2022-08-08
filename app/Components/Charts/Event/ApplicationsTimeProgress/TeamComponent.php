<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\ApplicationsTimeProgress;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventTypeModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class TeamComponent extends FrontEndComponent implements Chart
{

    private EventTypeModel $eventType;

    public function __construct(Container $context, EventModel $event)
    {
        parent::__construct($context, 'chart.events.teams.time-progress');
        $this->eventType = $event->event_type;
    }

    protected function getData(): array
    {
        $data = [
            'teams' => [],
            'events' => [],
        ];
        foreach ($this->eventType->getEvents() as $row) {
            $event = EventModel::createFromActiveRow($row);
            $data['teams'][$event->event_id] = TeamService2::serialiseTeams($event);
            $data['events'][$event->event_id] = $event->__toArray();
        }
        return $data;
    }

    public function getTitle(): string
    {
        return _('Team applications time progress');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
