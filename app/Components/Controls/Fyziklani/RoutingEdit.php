<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class RoutingEdit
 * @author Michal Červeňák <miso@fykos.cz>
 */
class RoutingEdit extends FyziklaniReactControl {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ServiceFyziklaniRoom $serviceFyziklaniRoom;

    private ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition;

    public function injectPrimary(
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition,
        ServiceFyziklaniRoom $serviceFyziklaniRoom
    ): void {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->serviceFyziklaniRoom = $serviceFyziklaniRoom;
    }

    /**
     * @param mixed ...$args
     * @return string
     * @throws JsonException
     */
    public function getData(...$args): string {
        return Json::encode([
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->getEvent()),
            'rooms' => $this->getRooms(),
        ]);
    }

    public function getReactId(...$args): string {
        return 'fyziklani.routing';
    }

    /**
     * @throws InvalidLinkException
     */
    protected function configure() {
        $this->addAction('save', $this->link('save!'));
        parent::configure();
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadTypeException
     */
    public function handleSave() {
        $data = $this->getHttpRequest()->getPost('requestData');
        $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
        $response = new ReactResponse();
        $response->setAct('update-teams');
        $response->setData(['updatedTeams' => $updatedTeams]);
        $response->addMessage(new Message(_('Routing has been saved'), Message::LVL_SUCCESS));
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
