<?php

namespace EventModule;

use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\Controls\Fyziklani\RoutingEdit;
use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DeprecatedException;
use ReactMessage;

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
    public function injectServiceFyziklaniTeamPosition(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition): void {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

    public function titleDefault(): void {
        $this->setTitle(_('Rozdělení do místností'));
        $this->setIcon('fa fa-arrows');
    }

    public function titleEdit(): void {
        $this->setTitle(_('Edit routing'));
        $this->setIcon('fa fa-pencil');
    }

    public function titleDownload(): void {
        $this->setTitle(_('Download routing'));
        $this->setIcon('fa fa-download');
    }

    public function titleList(): void {
        $this->setTitle(_('List of all teams'));
        $this->setIcon('fa fa-print');
    }

    public function titlePreview(): void {
        $this->setTitle(_('Preview'));
        $this->setIcon('fa fa-search');
    }

    /**
     * @param ModelEvent $event
     * @return bool
     */
    protected function isEnabledForEvent(ModelEvent $event): bool {
        return $event->event_type_id === 1;
    }

    public function authorizedEdit(): void {
        $this->setAuthorized(false);
        // $this->setAuthorized(($this->eventIsAllowed('event.seating', 'edit')));
    }

    public function authorizedDownload(): void {
        $this->setAuthorized(false);
        // $this->setAuthorized(($this->eventIsAllowed('event.seating', 'download')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedPreview(): void {
        $this->setAuthorized(($this->eventIsAllowed('event.seating', 'preview')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedList(): void {
        $this->setAuthorized(($this->eventIsAllowed('event.seating', 'list')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDefault(): void {
        $download = $this->eventIsAllowed('event.seating', 'download');
        $edit = $this->eventIsAllowed('event.seating', 'edit');
        $this->setAuthorized($download || $edit);
    }


    /**
     * @throws AbortException
     */
    public function renderEdit(): void {
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
    public function renderList(): void {
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
    public function renderPreview(): void {
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
