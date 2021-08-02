<?php

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\Exports\Formats\PlainTextResponse;

class TeacherEventModel extends EventModel
{

    public function createResponse(): PlainTextResponse
    {
        $query = $this->explorer->query(
            "select distinct ap.`name`, ap.`surname`, ap.`id`, ap.`street`,
ap.`town`, ap.`postcode`, ap.`country`, ap.`fullname`,
ap.`gender`, ap.`born`,
-- ap.`school`, ap.`school-name`, ap.`end-year`,
ap.`email`, ap.`spam-flag`, ap.`spam-date`,
'U' as `teacher` from v_aesop_person ap
join `e_fyziklani_team` eft on ap.`x-person_id` = eft.`teacher_id`
join `event` e on e.`event_id` = eft.`event_id`
where
	e.`event_type_id` = ?
	and e.`year` = ?
	and eft.status = 'participated'
order by surname, name",
            $this->mapEventNameToTypeId(),
            $this->contestYear->year
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

    protected function getMask(): string
    {
        return $this->contestYear->getContest()->getContestSymbol() . '.fyziklani.ucitele';
    }
}
