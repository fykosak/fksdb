<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class ParticipantsTimeGeoChart extends FrontEndComponent implements Chart
{

    protected EventModel $event;

    public function __construct(Container $context, EventModel $event)
    {
        parent::__construct($context, 'chart.events.participants.time-geo');
        $this->event = $event;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Participants per country'), 'fas fa-earth-europe');
    }

    protected function getData(): array
    {
        $rawData = [];
        if ($this->event->isTeamEvent()) {
            /** @var TeamModel2 $team */
            foreach ($this->event->getTeams() as $team) {
                /** @var TeamMemberModel $member */
                foreach ($team->getMembers() as $member) {
                    $iso = $member->getPersonHistory()->school->address->country->alpha_3;
                    $rawData[] = [
                        'country' => $iso,
                        'created' => $team->created->format('c'),
                    ];
                }
            }
        } else {
            /** @var EventParticipantModel $participant */
            foreach ($this->event->getParticipants() as $participant) {
                $iso = $participant->getPersonHistory()->school->address->country->alpha_3;
                $rawData[] = [
                    'country' => $iso,
                    'created' => $participant->created->format('c'),
                ];
            }
        }
        return $rawData;
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
