<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

/**
 * Class SittingControl
 * @package FKSDB\Components\Controls\Fyziklani
 */
class SeatingControl extends Control {
    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * SeatingControl constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct();
        $this->serviceFyziklaniTeamPosition = $container->getByType(ServiceFyziklaniTeamPosition::class);
        $this->translator = $container->getByType(ITranslator::class);
    }

    /**
     * @param ModelEvent $event
     */
    public function renderAll(ModelEvent $event) {
        $this->render($event, 'all');
    }

    /**
     * @param ModelEvent $event
     * @param int $teamId
     * @param string $lang
     */
    public function renderTeam(ModelEvent $event, int $teamId, string $lang) {
        $this->template->teamId = $teamId;
        $this->render($event, 'single', $lang);
    }

    /**
     * @param ModelEvent $event
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
     */
    public function render(ModelEvent $event, string $mode, string $lang = 'cs') {
        $this->template->places = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms($event));
        $this->template->mode = $mode;
        $this->template->lang = $lang;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Seating.' . $mode . '.latte');
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
