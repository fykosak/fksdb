<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\OrgFormComponent;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\Model\Entity\ModelNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Model\ORM\Models\ModelOrg;
use FKSDB\Model\ORM\Services\ServiceOrg;
use FKSDB\Model\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class OrgPresenter
 *
 */
class OrgPresenter extends BasePresenter {
    use EntityPresenterTrait {
        getEntity as traitGetEntity;
    }

    private ServiceOrg $serviceOrg;

    final public function injectServiceOrg(ServiceOrg $serviceOrg): void {
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit of organiser %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-pencil'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Org %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-user'));
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Create an organiser'), 'fa fa-user-plus');
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('Organisers'), 'fa fa-address-book');
    }

    /**
     * @return ModelOrg
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function getEntity(): ModelOrg {
        /** @var ModelOrg $entity */
        $entity = $this->traitGetEntity();
        if ($entity->contest_id != $this->getSelectedContest()->contest_id) {
            throw new ForbiddenRequestException(_('Editing of organiser outside chosen seminar.'));
        }
        return $entity;
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): ServiceOrg {
        return $this->serviceOrg;
    }

    protected function getModelResource(): string {
        return ModelOrg::RESOURCE_ID;
    }

    protected function createComponentCreateForm(): OrgFormComponent {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContest(), null);
    }

    /**
     * @return OrgFormComponent
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): OrgFormComponent {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContest(), $this->getEntity());
    }

    protected function createComponentGrid(): OrgsGrid {
        return new OrgsGrid($this->getContext(), $this->getSelectedContest());
    }


    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}