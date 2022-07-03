<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\ApplicationsTimeProgress;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventType;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class TeamComponent extends FrontEndComponent implements Chart
{

    private ModelEventType $eventType;

    public function __construct(Container $context, ModelEvent $event)
    {
        parent::__construct($context, 'chart.events.teams.time-progress');
        $this->eventType = $event->getEventType();
    }

    protected function getData(): array
    {
        $data = [
            'teams' => [],
            'events' => [],
        ];
        foreach ($this->eventType->getEventsByType() as $row) {
            $event = ModelEvent::createFromActiveRow($row);
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
