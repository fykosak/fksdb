<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\Controls\Fyziklani\RoutingEdit;
use FKSDB\Components\Controls\Fyziklani\SittingControl;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use ReactMessage;
use Tracy\Debugger;

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
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedEdit() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani.rooms', 'edit')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDownload() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani.rooms', 'download')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $download = $this->eventIsAllowed('fyziklani.rooms', 'download');
        $edit = $this->eventIsAllowed('fyziklani.rooms', 'edit');
        $this->setAuthorized($download || $edit);
    }


    /**
     * @throws AbortException
     */
    public function renderEdit() {
        if ($this->isAjax()) {
            $data = $this->getHttpRequest()->getPost('requestData');
            $updatedTeams = $this->getServiceFyziklaniTeamPosition()->updateRouting($data);
            $response = new ReactResponse();
            $response->setAct('update-teams');
            $response->setData(['updatedTeams' => $updatedTeams]);
            $response->addMessage(new ReactMessage(_('Zmeny boli uložené'), \BasePresenter::FLASH_SUCCESS));
            $this->sendResponse($response);
        }
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderList() {
        $this->template->event = $this->getEvent();
        $this->template->teams = $this->getEvent()->getTeams()->limit(5);
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderPreview() {
        $this->template->event = $this->getEvent();
    }

    /**
     * @return RoutingDownload
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentDownload(): RoutingDownload {
        return $this->fyziklaniComponentsFactory->createRoutingDownload($this->getEvent());
    }

    /**
     * @return RoutingEdit
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentRouting(): RoutingEdit {
        return $this->fyziklaniComponentsFactory->createRoutingEdit($this->getEvent());
    }

    /**
     * @return SittingControl
     */
    public function createComponentSitting(): SittingControl {
        return new SittingControl($this->getServiceFyziklaniTeamPosition(), $this->getTranslator());
    }
}
