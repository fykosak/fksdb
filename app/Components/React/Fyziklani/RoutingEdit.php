<?php

namespace FKSDB\Components\React\Fyziklani;

use Nette\Utils\Json;

/**
 * Class Routing
 */
class RoutingEdit extends FyziklaniModule {

    public function getData(): string {

        return Json::encode([
            'teams' => $this->serviceFyziklaniTeam->getTeams($this->event),
            'rooms' => $this->getRooms(),
        ]);
    }

    public function getMode(): string {
        return '';
    }

    public function getComponentName(): string {
        return 'routing';
    }

    protected function prepareActionLinks(): array {
        parent::prepareActionLinks();
        $this->addActionLink('save', $this->link('save!'));
    }

    public function handleSave() {
        $data = $this->getHttpRequest()->getPost('requestData');
        $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
        $response = new \ReactResponse();
        $response->setAct('update-teams');
        $response->setData(['updatedTeams' => $updatedTeams]);
        $response->addMessage(new \ReactMessage(_('Zmeny boli uložené'), 'success'));
        $this->getPresenter()->sendResponse($response);
    }
}
