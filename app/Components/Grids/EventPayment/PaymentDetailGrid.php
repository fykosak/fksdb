<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;
use FKSDB\ORM\ModelEventPersonAccommodation;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class PaymentDetailGrid
 * @package FKSDB\Components\Grids\Payment
 * @property FileTemplate $template
 */
class PaymentDetailGrid extends Control {
    /**
     * @var \ServiceEventParticipant
     */
    private $serviceEventParticipant;
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var ModelEventPayment
     */
    private $eventPayment;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var mixed
     */
    private $data;
    /**
     * @var ITranslator
     */
    private $translator;

    public function __construct(ITranslator $translator, ModelEventPayment $eventPayment, ModelEvent $event, $data, \ServiceEventPersonAccommodation $serviceEventPersonAccommodation, \ServiceEventParticipant $serviceEventParticipant) {
        parent::__construct();
        $this->serviceEventParticipant = $serviceEventParticipant;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->event = $event;
        $this->eventPayment = $eventPayment;
        $this->data = $data;
        $this->translator = $translator;
    }

    public function render() {

        $data = [];

        foreach ($this->data['accommodated_person_ids'] as $id) {

            $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $eventAcc = $model->getEventAccommodation();
            $fromDate = $model->getEventAccommodation()->date->format('d. m.');
            $toDate = $model->getEventAccommodation()->date->add(new \DateInterval('P1D'))->format('d. m. Y');
            $data[] = [
                'label' => \sprintf(_('Ubytovaní %s od %s do %s'), $model->getPerson()->getFullName(), $fromDate, $toDate),
                'kc' => $eventAcc->price_kc,
                'eur' => $eventAcc->price_eur,
            ];

        }
        /*
        $schedule = $this->event->getParameter('schedule');
        foreach ($this->data['event_participants'] as $id) {
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

        foreach ($this->data['event_participants'] as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $this->price['kc'] += $model->price;
        }
*/
        $this->template->data = $data;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'PaymentDetailGrid.latte');
        $this->template->render();
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
