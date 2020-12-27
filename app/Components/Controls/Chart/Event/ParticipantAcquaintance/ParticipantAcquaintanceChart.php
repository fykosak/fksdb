<?php

namespace FKSDB\Components\Controls\Chart\Event\ParticipantAcquaintance;

use FKSDB\Components\Controls\Chart\Contestants\Core\Chart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\DI\Container;

/**
 * Class ParticipantAcquaintanceChartControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ParticipantAcquaintanceChart extends ReactComponent implements Chart {

    private ModelEvent $event;

    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context, 'chart.events.participants.acquaintance');
        $this->event = $event;
    }

    public function getData(): array {
        $data = [];
        foreach ($this->event->getParticipants()->where('status', ['participated', 'applied']) as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);

            $participants = [];
            foreach ($participant->getPerson()->getEventParticipants()->where('status', ['participated']) as $item) {
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
        return $data;
    }

    public function getTitle(): string {
        return _('Participant acquaintance');
    }

    public function getControl(): self {
        return $this;
    }

    public function getDescription(): ?string {
        return null;
    }
}
