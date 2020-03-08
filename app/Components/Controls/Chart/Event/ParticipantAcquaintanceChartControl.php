<?php

namespace FKSDB\Components\Controls\Chart\Event;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class ParticipantAcquaintanceChartControl
 * @package FKSDB\Components\Controls\Chart
 */
class ParticipantAcquaintanceChartControl extends ReactComponent implements IChart {
    /**
     * @var int
     */
    private $event;
    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * ParticipantAcquaintanceChartControl constructor.
     * @param Container $context
     * @param ModelEvent $event
     */
    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context);
        $this->event = $event;
        $this->serviceEvent = $context->getByType(ServiceEvent::class);
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return 'participantAcquaintance';
    }

    /**
     * @return string
     * @throws JsonException
     */
    function getData(): string {
        $data = [];
        foreach ($this->event->getParticipants()->where('status', ['participated', 'applied']) as $row) {

            $participant = ModelEventParticipant::createFromActiveRow($row);

            $participant->getPerson()->getEventParticipant();
            $participants = [];
            foreach ($participant->getPerson()->getEventParticipant()->where('status', ['participated']) as $item) {
                $personParticipation = ModelEventParticipant::createFromActiveRow($item);
                $participants[] = $personParticipation->getEvent()->event_id;
            }
            $datum = [
                'person' => [
                    'name' => $participant->getPerson()->getFullName(),
                    'gender' => $participant->getPerson()->gender,
                ],
                'participation' => $participants,
            ];
            $data[] = $datum;
        }
        return Json::encode($data);
    }

    /**
     * @return string
     */
    protected function getReactId(): string {
        return 'chart.participant-acquaintance';
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Participant acquaintance');
    }

    /**
     * @return Control
     */
    public function getControl(): Control {
        return $this;
    }
}
