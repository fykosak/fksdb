<?php

namespace FKSDB\Components\Controls\Fyziklani\Seating;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;

/**
 * Class SeatingControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SeatingComponent extends BaseComponent {

    private ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition;

    private EventDispatchFactory $eventDispatchFactory;

    final public function injectServicePrimary(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition, EventDispatchFactory $eventDispatchFactory): void {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    public function renderAll(ModelEvent $event): void {
        $this->render($event, 'all');
    }

    public function renderTeam(ModelEvent $event, int $teamId, string $lang): void {
        $this->template->teamId = $teamId;
        $this->render($event, 'single', $lang);
    }

    public function renderDev(ModelEvent $event): void {
        $this->template->teams = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event))
            ->where('e_fyziklani_team_id IS NOT NULL');
        $this->render($event, 'dev');
    }

    public function render(ModelEvent $event, string $mode, string $lang = 'cs'): void {
        $this->template->places = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event));
        $this->template->mode = $mode;
        $this->template->lang = $lang;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.' . $mode . '.latte');
        $this->template->render();
    }

    private function getRooms(ModelEvent $event): array {
        try {
            return $this->eventDispatchFactory->getDummyHolder($event)->getParameter('rooms') ?: [];
        } catch (\Exception $exception) {
            return [];
        }
    }
}
