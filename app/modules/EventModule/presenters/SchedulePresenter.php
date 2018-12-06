<?php

namespace EventModule;

use Nette\Database\Connection;
use Nette\InvalidArgumentException;

class SchedulePresenter extends BasePresenter {
    /**
     * @var Connection
     */
    private $connection;

    public function injectConnection(Connection $connection) {
        $this->connection = $connection;
    }

    public function titleDefault() {
        $this->setTitle(\sprintf(_('Schedule.')));
        $this->setIcon('fa fa-calendar-check-o');
    }

    protected function hasEventSchedule() {
        try {
            $this->getEvent()->getParameter('schedule');
        } catch (InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    public function authorizedDefault() {

        if ($this->hasEventSchedule()) {
            return $this->setAuthorized($this->eventIsAllowed('event.schedule', 'default'));
        }
        return $this->setAuthorized(false);
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
