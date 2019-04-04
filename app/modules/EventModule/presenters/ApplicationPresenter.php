<?php

namespace EventModule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Events\ApplicationGrid;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationPresenter
 * @package EventModule
 */
class ApplicationPresenter extends AbstractApplicationPresenter {
    /**
     * @var ServiceEventParticipant
     */
    private $serviceEventParticipant;

    /**
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function injectServiceEventParticipant(ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function titleList() {
        $this->setTitle(_('List of applications'));
        $this->setIcon('fa fa-users');
    }

    public function titleDetail() {
        $this->setTitle(_('Application detail'));
        $this->setIcon('fa fa-user');
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDetail() {
        if (\in_array($this->getEvent()->event_type_id, [1, 9])) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->eventIsAllowed('event.application', 'detail'));
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedList() {
        if (\in_array($this->getEvent()->event_type_id, [1, 9])) {
            $this->setAuthorized(false);
            return;
        }
        $this->setAuthorized($this->eventIsAllowed('event.application', 'list'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function loadModel(int $id) {
        $row = $this->serviceEventParticipant->findByPrimary($id);
        if (!$row) {
            throw new BadRequestException('Model not found');
        }
        $model = ModelEventParticipant::createFromTableRow($row);
        if ($model->event_id != $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }
        $this->model = $model;
    }

    /**
     * @return ApplicationGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentGrid(): BaseGrid {
        return new ApplicationGrid($this->getEvent());
    }

    /**
     * @return ModelEventParticipant
     */
    protected function getModel(): ModelEventParticipant {
        return $this->model;
    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function renderDetail() {
        $this->template->fields = $this->getEvent()->getHolder()->getPrimaryHolder()->getFields();
        $this->template->model = $this->getModel();
    }
}
