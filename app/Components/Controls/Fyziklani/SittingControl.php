<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Tracy\Debugger;

/**
 * Class SittingControl
 * @package FKSDB\Components\Controls\Fyziklani
 */
class SittingControl extends Control {
    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * SittingControl constructor.
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param ITranslator $translator
     */
    public function __construct(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition, ITranslator $translator) {
        parent::__construct();
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->translator = $translator;
    }

    /**
     * @param ModelEvent $event
     */
    public function renderAll(ModelEvent $event) {
        $this->template->places = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event));
        $this->template->teams = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event))
            ->where('e_fyziklani_team_id IS NOT NULL');
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Sitting.latte');
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }

    /**
     * @param ModelEvent $event
     * @param int $teamId
     */
    public function renderTeam(ModelEvent $event, int $teamId) {
        $this->template->places = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event));
        $this->template->teams = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event))
            ->where('e_fyziklani_team_id', $teamId);

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Sitting.latte');
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }

    /**
     * @param ModelEvent $event
     * @return int[]
     */
    private function getRooms(ModelEvent $event): array {
        try {
            return $event->getParameter('rooms');
        } catch (\Exception $exception) {
            return [];
        }
    }
}
