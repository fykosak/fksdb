<?php

namespace FKSDB\Components\React\ReactComponent\Events;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventType;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class TeamApplicationsTimeProgress
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamApplicationsTimeProgress extends ReactComponent implements IChart {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ModelEventType $eventType;

    private ServiceEvent $serviceEvent;

    /**
     * TeamApplicationsTimeProgress constructor.
     * @param Container $context
     * @param ModelEvent $event
     */
    public function __construct(Container $context, ModelEvent $event) {
        parent::__construct($context);
        $this->eventType = $event->getEventType();
    }

    public function injectPrimary(ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceEvent $serviceEvent): void {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceEvent = $serviceEvent;
    }


    protected function getReactId(): string {
        return 'events.applications-time-progress.teams';
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function getData(): string {
        $data = [
            'teams' => [],
            'events' => [],
        ];
        /**
         * @var ModelEvent $event
         */
        foreach ($this->serviceEvent->getEventsByType($this->eventType) as $event) {
            $data['teams'][$event->event_id] = $this->serviceFyziklaniTeam->getTeamsAsArray($event);
            $data['events'][$event->event_id] = $event->__toArray();
        }
        return Json::encode($data);
    }

    public function getAction(): string {
        return 'teamApplicationProgress';
    }

    public function getTitle(): string {
        return 'Team applications time progress';
    }

    public function getControl(): Control {
        return $this;
    }

    public function getDescription(): ?string {
        return null;
    }
}
