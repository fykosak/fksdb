<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\Exports\Formats\PlainTextResponse;

class EventParticipantModel extends EventModel
{
    public function createResponse(): PlainTextResponse
    {
        $query = $this->explorer->query(
            "select ap.*, 
if(ep.`status`='participated','','N') as `status`
from v_aesop_person ap
right join event_participant ep on ep.`person_id` = ap.`x-person_id`
join `event` e on e.`event_id` = ep.`event_id`
where
	ap.`x-ac_year` = ?
	and e.`event_type_id` = ?
	and e.`year` = ?
	and ep.status in ('participated','missed','cancelled','rejected','interested')
order by surname, name",
            $this->contestYear->ac_year,
            $this->mapEventNameToTypeId(),
            $this->contestYear->year,
        );
        $event = $this->serviceEvent->getByEventTypeId($this->contestYear, $this->mapEventNameToTypeId());
        return $this->formatResponse(
            $this->getDefaultParams() + [
                'max-rank' => $query->getRowCount(),
                'start-date' => $event->begin->format('Y-m-d'),
                'end-date' => $event->end->format('Y-m-d'),
            ],
            $query->fetchAll(),
            array_keys($query->getColumnTypes())
        );
    }

    protected function getMask(): string
    {
        $maskMapping = [
            'sous.j' => 'sous.jaro',
            'sous.p' => 'sous.podzim',
            'setkani.j' => 'setkani.jaro',
            'setkani.p' => 'setkani.podzim',
        ];
        return $this->contestYear->contest->getContestSymbol()
            . '.'
            . $maskMapping[$this->eventName] ?? $this->eventName;
    }
}
