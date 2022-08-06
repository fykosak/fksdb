<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\SchoolFormComponent;
use FKSDB\Components\Grids\ContestantsFromSchoolGrid;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Security\Resource;

/**
 * @method SchoolModel getEntity()
 */
class SchoolPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private SchoolService $schoolService;

    final public function injectServiceSchool(SchoolService $schoolService): void
    {
        $this->schoolService = $schoolService;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Schools'), 'fa fa-school');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create school'), 'fa fa-plus');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit school %s'), $this->getEntity()->name_abbrev), 'fas fa-pen');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Detail of school %s'), $this->getEntity()->name_abbrev),
            'fa fa-university'
        );
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentGrid(): SchoolsGrid
    {
        return new SchoolsGrid($this->getContext());
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    protected function createComponentEditForm(): SchoolFormComponent
    {
        return new SchoolFormComponent($this->getContext(), $this->getEntity());
    }

    protected function createComponentCreateForm(): SchoolFormComponent
    {
        return new SchoolFormComponent($this->getContext(), null);
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    protected function createComponentContestantsFromSchoolGrid(): ContestantsFromSchoolGrid
    {
        return new ContestantsFromSchoolGrid($this->getEntity(), $this->getContext());
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }

    protected function getORMService(): SchoolService
    {
        return $this->schoolService;
    }
}
