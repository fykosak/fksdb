<?php

namespace FKSDB\Components\Controls\Fyziklani;

use BasePresenter;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use ReactMessage;

/**
 * Class RoutingEdit
 * @author Michal Červeňák <miso@fykos.cz>
 */
class RoutingEdit extends FyziklaniReactControl {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ServiceFyziklaniRoom $serviceFyziklaniRoom;

    private ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition;

    /**
     * RoutingEdit constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, $event);
        $this->serviceFyziklaniTeam = $container->getByType(ServiceFyziklaniTeam::class);
        $this->serviceFyziklaniTeamPosition = $container->getByType(ServiceFyziklaniTeamPosition::class);
        $this->serviceFyziklaniRoom = $container->getByType(ServiceFyziklaniRoom::class);
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function getData(): string {
        return Json::encode([
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->getEvent()),
            'rooms' => $this->getRooms(),
        ]);
    }

    protected function getReactId(): string {
        return 'fyziklani.routing';
    }

    /**
     * @throws InvalidLinkException
     */
    protected function configure(): void {
        $this->addAction('save', $this->link('save!'));
        parent::configure();
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleSave(): void {
        $data = $this->getHttpRequest()->getPost('requestData');
        $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
        $response = new ReactResponse();
        $response->setAct('update-teams');
        $response->setData(['updatedTeams' => $updatedTeams]);
        $response->addMessage(new ReactMessage(_('Zmeny boli uložené'), BasePresenter::FLASH_SUCCESS));
        $this->getPresenter()->sendResponse($response);
    }

    /**
     * @return ModelFyziklaniRoom[]
     * TODO fix getParameter
     */
    protected function getRooms() {
        return $this->serviceFyziklaniRoom->getRoomsByIds([]/*$this->getEvent()->getParameter(null, 'gameSetup')['rooms']*/);
    }
}
