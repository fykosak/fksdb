<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\ApplicationsTimeProgress;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventTypeModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class ProgressComponent extends FrontEndComponent implements Chart
{
    private EventTypeModel $eventType;

    public function __construct(Container $context, EventModel $event)
    {
        parent::__construct($context, 'chart.events.time-progress');
        $this->eventType = $event->event_type;
    }

    protected function getData(): array
    {
        $data = [
            'applications' => [],
            'events' => [],
        ];
        /** @var EventModel $event */
        foreach ($this->eventType->getEvents() as $event) {
            $applications = [];
            if ($event->isTeamEvent()) {
                /** @var TeamModel2 $team */
                foreach ($event->getPossiblyAttendingTeams() as $team) {
                    $applications[] = $team->created->format('c');
                }
            } else {
                /** @var EventParticipantModel $participant */
                foreach ($event->getPossiblyAttendingParticipants() as $participant) {
                    $applications[] = $participant->created->format('c');
                }
            }

            $data['applications'][$event->event_id] = $applications;
            $data['events'][$event->event_id] = $event->__toArray();
        }
        return $data;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Applications time progress'), 'fas fa-line-chart');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
