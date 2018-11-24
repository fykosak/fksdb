<?php

namespace OrgModule;

use FKSDB\ORM\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\DI\Container;

class EventSchedulePresenter extends BasePresenter {
    /**
     * @persistent
     */
    public $eventId;
    /**
     * @var \ServiceEvent
     */
    private $serviceEvent;
    /**
     *
     * @var Container
     */
    protected $container;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var Connection
     */
    private $connection;

    public function injectServiceEvent(\ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    public function injectConnection(Connection $connection) {
        $this->connection = $connection;
    }

    public function titleDefault() {
        $this->setTitle(\sprintf(_('Schedule of event %s.'), $this->getEvent()->name));
        $this->setIcon('fa fa-calendar-check-o');
    }

    /**
     * @return ModelEvent
     * @throws BadRequestException
     */
    public function getEvent() {
        if (!$this->event) {
            $row = $this->serviceEvent->findByPrimary($this->eventId);

            if ($row) {
                $event = ModelEvent::createFromTableRow($row);
                $holder = $this->container->createEventHolder($event);
                $event->setHolder($holder);
                $this->event = $event;
            } else {
                throw new BadRequestException('EventId je povinne!');
            }
        }
        return $this->event;
    }

    public function renderDefault() {
        $query = $this->connection->query('SELECT p.name,p.person_id,apps.type, group_concat(DISTINCT apps.team separator \', \') AS `team`,schedule
FROM v_person p
right join (
  select teacher_id as person_id,\'teacher\' as type, event_id, name as team, teacher_schedule as schedule
  FROM e_fyziklani_team eft
  WHERE eft.status != \'cancelled\'
  UNION ALL
  SELECT person_id, \'participant\' AS type, ep.event_id, eftp.name, ep.schedule
  FROM event_participant ep
  right join e_fyziklani_participant efp on efp.event_participant_id = ep.event_participant_id
  LEFT JOIN e_fyziklani_team eftp on eftp.e_fyziklani_team_id = efp.e_fyziklani_team_id
  WHERE eftp.status != \'cancelled\' 
) apps ON apps.person_id = p.person_id
LEFT JOIN event e ON e.event_id = apps.event_id
WHERE e.event_id=?
GROUP BY p.person_id,type,schedule', $this->getEvent()->event_id)->fetchAll();
        $schedule = $this->getEvent()->getParameter('schedule');
        $results = [];
        $stats = [];
        foreach ($query as $row) {

            $innerSchedule = [];
            if ($row->schedule) {
                $innerSchedule = json_decode($row->schedule);
            };
            $results[] = [
                'name' => $row->name,
                'schedule' => $innerSchedule,
                'person_id' => $row->person_id,
                'type' => $row->type,
                'team' => $row->team,
            ];
            foreach ($innerSchedule as $key => $item) {
                if (!isset($stats[$key])) {
                    $stats[$key] = [];
                }
                if (!isset($stats[$key][$item])) {
                    $stats[$key][$item] = 0;
                }
                $stats[$key][$item] += 1;

            }
        }

        $this->template->participants = $results;
        $this->template->schedule = $schedule;
        $this->template->stats = $stats;

    }
}
