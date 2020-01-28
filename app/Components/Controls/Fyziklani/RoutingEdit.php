<?php

namespace FKSDB\Components\Controls\Fyziklani;

use BasePresenter;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use ReactMessage;

/**
 * Class Routing
 */
class RoutingEdit extends FyziklaniReactControl {

    /**
     * @return string
     * @throws JsonException
     */
    public function getData(): string {
        return Json::encode([
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->event),
            'rooms' => $this->getRooms(),
        ]);
    }

    /**
     * @return string
     */
    public function getMode(): string {
        return '';
    }

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'routing';
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    public function getActions(): array {
        $actions = parent::getActions();
        $actions['save'] = $this->link('save!');
        return $actions;
    }

    /**
     * @throws AbortException
     */
    public function handleSave() {
        $data = $this->getHttpRequest()->getPost('requestData');
        $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
        $response = new ReactResponse();
        $response->setAct('update-teams');
        $response->setData(['updatedTeams' => $updatedTeams]);
        $response->addMessage(new ReactMessage(_('Zmeny boli uložené'), BasePresenter::FLASH_SUCCESS));
        $this->getPresenter()->sendResponse($response);
    }
}
