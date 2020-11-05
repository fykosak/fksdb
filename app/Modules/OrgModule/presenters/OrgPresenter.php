<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\OrgFormComponent;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use FKSDB\UI\PageTitle;
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
     * @throws BadTypeException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit of organiser %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-pencil'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws BadTypeException
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
     * @return void
     * @throws ModelNotFoundException
     * @throws BadTypeException
     */
    public function actionEdit(): void {
        $this->traitActionEdit();
    }

    /**
     * @return ModelOrg
     * @throws ForbiddenRequestException
     * @throws BadTypeException
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
     * @throws BadTypeException
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

    /**
     * @return OrgFormComponent
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function createComponentCreateForm(): OrgFormComponent {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContest(), true);
    }

    /**
     * @return OrgFormComponent
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function createComponentEditForm(): OrgFormComponent {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContest(), false);
    }

    /**
     * @return OrgsGrid
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function createComponentGrid(): OrgsGrid {
        return new OrgsGrid($this->getContext(), $this->getSelectedContest());
    }


    /**
     * @param IResource|string|null $resource
     * @param string $privilege
     * @return bool
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
