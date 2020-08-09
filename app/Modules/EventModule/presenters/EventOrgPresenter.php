<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Entity\EventOrg\EventOrgFormComponent;
use FKSDB\Components\Grids\EventOrgsGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEventOrg;
use FKSDB\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class EventOrgPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEventOrg getEntity()
 */
class EventOrgPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    private ServiceEventOrg $serviceEventOrg;

    public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg): void {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(sprintf(_('Organisers of event')), 'fa fa-users');
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(sprintf(_('Create organiser of event')), 'fa fa-users');
    }

    /**
     * @return void
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws BadTypeException
     */
    public function titleEdit() {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit Organiser of event "%s"'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-users'));
    }

    /**
     * @param IResource|string|null $resource
     * @param string $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /**
     * @throws AbortException
     */
    public function actionDelete() {
        try {
            [$message] = $this->traitHandleDelete();
            $this->flashMessage($message->getMessage(), $message->getLevel());
            $this->redirect('list');
        } catch (BadRequestException $exception) {
            $this->flashMessage(_('Error during deleting'), self::FLASH_ERROR);
            $this->redirect('list');
        }
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     * @throws BadTypeException
     */
    public function actionEdit() {
        $this->traitActionEdit();
    }

    protected function getORMService(): ServiceEventOrg {
        return $this->serviceEventOrg;
    }

    /**
     * @return EventOrgsGrid
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): EventOrgsGrid {
        return new EventOrgsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return Control
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): Control {
        return new EventOrgFormComponent($this->getContext(), $this->getEvent(), true);
    }

    /**
     * @return Control
     * @throws EventNotFoundException
     */
    protected function createComponentEditForm(): Control {
        return new EventOrgFormComponent($this->getContext(), $this->getEvent(), false);
    }

}
