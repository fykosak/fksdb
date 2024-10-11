<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventTypeModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-import-type SerializedEventModel from EventModel
 */
class ProgressComponent extends FrontEndComponent implements Chart
{
    private EventTypeModel $eventType;

    public function __construct(Container $context, EventModel $event, string $type)
    {
        parent::__construct($context, 'chart.events.' . $type);
        $this->eventType = $event->event_type;
    }

    /**
     * @phpstan-return array{
     *     applications:array<int,array<int,array{created:string,createdBefore:int}>>,
     *     events:array<int,SerializedEventModel>,
     * }
     */
    protected function getData(): array
    {
        $data = [
            'applications' => [],
            'events' => [],
        ];
        /** @var EventModel $event */
        foreach ($this->eventType->getEvents() as $event) {
            $eventBegin = $event->begin->getTimestamp();
            $applications = [];
            if ($event->isTeamEvent()) {
                /** @var TeamModel2 $team */
                foreach ($event->getPossiblyAttendingTeams() as $team) {
                    $applications[] = [
                        'created' => $team->created->format('c'),
                        'createdBefore' => $team->created->getTimestamp() - $eventBegin,
                    ];
                }
            } else {
                /** @var EventParticipantModel $participant */
                foreach ($event->getPossiblyAttendingParticipants() as $participant) {
                    $applications[] = [
                        'created' => $participant->created->format('c'),
                        'createdBefore' => $participant->created->getTimestamp() - $eventBegin,
                    ];
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
