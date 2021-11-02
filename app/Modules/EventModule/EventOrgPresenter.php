<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\EntityForms\EventOrgFormComponent;
use FKSDB\Components\Grids\EventOrg\EventOrgsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use FKSDB\Models\ORM\Services\ServiceEventOrg;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * @method ModelEventOrg getEntity()
 */
class EventOrgPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    private ServiceEventOrg $serviceEventOrg;

    final public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg): void
    {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Organisers of event'), 'fa fa-user-tie');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create organiser of event'), 'fa fa-user-plus');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            sprintf(_('Edit Organiser of event "%s"'), $this->getEntity()->getPerson()->getFullName()),
            'fa fa-user-edit'
        );
    }

    public function actionDelete(): void
    {
        try {
            $this->traitHandleDelete();
            $this->flashMessage(_('Entity has been deleted'), Message::LVL_WARNING);
            $this->redirect('list');
        } catch (BadRequestException $exception) {
            $this->flashMessage(_('Error during deleting'), self::FLASH_ERROR);
            $this->redirect('list');
        }
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    protected function getORMService(): ServiceEventOrg
    {
        return $this->serviceEventOrg;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): EventOrgsGrid
    {
        return new EventOrgsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): EventOrgFormComponent
    {
        return new EventOrgFormComponent($this->getContext(), $this->getEvent(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     */
    protected function createComponentEditForm(): EventOrgFormComponent
    {
        return new EventOrgFormComponent($this->getContext(), $this->getEvent(), $this->getEntity());
    }
}
