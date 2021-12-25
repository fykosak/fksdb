<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\OrgFormComponent;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Services\ServiceOrg;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

class OrgPresenter extends BasePresenter
{
    use EntityPresenterTrait {
        getEntity as traitGetEntity;
    }

    private ServiceOrg $serviceOrg;

    final public function injectServiceOrg(ServiceOrg $serviceOrg): void
    {
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            sprintf(_('Edit of organiser %s'), $this->getEntity()->getPerson()->getFullName()),
            'fa fa-user-edit'
        );
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function getEntity(): ModelOrg
    {
        /** @var ModelOrg $entity */
        $entity = $this->traitGetEntity();
        if ($entity->contest_id != $this->getSelectedContest()->contest_id) {
            throw new ForbiddenRequestException(_('Editing of organiser outside chosen seminar.'));
        }
        return $entity;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(sprintf(_('Org %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-user');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create an organiser'), 'fa fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Organisers'), 'fa fa-user-tie');
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): ServiceOrg
    {
        return $this->serviceOrg;
    }

    protected function getModelResource(): string
    {
        return ModelOrg::RESOURCE_ID;
    }

    protected function createComponentCreateForm(): OrgFormComponent
    {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContest(), null);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): OrgFormComponent
    {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContest(), $this->getEntity());
    }

    protected function createComponentGrid(): OrgsGrid
    {
        return new OrgsGrid($this->getContext(), $this->getSelectedContest());
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
