<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\Application\BadRequestException;

/**
 * Class SeatingControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SeatingControl extends BaseComponent {
    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;
    /** @var EventDispatchFactory */
    private $eventDispatchFactory;

    /**
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param EventDispatchFactory $eventDispatchFactory
     * @return void
     */
    public function injectServicePrimary(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition, EventDispatchFactory $eventDispatchFactory) {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @param ModelEvent $event
     * @return void
     * @throws NeonSchemaException
     */
    public function renderAll(ModelEvent $event) {
        $this->render($event, 'all');
    }

    /**
     * @param ModelEvent $event
     * @param int $teamId
     * @param string $lang
     * @return void
     * @throws NeonSchemaException
     */
    public function renderTeam(ModelEvent $event, int $teamId, string $lang) {
        $this->template->teamId = $teamId;
        $this->render($event, 'single', $lang);
    }

    /**
     * @param ModelEvent $event
     * @return void
     * @throws NeonSchemaException
     */
    public function renderDev(ModelEvent $event) {
        $this->template->teams = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event))
            ->where('e_fyziklani_team_id IS NOT NULL');
        $this->render($event, 'dev');
    }

    /**
     * @param ModelEvent $event
     * @param string $mode
     * @param string $lang
     * @return void
     * @throws NeonSchemaException
     */
    public function render(ModelEvent $event, string $mode, string $lang = 'cs') {
        $this->template->places = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event));
        $this->template->mode = $mode;
        $this->template->lang = $lang;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Seating.' . $mode . '.latte');
        $this->template->render();
    }

    /**
     * @param ModelEvent $event
     * @return int[]
     * @throws NeonSchemaException
     */
    private function getRooms(ModelEvent $event): array {
        try {
            return $this->eventDispatchFactory->getDummyHolder($event)->getParameter('rooms') ?: [];
        } catch (BadRequestException $exception) {
            return [];
        }
    }
}
