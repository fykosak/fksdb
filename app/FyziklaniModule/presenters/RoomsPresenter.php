<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\RoutingDownload;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RoomsPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Rozdělení do místností'));
        $this->setIcon('fa fa-arrows');
    }

    public function titleEdit() {
        $this->setTitle(_('Edit routing'));
        $this->setIcon('fa fa-pencil');
    }

    public function titleDownload() {
        $this->setTitle(_('Download routing'));
        $this->setIcon('fa fa-download');
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedEdit() {
        // TODO now can edit routing anybody
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms.edit')));
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDownload() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms.download')));
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDefault() {
        $download = $this->eventIsAllowed('fyziklani', 'rooms.download');
        $edit = $this->eventIsAllowed('fyziklani', 'rooms.edit');
        $this->setAuthorized($download || $edit);
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderEdit() {
        if ($this->isAjax()) {
            $data = $this->getHttpRequest()->getPost('requestData');
            $updatedTeams = $this->serviceBrawlTeamPosition->updateRouting($data);
            $response = new \ReactResponse();
            $response->setAct('update-teams');
            $response->setData(['updatedTeams' => $updatedTeams]);
            $response->addMessage(new \ReactMessage(_('Zmeny boli uložené'), 'success'));
            $this->sendResponse($response);
        }
    }

    /**
     * @return RoutingDownload
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentDownload() {
        $control = new RoutingDownload($this->getTranslator());
        $buildings = $this->getEvent()->getParameter('gameSetup')['buildings'];
        $control->setBuildings($buildings);
        $control->setRooms($this->getRooms());
        $control->setTeams($this->serviceFyziklaniTeam->getTeamsArray($this->getEvent()));
        return $control;
    }

    /**
     * @return \FKSDB\Components\React\Fyziklani\RoutingEdit
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentRouting() {
        return $this->fyziklaniComponentsFactory->createRoutingEdit($this->getEvent());
    }
}
