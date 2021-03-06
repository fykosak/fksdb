<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\EventOrgFormComponent;
use FKSDB\Components\Grids\EventOrg\EventOrgsGrid;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Messages\Message;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use FKSDB\Models\ORM\Services\ServiceEventOrg;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method ModelEventOrg getEntity()
 */
class EventOrgPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    private ServiceEventOrg $serviceEventOrg;

    final public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg): void {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(sprintf(_('Organisers of event')), 'fa fa-user-tie');
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(sprintf(_('Create organiser of event')), 'fa fa-user-plus');
    }

    /**
     * @return void
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit Organiser of event "%s"'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-user-edit'));
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    public function actionDelete(): void {
        try {
            $this->traitHandleDelete();
            $this->flashMessage(_('Entity has been deleted'), Message::LVL_WARNING);
            $this->redirect('list');
        } catch (BadRequestException $exception) {
            $this->flashMessage(_('Error during deleting'), self::FLASH_ERROR);
            $this->redirect('list');
        }
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
     * @return EventOrgFormComponent
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): EventOrgFormComponent {
        return new EventOrgFormComponent($this->getContext(), $this->getEvent(), null);
    }

    /**
     * @return EventOrgFormComponent
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentEditForm(): EventOrgFormComponent {
        return new EventOrgFormComponent($this->getContext(), $this->getEvent(), $this->getEntity());
    }

}
