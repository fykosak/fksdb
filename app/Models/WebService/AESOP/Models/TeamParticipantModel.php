<?php

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\WebService\AESOP\AESOPFormat;
use Nette\DI\Container;

class TeamParticipantModel extends EventModel {

    private string $category;

    public function __construct(Container $container, ModelContestYear $contestYear, string $eventName, string $category) {
        parent::__construct($container, $contestYear, $eventName);
        $this->category = $category;
    }

    protected function createFormat(): AESOPFormat {
        $query = $this->explorer->query("select ap.*,
       eft.`rank_category`                        as `rank`,
       eft.`points`,
       if(eft.`status` = 'participated', '', 'N') as `status`
from v_aesop_person ap
         right join event_participant ep on ep.`person_id` = ap.`x-person_id`
         join `event` e on e.`event_id` = ep.`event_id`
         join `e_fyziklani_participant` efp on efp.`event_participant_id` = ep.`event_participant_id`
         join `e_fyziklani_team` eft on efp.`e_fyziklani_team_id` = eft.`e_fyziklani_team_id`
where ap.`x-ac_year` = ?
  and e.`event_type_id` = ?
  and e.`year` = ?
  and eft.`status` in ('participated', 'missed', 'cancelled', 'rejected')
  and eft.`category` = ?
order by surname, name;",
            $this->contestYear->ac_year,
            $this->mapEventNameToTypeId(),
            $this->contestYear->year,
            $this->mapCategory()
        );
        $event = $this->serviceEvent->getByEventTypeId($this->contestYear, $this->mapEventNameToTypeId());
        return new AESOPFormat([
            'version' => 1,
            'event' => $this->getMask(),
            'year' => $this->contestYear->ac_year,
            'date' => date('Y-m-d H:i:s'),
            'errors-to' => 'it@fykos.cz',
            'id-scope' => self::ID_SCOPE,
            'start-date' => $event->begin->format('Y-m-d'),
            'end-date' => $event->end->format('Y-m-d'),
        ], $query->fetchAll(), array_keys($query->getColumnTypes()));
    }

    private function mapCategory(): string {
        switch ($this->category) {
            case 'zahranicni':
                return 'F';
            case'open':
                return 'O';
            default:
                return $this->category;
        }
    }

    protected function getMask(): string {
        $mapping = [
            'fol' => 'Fol',
            'klani' => 'fyziklani',
        ];
        return $this->contestYear->getContest()->getContestSymbol() . '.' . $mapping[$this->eventName] . '.' . $this->category;
    }
}
