<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Entity\EventOrgFormComponent;
use FKSDB\Components\Grids\EventOrg\EventOrgsGrid;
use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Messages\Message;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use FKSDB\Models\ORM\Services\ServiceEventOrg;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 * Class EventOrgPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEventOrg getEntity()
 */
class EventOrgPresenter extends BasePresenter {
    use EventEntityPresenterTrait;

    private ServiceEventOrg $serviceEventOrg;

    final public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg): void {
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
     * @throws CannotAccessModelException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit Organiser of event "%s"'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-users'));
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /**
     * @throws AbortException
     */
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
     * @throws InvalidStateException
     */
    protected function createComponentGrid(): EventOrgsGrid {
        return new EventOrgsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return EventOrgFormComponent
     * @throws EventNotFoundException
     * @throws InvalidStateException
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
