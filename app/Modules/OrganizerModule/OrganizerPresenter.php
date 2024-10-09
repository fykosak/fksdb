<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\EntityForms\OrganizerFormComponent;
use FKSDB\Components\Grids\OrganizersGrid;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Services\OrganizerService;
use FKSDB\Modules\Core\PresenterTraits\ContestEntityTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class OrganizerPresenter extends BasePresenter
{
    /** @phpstan-use ContestEntityTrait<OrganizerModel> */
    use ContestEntityTrait;

    private OrganizerService $service;

    final public function injectServiceOrganizer(OrganizerService $service): void
    {
        $this->service = $service;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromOwnResource($this->getEntity()),
            'edit',
            $this->getSelectedContest()
        );
    }
    /**
     * @throws ForbiddenRequestException
     * @throws GoneException|\ReflectionException
     * @throws NoContestAvailable
     * @throws NotFoundException
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
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromOwnResource($this->getEntity()),
            'detail',
            $this->getSelectedContest()
        );
    }
    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NoContestAvailable
     * @throws NotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Organizer %s'), $this->getEntity()->person->getFullName()),
            'fas fa-user'
        );
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(OrganizerModel::RESOURCE_ID, $this->getSelectedContest()),
            'create',
            $this->getSelectedContest()
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create an organizer'), 'fas fa-user-plus');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(OrganizerModel::RESOURCE_ID, $this->getSelectedContest()),
            'list',
            $this->getSelectedContest()
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Organizers'), 'fas fa-user-tie');
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NoContestAvailable
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): OrganizerService
    {
        return $this->service;
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): OrganizerFormComponent
    {
        return new OrganizerFormComponent($this->getContext(), $this->getSelectedContestYear(), null);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): OrganizerFormComponent
    {
        return new OrganizerFormComponent($this->getContext(), $this->getSelectedContestYear(), $this->getEntity());
    }

    /**
     * @throws NoContestAvailable
     */
    protected function createComponentGrid(): OrganizersGrid
    {
        return new OrganizersGrid($this->getContext(), $this->getSelectedContest());
    }

    /**
     * @throws NoContestAvailable
     */
    protected function getModelResource(): ContestResourceHolder
    {
        return ContestResourceHolder::fromResourceId(OrganizerModel::RESOURCE_ID, $this->getSelectedContest());
    }
}
