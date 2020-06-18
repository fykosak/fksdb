<?php

namespace FKSDB\EventModule;

use FKSDB\Components\Controls\Entity\EventOrg\EventOrgForm;
use FKSDB\Components\Grids\EventOrgsGrid;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;

/**
 * Class EventOrgPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEventOrg getEntity()
 */
class EventOrgPresenter extends BasePresenter {
    use EventEntityTrait;

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;

    /**
     * @param ServiceEventOrg $serviceEventOrg
     * @return void
     */
    public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg) {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setTitle(sprintf(_('Organisers of event')), 'fa fa-users');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCreate() {
        $this->setTitle(sprintf(_('Create organiser of event')), 'fa fa-users');
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit() {
        $this->setTitle(sprintf(_('Edit Organiser of event "%s"'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-users');
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /**
     * @throws AbortException
     */
    public function actionDelete() {
        try {
            list($message) = $this->traitHandleDelete();
            $this->flashMessage($message->getMessage(), $message->getLevel());
            $this->redirect('list');
        } catch (BadRequestException $exception) {
            $this->flashMessage(_('Error during deleting'), self::FLASH_ERROR);
            $this->redirect('list');
        }
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
    }

    protected function getORMService(): ServiceEventOrg {
        return $this->serviceEventOrg;
    }

    /**
     * @return EventOrgsGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentGrid(): EventOrgsGrid {
        return new EventOrgsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return Control
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentCreateForm(): Control {
        return new EventOrgForm($this->getContext(), $this->getEvent(), true);
    }

    /**
     * @return Control
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentEditForm(): Control {
        return new EventOrgForm($this->getContext(), $this->getEvent(), false);
    }

}
