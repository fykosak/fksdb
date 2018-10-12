<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\React\Fyziklani\RoutingEdit;
use Nette\Application\Responses\JsonResponse;
use Nette\Utils\Json;

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

    public function authorizedEdit() {
        // TODO now can edit routing anybody
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms')));
    }

    public function authorizedDownload() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms')));
    }

    public function authorizedDefault() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms')));
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

    public function createComponentDownload() {
        $control = new RoutingDownload($this->getTranslator());
        $buildings = $this->getEvent()->getParameter('buildings');
        $control->setBuildings($buildings);
        $control->setRooms($this->getRooms());
        $control->setTeams($this->serviceFyziklaniTeam->getTeams($this->getEventId()));
        return $control;
    }


    public function createComponentRouting() {
       return $this->fyziklaniComponentsFactory->createRoutingEdit($this->context,$this->getEvent());
    }
}
