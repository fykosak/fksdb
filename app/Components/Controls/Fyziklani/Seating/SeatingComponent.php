<?php

namespace FKSDB\Components\Controls\Fyziklani\Seating;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;

class SeatingComponent extends BaseComponent {

    private ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition;

    private EventDispatchFactory $eventDispatchFactory;

    final public function injectServicePrimary(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition, EventDispatchFactory $eventDispatchFactory): void {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    final public function renderAll(ModelEvent $event): void {
        $this->render($event, 'all');
    }

    final public function renderTeam(ModelFyziklaniTeam $team): void {
        $this->template->team = $team;
        $this->render($team->getEvent(), 'single', $team->game_lang ?? 'cs');
    }

    final public function renderDev(ModelEvent $event): void {
        $this->template->teams = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event))
            ->where('e_fyziklani_team_id IS NOT NULL');
        $this->render($event, 'dev');
    }

    final public function render(ModelEvent $event, string $mode, string $lang = 'cs'): void {
        $this->template->places = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event));
        $this->template->mode = $mode;
        $this->template->lang = $lang;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.' . $mode . '.latte');
    }

    private function getRooms(ModelEvent $event): array {
        try {
            return $this->eventDispatchFactory->getDummyHolder($event)->getParameter('rooms') ?: [];
        } catch (\Exception $exception) {
            return [];
        }
    }
}
