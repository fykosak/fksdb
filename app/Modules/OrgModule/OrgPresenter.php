<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\OrgFormComponent;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Modules\Core\PresenterTraits\ContestEntityTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

final class OrgPresenter extends BasePresenter
{
    /** @phpstan-use ContestEntityTrait<OrgModel> */
    use ContestEntityTrait;

    private OrgService $orgService;

    final public function injectServiceOrg(OrgService $orgService): void
    {
        $this->orgService = $orgService;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException|\ReflectionException
     * @throws NoContestAvailable
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit organizer %s'), $this->getEntity()->person->getFullName()),
            'fas fa-user-edit'
        );
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException|\ReflectionException
     * @throws NoContestAvailable
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Organizer %s'), $this->getEntity()->person->getFullName()),
            'fas fa-user'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create an organizer'), 'fas fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Organizers'), 'fas fa-user-tie');
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException|\ReflectionException
     * @throws NoContestAvailable
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

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): OrgFormComponent
    {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContestYear(), null);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentEditForm(): OrgFormComponent
    {
        return new OrgFormComponent($this->getContext(), $this->getSelectedContestYear(), $this->getEntity());
    }

    /**
     * @throws NoContestAvailable
     */
    protected function createComponentGrid(): OrgsGrid
    {
        return new OrgsGrid($this->getContext(), $this->getSelectedContest());
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
