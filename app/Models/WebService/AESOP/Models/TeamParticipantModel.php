<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\Exports\Formats\PlainTextResponse;
use FKSDB\Models\ORM\Models\ModelContestYear;
use Nette\DI\Container;

class TeamParticipantModel extends EventModel
{
    private string $category;

    public function __construct(
        Container $container,
        ModelContestYear $contestYear,
        string $eventName,
        string $category
    ) {
        parent::__construct($container, $contestYear, $eventName);
        $this->category = $category;
    }

    public function createResponse(): PlainTextResponse
    {
        $query = $this->explorer->query(
            "select ap.*,
       ft.`rank_category`                        as `rank`,
       ft.`points`,
       if(ft.`state` = 'participated', '', 'N') as `status`
from v_aesop_person ap
         right join fyziklani_team_member ftm on ftm.`person_id` = ap.`x-person_id`
    join fyziklani_team ft on ft.fyziklani_team_id = ftm.fyziklani_team_id
         join `event` e on e.`event_id` = ft.`event_id`
where ap.`x-ac_year` = ?
  and e.`event_type_id` = ?
  and e.`year` = ?
  and ft.`state` in ('participated', 'missed', 'cancelled', 'rejected')
  and ft.`category` = ?
order by surname, name;",
            $this->contestYear->ac_year,
            $this->mapEventNameToTypeId(),
            $this->contestYear->year,
            $this->mapCategory()
        );
        $event = $this->serviceEvent->getByEventTypeId($this->contestYear, $this->mapEventNameToTypeId());
        return $this->formatResponse(
            $this->getDefaultParams() + [
                'start-date' => $event->begin->format('Y-m-d'),
                'end-date' => $event->end->format('Y-m-d'),
            ],
            $query->fetchAll(),
            array_keys($query->getColumnTypes())
        );
    }

    private function mapCategory(): string
    {
        switch ($this->category) {
            case 'zahranicni':
                return 'F';
            case 'open':
                return 'O';
            default:
                return $this->category;
        }
    }

    protected function getMask(): string
    {
        $mapping = [
            'fol' => 'Fol',
            'klani' => 'fyziklani',
        ];
        return $this->contestYear->contest->getContestSymbol()
            . '.'
            . $mapping[$this->eventName]
            . '.'
            . $this->category;
    }
}
