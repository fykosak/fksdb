<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventParticipant;
use Nette\Application\BadRequestException;

class EventSchedulePrice extends AbstractPreProcess {
    /**
     * @var \ServiceEventParticipant
     */
    private $serviceEventParticipant;

    public function __construct(\ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function run(array $data, ModelEvent $event) {
        $ids = $data['event_participants'];
        $schedule = $event->getParameter('schedule');
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $participantSchedule = $model->schedule;
            if ($participantSchedule) {
                $data = \json_decode($participantSchedule);
                foreach ($data as $key => $selectedId) {
                    $parallel = $this->findScheduleItem($schedule, $key, $selectedId);
                    $this->price['kc'] += $parallel['price']['kc'];
                    $this->price['eur'] += $parallel['price']['eur'];
                }
            }
        }
    }

    private function findScheduleItem($schedule, string $key, int $id) {
        foreach ($schedule as $scheduleKey => $item) {
            if ($scheduleKey !== $key) {
                continue;
            }
            foreach ($item['parallels'] as $parallel) {
                if ($parallel['id'] == $id) {
                    return $parallel;
                }
            }
        }
        throw new BadRequestException('Item nenájdený');
    }


}
