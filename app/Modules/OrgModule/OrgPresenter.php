<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\OrgFormComponent;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Services\OrgService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

class OrgPresenter extends BasePresenter
{
    use EntityPresenterTrait {
        getEntity as traitGetEntity;
    }

    private OrgService $orgService;

    final public function injectServiceOrg(OrgService $orgService): void
    {
        $this->orgService = $orgService;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit organizer %s'), $this->getEntity()->person->getFullName()),
            'fa fa-user-edit'
        );
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function getEntity(): OrgModel
    {
        /** @var OrgModel $entity */
        $entity = $this->traitGetEntity();
        if ($entity->contest_id != $this->getSelectedContest()->contest_id) {
            throw new ForbiddenRequestException(_('Editing organizer outside chosen seminar'));
        }
        return $entity;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Org %s'), $this->getEntity()->person->getFullName()), 'fa fa-user');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create an organizer'), 'fa fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Organizers'), 'fa fa-user-tie');
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): OrgService
    {
        return $this->orgService;
    }

    protected function getModelResource(): string
    {
        return OrgModel::RESOURCE_ID;
    }

    protected function createComponentCreateForm(): OrgFormComponent
    {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContestYear(), null);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    protected function createComponentEditForm(): OrgFormComponent
    {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContestYear(), $this->getEntity());
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
