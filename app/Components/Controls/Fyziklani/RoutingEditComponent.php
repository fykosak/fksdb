<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\React\AjaxComponent;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniRoom;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniRoom;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\Application\UI\InvalidLinkException;
use Nette\DeprecatedException;

class RoutingEditComponent extends AjaxComponent {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ServiceFyziklaniRoom $serviceFyziklaniRoom;

    private ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition;

    final public function injectPrimary(
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition,
        ServiceFyziklaniRoom $serviceFyziklaniRoom
    ): void {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->serviceFyziklaniRoom = $serviceFyziklaniRoom;
    }

    public function getData(...$args): string {
        return json_encode([
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
    protected function configure(): void {
        $this->addAction('save', $this->link('save!'));
        parent::configure();
    }

    public function handleSave(): void {
        throw new DeprecatedException();
        /*$data = $this->getHttpRequest()->getPost('requestData');
        $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
        $response = new ReactResponse();
        $response->setAct('update-teams');
        $response->setData(['updatedTeams' => $updatedTeams]);
        $response->addMessage(new Message(_('Routing has been saved'), Message::LVL_SUCCESS));
        $this->getPresenter()->sendResponse($response);*/
    }

    /**
     * @return ModelFyziklaniRoom[]
     * TODO fix getParameter
     */
    protected function getRooms(): array {
        return $this->serviceFyziklaniRoom->getRoomsByIds([]/*$this->getEvent()->getParameter(null, 'gameSetup')['rooms']*/);
    }
}
