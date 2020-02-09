<?php

namespace EventModule;

use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\Controls\Fyziklani\RoutingEdit;
use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DeprecatedException;
use ReactMessage;
use Tracy\Debugger;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeatingPresenter extends BasePresenter {

    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;

    /**
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     */
    public function injectServiceFyziklaniTeamPosition(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition) {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

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

    public function titleList() {
        $this->setTitle(_('List of all teams'));
        $this->setIcon('fa fa-print');
    }

    public function titlePreview() {
        $this->setTitle(_('Preview'));
        $this->setIcon('fa fa-search');
    }


    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedEdit() {
        $this->setAuthorized(($this->eventIsAllowed('event.seating', 'edit')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDownload() {
        $this->setAuthorized(($this->eventIsAllowed('event.seating', 'download')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedPreview() {
        $this->setAuthorized(($this->eventIsAllowed('event.seating', 'preview')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized(($this->eventIsAllowed('event.seating', 'list')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $download = $this->eventIsAllowed('event.seating', 'download');
        $edit = $this->eventIsAllowed('event.seating', 'edit');
        $this->setAuthorized($download || $edit);
    }


    /**
     * @throws AbortException
     */
    public function renderEdit() {
        if ($this->isAjax()) {
            $data = $this->getHttpRequest()->getPost('requestData');
            $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
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
        $teams = $this->getEvent()->getTeams();
        $this->template->teams = $teams;
        $toPayAll = [];
        foreach ($teams as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $toPayAll[$team->getPrimary()] = $team->getScheduleRest();
        }
        $this->template->toPay = $toPayAll;
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
     */
    public function createComponentDownload(): RoutingDownload {
        throw new DeprecatedException();
    }

    /**
     * @return RoutingEdit
     */
    public function createComponentRouting(): RoutingEdit {
        throw new DeprecatedException();
    }

    /**
     * @return SeatingControl
     */
    public function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->serviceFyziklaniTeamPosition, $this->getTranslator());
    }
}
