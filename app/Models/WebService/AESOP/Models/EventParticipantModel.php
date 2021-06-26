<?php

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\WebService\AESOP\AESOPFormat;
use Nette\Database\Explorer;
use Nette\DI\Container;

class EventParticipantModel extends AESOPModel {

    private string $eventName;
    private ServiceEvent $serviceEvent;
    private Explorer $explorer;

    public function __construct(Container $container, ModelContestYear $contestYear, string $eventName) {
        parent::__construct($container, $contestYear);
        $this->eventName = $eventName;
    }

    public function injectSecondary(ServiceEvent $serviceEvent, Explorer $explorer): void {
        $this->serviceEvent = $serviceEvent;
        $this->explorer = $explorer;
    }

    private function mapEventNameToTypeId(): int {
        $idMapping = [
            'dsef' => 2,
            'vaf' => 3,
            'sous.j' => 4,
            'sous.p' => 5,
            'tsaf' => 7,
            'tabor' => 10,
            'setkani.j' => 11,
            'setkani.p' => 12,
            'dsef2' => 14,
            'fov' => 16,
        ];
        return $idMapping[$this->eventName] ?? 0;
    }

    protected function createFormat(): AESOPFormat {
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
        return new AESOPFormat([
            'version' => 1,
            'event' => $this->getMask(),
            'year' => $this->contestYear->ac_year,
            'date' => date('Y-m-d H:i:s'),
            'errors-to' => 'it@fykos.cz',
            'max-rank' => $query->getRowCount(),
            'id-scope' => self::ID_SCOPE,
            'start-date' => $event->begin->format('Y-m-d'),
            'end-date' => $event->end->format('Y-m-d'),
        ], $query->fetchAll(), array_keys($query->getColumnTypes()));
    }

    protected function getMask(): string {
        $maskMapping = [
            'sous.j' => 'sous.jaro',
            'sous.p' => 'sous.podzim',
            'setkani.j' => 'setkani.jaro',
            'setkani.p' => 'setkani.podzim',
        ];
        return $this->contestYear->getContest()->getContestSymbol() . '.' . $maskMapping[$this->eventName] ?? $this->eventName;
    }
}
