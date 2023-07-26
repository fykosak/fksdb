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
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Container;

class TimeGeoChart extends FrontEndComponent implements Chart
{
    protected EventModel $event;
    private Cache $cache;

    public function __construct(Container $context, EventModel $event)
    {
        parent::__construct($context, 'chart.events.participants.time-geo');
        $this->event = $event;
    }

    public function inject(Storage $storage): void
    {
        $this->cache = new Cache($storage, self::class);
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Participants per country'), 'fas fa-earth-europe');
    }

    /**
     * @throws \Throwable
     */
    protected function getData(): array
    {
        return $this->cache->load($this->event->event_id, function (&$dependencies) {
            $dependencies[Cache::Expire] = '20 minutes';
            $rawData = [];
            $eventBegin = $this->event->begin->getTimestamp();
            if ($this->event->isTeamEvent()) {
                /** @var TeamModel2 $team */
                foreach ($this->event->getTeams() as $team) {
                    $isoArray = [];
                    /** @var TeamMemberModel $member */
                    foreach ($team->getMembers() as $member) {
                        $isoArray[$member->getPersonHistory()->school->address->country->alpha_3] = true;
                    }
                    foreach ($isoArray as $iso => $dummy) {
                        $rawData[] = [
                            'country' => $iso,
                            'created' => $team->created->format('c'),
                            'createdBefore' => $team->created->getTimestamp() - $eventBegin,
                        ];
                    }
                }
            } else {
                /** @var EventParticipantModel $participant */
                foreach ($this->event->getParticipants() as $participant) {
                    $rawData[] = [
                        'country' => $participant->getPersonHistory()->school->address->country->alpha_3,
                        'created' => $participant->created->format('c'),
                        'createdBefore' => $participant->created->getTimestamp() - $eventBegin,
                    ];
                }
            }
            return $rawData;
        });
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
